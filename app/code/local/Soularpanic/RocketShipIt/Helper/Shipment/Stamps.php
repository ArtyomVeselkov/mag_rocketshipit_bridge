<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Stamps
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('Stamps shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');

    $orderId = $mageShipment->getOrder()->getId();
    $orderData = Mage::getModel('rocketshipit/orderExtras')->load($orderId);

    $customs = new \RocketShipIt\Customs('stamps');
    
    $weight = $mageShipment->getOrder()->getWeight();
    $customs->setParameter('customsWeight', $weight);

    $qty = $orderData->getCustomsQty();
    $customs->setParameter('customsQuantity', $qty);
    
    $value = $orderData->getCustomsValue();
    $customs->setParameter('customsValue', $value);
    //$rsiShipment->setParameter('declaredValue', $value);

    $desc = $orderData->getCustomsDesc();
    $customs->setParameter('customsDescription', $desc);
    $rsiShipment->setParameter('customsOtherDescribe', $desc);

    $customs->setParameter('customsOriginCountry', 'US');
    $rsiShipment->setParameter('customsContentType', 'Other');
    
    $rsiShipment->addCustomsLineToShipment($customs);
    return $rsiShipment;
  }

  public function getPackage($shipment) {
    $rateHelper = Mage::helper('rocketshipit/rates');
    $dataHelper = Mage::helper('rocketshipit/data');

    $destAddr = $shipment->getShippingAddress();
    $order = $shipment->getOrder();
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());

    $stampsRate = $rateHelper->getRSIRate('stamps', $destAddr);
    if ($stampsRate->weightPounds == '') {
      $stampsRate->setParameter('weightPounds', $order->getWeight());
    }
    if ($destAddr->getCountryId() !== 'US') {
      $orderData = Mage::getModel('rocketshipit/orderExtras')->load($order->getId());
      $stampsRate->setParameter('declaredValue', $orderData->getCustomsValue());
    }
    $stampsResp = $stampsRate->getAllRates();
    $stampsRates = $stampsResp->Rates->Rate;

    $serviceArr = $this->_parseStampsShippingMethod($shippingMethod);
    $serviceType = $serviceArr['serviceType'];
    $packageType = $serviceArr['packageType'];
    
    

    foreach ($stampsRates as $stampsRate) {
      if ($stampsRate->ServiceType === $serviceType
	  && $stampsRate->PackageType === $packageType) {
	$rsiPackage = $stampsRate;
	$rsiPackage->AddOns = null;
	$addOns = array();
	// $guess = new \stdClass();
	// $guess->AddOnType = 'US-A-DC';
	// array_push($addOns, $guess);
	$rsiPackage->AddOns = $addOns;
      }
    }
    return $rsiPackage;
  }

  public function extractShippingLabel($shipmentResponse) {
    //$rsiTrackNo = $shipmentResponse->TrackingNumber;
    $labelUrlsStr = $shipmentResponse->URL;
    $labelUrls = explode(' ', $labelUrlsStr);
    $labelImages = $this->_fetchLabelImages($labelUrls);

    $labelPdf = $this->convertImagesToPdf($labelImages);
    $pdfStr = $labelPdf->render();

    return $pdfStr;
  }

  public function extractTrackingNo($shipmentResponse) {
    return $shipmentResponse->TrackingNumber;
  }

  function _fetchLabelImages($labelUrls) {
    $labelResources = array();
    foreach ($labelUrls as $labelUrl) {
      $curlObj = curl_init();
      curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
      $labelStr = curl_exec($curlObj);
      $resource = imagecreatefromstring($labelStr);
      $labelResources[] = $resource;
      curl_close($curlObj);
    }
    return $labelResources;
  }

  public function getServiceType($shippingMethod) {
    $method = $this->_parseStampsShippingMethod($shippingMethod);
    return $method['serviceType'];
  }

  function _parseStampsShippingMethod($shippingMethod) {
    $serviceArr = explode(':', $shippingMethod['service']);
    $serviceType = $serviceArr[0];
    $packageType = str_replace('-', ' ', $serviceArr[1]);
    return array('serviceType' => $serviceType,
		 'packageType' => $packageType);
  }

}
?>
