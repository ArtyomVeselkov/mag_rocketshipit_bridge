<?php 
class Soularpanic_RocketShipIt_Helper_Tracking
extends Mage_Core_Helper_Abstract {
  public function getTrackingUrl($track) {
    $dataHelper = Mage::helper('rocketshipit');
    $carrierCode = $dataHelper->parseShippingMethod($track->getCarrierCode());
    $carrier = $carrierCode['carrier'];
    $number = $track->getNumber();
    switch ($carrier) {
      case 'ups':
	$url = "http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$number";
	break;
      case 'stamps':
      case 'usps':
	$url = "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=$number";
	break;
      default:
	$url = "#";
	break;
    }
    return $url;
  }
}

