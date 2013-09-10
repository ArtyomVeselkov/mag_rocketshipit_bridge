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
    $shipmentHelper = Mage::helper('rocketshipit/shipment_'.$carrier);
    $rsiShipment = $shipmentHelper->prepareShipment($shipment);
    // $destAddr = $shipment->getShippingAddress();
    // $rsiShipment = $shipmentHelper->asRSIShipment($carrier, $destAddr);
    
    // $serviceType = $shipmentHelper->getServiceType($shippingMethod);
    
    // $rsiPackage = $shipmentHelper->getPackage($shipment);

    // $rsiShipment->setParameter('service', $serviceType);

    // $rsiShipment->addPackageToShipment($rsiPackage);
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException('Label generation failed: '.$label);
    }

    $labelImg = $shipmentHelper->extractShippingLabel($label);
    $shipment->setShippingLabel($labelImg);

    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle($shipment->getOrder()->getShippingDescription());
    $track->setNumber($rsiTrackNo);
    $track->setCarrierCode($shipment->getOrder()->getShippingMethod());
    $shipment->addTrack($track);
    die('wait');
  }
}
?>
