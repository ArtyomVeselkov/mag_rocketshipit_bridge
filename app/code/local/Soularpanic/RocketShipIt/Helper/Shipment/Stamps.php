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

    $desc = $orderData->getCustomsDesc();
    $customs->setParameter('customsDescription', $desc);
    $rsiShipment->setParameter('customsOtherDescribe', $desc);

    $customs->setParameter('customsOriginCountry', 'US');
    $rsiShipment->setParameter('customsContentType', 'Other');
    
    $rsiShipment->addCustomsLineToShipment($customs);
    return $rsiShipment;
  }

  public function needsCustomsData($destAddr) {
    $countryCode = $destAddr->getCountryId();
    $regionCode = $destAddr->getRegionCode();
    return ($countryCode !== 'US' ||
	    $regionCode === 'AP' ||
	    $regionCode === 'AE' ||
	    $regionCode === 'AA');
  }

  public function getPackage($shipment) {
    $rateHelper = Mage::helper('rocketshipit/rates');
    $dataHelper = Mage::helper('rocketshipit/data');

    $destAddr = $shipment->getShippingAddress();
    $order = $shipment->getOrder();
    $orderData = Mage::getModel('rocketshipit/orderExtras')->load($order->getId());
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());

    $stampsRate = $rateHelper->getRSIRate('stamps', $destAddr);
    if ($stampsRate->weightPounds == '') {
      $stampsRate->setParameter('weightPounds', $order->getWeight());
    }
    if ($destAddr->getCountryId() !== 'US') {
      $stampsRate->setParameter('declaredValue', $orderData->getCustomsValue());
    }
    $stampsResp = $stampsRate->getAllRates();
    $stampsRates = $stampsResp->Rates->Rate;

    $serviceArr = $this->_parseStampsShippingMethod($shippingMethod);
    $serviceType = $serviceArr['serviceType'];
    $packageType = $serviceArr['packageType'];
    
    $addOns = $this->_getAddOns($orderData->getCarrierServices(),
				$serviceType,
				$destAddr);

    foreach ($stampsRates as $stampsRate) {
      if ($stampsRate->ServiceType === $serviceType
	  && $stampsRate->PackageType === $packageType) {
	$rsiPackage = $stampsRate;
	$rsiPackage->AddOns = null;
	$rsiPackage->AddOns = $addOns;
      }
    }
    return $rsiPackage;
  }

  public function extractShippingLabel($shipmentResponse) {
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

  function _carrierRequiresDeliveryConfirmation($carrierCode) {
    return ($carrierCode != 'US-XM' &&
	    $carrierCode != 'US-PMI' &&
	    $carrierCode != 'US-EMI');
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

  function _getAddOns($carrierAddOnCode, $carrierCode, $destAddr) {
    $needDeliveryConfirmation = $this->_carrierRequiresDeliveryConfirmation($carrierCode);

    $addOns = array();

    if ($destAddr->getCountryId() === 'US') {
      $hiddenPostage = new \stdClass();
      $hiddenPostage->AddOnType = 'SC-A-HP';
      $addOns[] = $hiddenPostage;
    }

    if ($carrierAddOnCode === 'signAndInsure') {
      $insure = new \stdClass();
      $insure->AddOnType = 'SC-A-INS';
      $addOns[] = $insure;

      $sign = new \stdClass();
      $sign->AddOnType = 'US-A-SC';
      $addOns[] = $sign;
    }
    elseif ($carrierAddOnCode === 'sign') {
      $sign = new \stdClass();
      $sign->AddOnType = 'US-A-SC';
      $addOns[] = $sign;
    }
    else {
      if ($needDeliveryConfirmation) {
	$confirm = new \stdClass();
	$confirm->AddOnType = 'US-A-DC';
	$addOns[] = $confirm;
      }
      elseif ($carrierCode == 'US-XM') {
	$noSig = new \stdClass();
	$noSig->AddOnType = 'US-A-WDS';
	$addOns[] = $noSig;
      }
    }
    
    return $addOns;
  }

}
?>
