<?php
class Soularpanic_RocketShipIt_Model_Observer
{
  protected $_code = 'rocketshipit';

  public function trackAndLabel(Varien_Event_Observer $observer)
  {
    Mage::log('rocketshipit observer firing',
	      null,
	      'rocketshipit_shipments.log');

    $shipment = $observer->getEvent()->getShipment();
    $order = $shipment->getOrder();

    // we should only generate a tracking # on the first shipment->save()
    if (!($shipment->isObjectNew())) {
      return;
    }

    $dataHelper = Mage::helper('rocketshipit/data');
    $rateHelper = Mage::helper('rocketshipit/rates');

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
      Mage::log("Label generation failed:\n".$rsiShipment->debug(), null, 'rocketshipit_errors.log');
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
    $request = Mage::app()->getRequest(); //$request = $observer->getEvent()->getRequest();
    $handlingCode = $request->getParam('shipping_addons', '');//$handlingCode = $request->getPost('shipping_addons', '');
    if ($handlingCode) {
      $shippingAddr = $quote->getShippingAddress();
      $shippingAddr->setHandlingCode($handlingCode);
    }
  }

  public function addCustomerCommentToQuote(Varien_Event_Observer $observer) {
    $quote = $observer->getEvent()->getQuote();
    //$request = $observer->getEvent()->getRequest();
    $request = Mage::app()->getRequest();
    //$comment = $request->getPost('comments', '');
    $comment = $request->getParam('comments', '');
    if ($comment) {
      $quote->setCustomerComment($comment);
    }
  }

  public function addCustomerCommentToOrder(Varien_Event_Observer $observer) {
    $order = $observer->getEvent()->getOrder();
    $quote = $order->getQuote();
    $quoteComment = $quote->getCustomerComment();
    if ($quoteComment) {
      $comment = $order->addStatusHistoryComment($quoteComment);
      $trsHelper = Mage::helper('trs/comment');
      $comment->setCommentType($trsHelper::CUSTOMER_COMMENT_TYPE);
      $order->save();
    }
  }

  public function addAuditFieldsToQuote(Varien_Event_Observer $observer) {
    $quote = $observer->getEvent()->getQuote();
    $request = Mage::app()->getRequest();
    $audit = $request->getParam('audit');
    if ($audit) {
      $use = filter_var($audit['requested'], FILTER_VALIDATE_BOOLEAN) === true;
      $quote->setCustomerVehicleYear($use ? $audit['year'] : null);
      $quote->setCustomerVehicleMake($use ? $audit['make'] : null);
      $quote->setCustomerVehicleModel($use ? $audit['model'] : null);
    }
  }
  
}
?>
