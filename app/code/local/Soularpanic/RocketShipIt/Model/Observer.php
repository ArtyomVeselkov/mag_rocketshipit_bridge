<?php
class Soularpanic_RocketShipIt_Model_Observer
{
  protected $_code = 'rocketshipit';

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
    
    try {
      $label = $rsiShipment->submitShipment();
    }
    catch (Exception $e) {
      $dataHelper->log("RSI shipment submission error!\n".$rsiShipment->debug());
      throw $e;
    }
    
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
    $shippingMethod = $shipment->getOrder()->getShippingMethod();
    $carrierCode = substr($shippingMethod, 0, strpos($shippingMethod, '_', 13));
    $track->setCarrierCode($carrierCode);
    $shipment->addTrack($track);

    $rsiId = $shipmentHelper->extractRocketshipitId($label);
    $shipment->setRocketshipitId($rsiId);
    //die('wait');
  }

  public function addHandlingCodeToQuote(Varien_Event_Observer $observer) {
    $quote = $observer->getEvent()->getQuote(); //Mage_Sales_Model_Quote
    $request = $observer->getEvent()->getRequest();
    $handlingCode = $request->getPost('shipping_addons', '');
    $shippingAddr = $quote->getShippingAddress();
    $shippingAddr->setHandlingCode($handlingCode);
  }

  public function addCustomerCommentToQuote(Varien_Event_Observer $observer) {
    $quote = $observer->getEvent()->getQuote();
    $request = $observer->getEvent()->getRequest();
    $comment = $request->getPost('comments', '');
    if (!empty($comment)) {
      $quote->setCustomerComment($comment);
    }
  }

  public function addCustomerCommentToOrder(Varien_Event_Observer $observer) {
    $quote = $observer->getEvent()->getQuote();
    $quoteComment = $quote->getCustomerComment();
    if (!empty($quoteComment)) {
      $order = $observer->getEvent()->getOrder();
      $order->addStatusHistoryComment($quoteComment);
      $order->save();
    }
  }
}
?>
