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

    $helper = Mage::helper('rocketshipit');
    
    $shipment = $observer->getEvent()->getShipment();
    $order = $shipment->getOrder();

    $shippingMethod = $helper->parseShippingMethod($order->getShippingMethod());

    $destAddr = $shipment->getShippingAddress();
    $rsiShipment = $helper->asRSIShipment('UPS', $destAddr);
    $rsiShipment->setParameter('service', $shippingMethod['service']);

    $rsiPackage = new RocketShipPackage('UPS');
    $rsiPackage->setParameter('length','6');
    $rsiPackage->setParameter('width','6');
    $rsiPackage->setParameter('height','6');
    
    $weight = $shipment->getOrder()->getWeight();
    $rsiPackage->setParameter('weight', $weight);
    
    $rsiShipment->addPackageToShipment($rsiPackage);
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException($this->__('Label generation failed: '.$label));
    }

    $rsiTrackNo = $label['trk_main'];
    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle($shipment->getOrder()->getShippingDescription());
    $track->setNumber($rsiTrackNo);
    $track->setCarrierCode($shipment->getOrder()->getShippingMethod());
    $shipment->addTrack($track);
    
    $labelImg = $label['pkgs'][0]['label_img'];
    $labelImgDecoded = base64_decode($labelImg);
    $shipment->setShippingLabel($labelImgDecoded);
  }
}
?>