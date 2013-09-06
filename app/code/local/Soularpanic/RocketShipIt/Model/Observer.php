<?php
class Soularpanic_RocketShipIt_Model_Observer
{
  protected $_code = 'rocketshipit';

  function __construct() { }

  public function trackAndLabel(Varien_Event_Observer $observer)
  {
    Mage::log('rocketshipit observer firing',
	      null,
	      'rocketshipit_shipments.log');

    $dataHelper = Mage::helper('rocketshipit/data');
    $rateHelper = Mage::helper('rocketshipit/rates');

    $shipment = $observer->getEvent()->getShipment();
    $order = $shipment->getOrder();

    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());
    $carrier = $shippingMethod['carrier'];

    $destAddr = $shipment->getShippingAddress();
    $rsiShipment = $dataHelper->asRSIShipment($carrier, $destAddr);
    
    $serviceType = $shippingMethod['service'];

    $rsiPackage = null;
    if ($carrier === 'stamps') {
      $stampsRate = $rateHelper->getRSIRate($carrier, $destAddr);
      $stampsResp = $stampsRate->getAllRates();
      $stampsRates = $stampsResp->Rates->Rate;
      $serviceArr = explode(':', $shippingMethod['service']);
      $serviceType = $serviceArr[0];
      $packageType = str_replace('-', ' ', $serviceArr[1]);

      foreach ($stampsRates as $stampsRate) {
	if ($stampsRate->ServiceType === $serviceType
	    && $stampsRate->PackageType === $packageType) {
	  $rsiPackage = $stampsRate;
	  $rsiPackage->AddOns = null;
	  $addOns = array();
	  $guess = new \stdClass();
	  $guess->AddOnType = 'US-A-DC';
	  array_push($addOns, $guess);
	  $rsiPackage->AddOns = $addOns;
	}
      }
    }
    else {
      $rsiPackage = new \RocketShipIt\Package($carrier);
      $rsiPackage->setParameter('length','6');
      $rsiPackage->setParameter('width','6');
      $rsiPackage->setParameter('height','6');
      
      $weight = $shipment->getOrder()->getWeight();
      $rsiPackage->setParameter('weight', $weight);
    }

    $rsiShipment->setParameter('service', $serviceType);

    $rsiShipment->addPackageToShipment($rsiPackage);
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException('Label generation failed: '.$label);
    }

    if ($carrier === 'stamps') {
      $rsiTrackNo = $label->TrackingNumber;
      $labelUrl = $label->URL;
      $curlObj = curl_init();
      curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
      $labelStr = curl_exec($curlObj);
      curl_close($curlObj);
      $shipment->setShippingLabel($labelStr);
    }
    elseif ($carrier === 'ups') {
      $rsiTrackNo = $label['trk_main'];
      $labelImg = $label['pkgs'][0]['label_img'];
      $labelImgDecoded = base64_decode($labelImg);
      $shipment->setShippingLabel($labelImgDecoded);
    }

    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle($shipment->getOrder()->getShippingDescription());
    $track->setNumber($rsiTrackNo);
    $track->setCarrierCode($shipment->getOrder()->getShippingMethod());
    $shipment->addTrack($track);
  }
}
?>
