<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Ups
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {
  //extends Mage_Core_Helper_Abstract {

  // public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
  //   return parent::asRSIShipment($carrierCode, $address);
  // }

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('UPS shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');
  }

  public function getPackage($shipment) {
    $rsiPackage = new \RocketShipIt\Package('ups');
    $rsiPackage->setParameter('length','6');
    $rsiPackage->setParameter('width','6');
    $rsiPackage->setParameter('height','6');
    
    $weight = $shipment->getOrder()->getWeight();
    $rsiPackage->setParameter('weight', $weight);

    return $rsiPackage;
  }

  public function extractShippingLabel($shipmentResponse) {
    $rsiTrackNo = $shipmentResponse['trk_main'];
    $labelImg = $shipmentResponse['pkgs'][0]['label_img'];
    $labelImgDecoded = base64_decode($labelImg);
    return $labelImgDecoded;
  }

  public function getServiceType($shippingMethod) {
    return $shippingMethod['service'];
  }

}
?>
