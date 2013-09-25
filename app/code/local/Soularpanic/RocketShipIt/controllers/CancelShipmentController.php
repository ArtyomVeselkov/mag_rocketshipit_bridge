<?php 
class Soularpanic_RocketShipIt_CancelShipmentController
extends Mage_Adminhtml_Controller_Action {

  const STATUS_CANCELED = 5;

  public function cancelShipmentAction() {
    $actionSuccess = true;

    $shipmentId = $this->getRequest()->getParam('shipmentId');
    $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

    $order = $shipment->getOrder();
    $dataHelper = Mage::helper('rocketshipit/data');
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());
    $carrier = $shippingMethod['carrier'];

    $voidCode = $shipment->getRocketshipitId();

    if (empty($voidCode)) {
      $this->_errorOut("Error: There is no recorded RocketShipIt Transaction ID for this shipment ({$shipment->getIncrementId()}); I cannot void it.");
      return;
    }

    $void = new \RocketShipIt\Void($carrier);
    try {
      $voidResp = $void->voidShipment($voidCode);
    }
    catch (Exception $e) {
      $this->_log("Error: ".$e->getMessage());
      $this->_errorOut("Error calling void service: ".$e->getMessage());
      return;
    }
    $this->_log("void response: " . print_r($voidResp, true));
    

    $error = $voidResp['VoidShipmentResponse']['Response']['Error'];
    if (!empty($error)) {
      $reason = $error['ErrorDescription'];
      $this->_log("Cannot cancel because ($reason)");
      $this->_errorOut($reason);
      return;
    }

    $deleteMsg = sprintf("Deleted %s shipment with ID of %s",
			 $carrier, $voidCode);
    $this->deleteShipment($shipment, $deleteMsg);

    $this->_redirect('adminhtml/sales_shipment/');
  }

  function deleteShipment($shipment, $deleteMsg = '') {
    $order = $shipment->getOrder();
    $this->_log("Attempting to clear out items from order with ID of (".$order->getId().")");

    $order->addStatusHistoryComment($deleteMsg, 'pending');

    $shipmentItems = array();
    foreach ($shipment->getAllItems() as $shipmentItem) {
      $shipmentItems[$shipmentItem->getOrderItemId()] = $shipmentItem->getQty();
    }
    $this->_log("shipmentItems array: ".print_r($shipmentItems, true));

    foreach ($order->getAllItems() as $orderItem) {
      $itemId = $orderItem->getId();
      $currentQty = $orderItem->getQtyShipped();
      $newQty =  max($currentQty - $shipmentItems[$itemId], 0);

      $this->_log("setting shipped qty of item $itemId from $currentQty to $newQty");
      $orderItem->setQtyShipped($newQty);
      $orderItem->save();
    }

    $shipment->delete();
    $order->save();
  }

  function _errorOut($errorMsg) {
    $this->_getSession()->addError($errorMsg);
    $this->_redirectUrl($this->_getRefererUrl());
  }

  function _log($msg) {
    Mage::log($msg, null, 'rocketshipit_shipments.log');
  }

}
?>
