<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Stamps
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {
  //extends Mage_Core_Helper_Abstract {

  // public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
  //   return parent::asRSIShipment($carrierCode, $address);
  // }

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('Stamps shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');


    $customs = new \RocketShipIt\Customs('stamps');
    $customs->setParameter('customsQuantity', $qty);
    $weight = $mageShipment->getOrder()->getWeight();
    $customs->setParameter('customsWeight', $weight);
    $value = $mageShipment->getOrder()->getSubtotal();
    $customs->setParameter('customsValue', $value);
    
  }

  public function getPackage($shipment) {
    $rateHelper = Mage::helper('rocketshipit/rates');
    $dataHelper = Mage::helper('rocketshipit/data');

    $destAddr = $shipment->getShippingAddress();
    $order = $shipment->getOrder();
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());

    $stampsRate = $rateHelper->getRSIRate('stamps', $destAddr);
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
    $rsiTrackNo = $shipmentResponse->TrackingNumber;
    $labelUrl = $shipmentResponse->URL;
    $curlObj = curl_init();
    curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
    $labelStr = curl_exec($curlObj);
    curl_close($curlObj);
    return $labelStr;
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
