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
    
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException('Label generation failed: '.$label);
    }

    $labelImg = $shipmentHelper->extractShippingLabel($label);
    $shipment->setShippingLabel($labelImg);

    $rsiTrackNo = $shipmentHelper->extractTrackingNo($label);
    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle($shipment->getOrder()->getShippingDescription());
    $track->setNumber($rsiTrackNo);
    $track->setCarrierCode($shipment->getOrder()->getShippingMethod());
    $shipment->addTrack($track);
    //die('wait');
  }

  public function addCarrierAddOns(Varien_Event_Observer $observer) {
    Mage::log('rocketshipit observer firing - addCarrierAddOns', null, 'rocketshipit_shipments.log');
    $quote = $observer->getEvent()->getQuote(); //Mage_Sales_Model_Quote
    $request = $observer->getEvent()->getRequest();
    $addOnCode = $request->getPost('shipping_addons', '');
    $shippingAddr = $quote->getShippingAddress();
    $price = 0.0;
    if ($addOnCode === 'sign') { $price = 5.0; }
    elseif ($addOnCode === 'signAndInsure') { $price = 7.5; }
    $shippingAddr->setHandlingAmount($price);
    $shippingAddr->setHandlingCode($addOnCode);
    
    //$shippingAddr->save();
    // $quote->setShippingAddress($shippingAddr);
    // $quote->save();
    // $shippingAddr->save();
    //Mage::log("quote: ".print_r($quote, true), null, 'rocketshipit_shipments.log');
  }

  public function salesQuoteAddressObserver(Varien_Event_Observer $observer) {
    Mage::log('quote address being recalculated',
	      null, 'rocketshipit_shipments.log');
  }

}
?>
