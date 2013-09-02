<?php

require_once('Fooman/OrderManager/controllers/Sales/OrderManagerController.php');

class Soularpanic_MassRocketShipIt_Sales_OrderManagerController 
extends Fooman_OrderManager_Sales_OrderManagerController {

  public function shipallAction() {
    Mage::log('sp mass order manager controller catching shipallAction',
	      null,
	      'rocketshipit_shipments.log');
    $orderIds = $this->getRequest()->getPost('order_ids');
    $shippingOverrides = $this->_getShippingOverrides();
    foreach ($orderIds as $orderId) {
      $order = Mage::getModel('sales/order')->load($orderId);
      if (!($order->canShip())) {
	continue;
      }
      $shippingMethod = $order->getShippingMethod();
      $shippingOverride = $shippingOverrides[$orderId];
      $overrideCode = $shippingOverride['code'];
      if ($overrideCode != $shippingMethod) {
	$overrideName = $shippingOverride['name'];
	/* $comment = Mage::getModel('sales/order_shipment_comment'); */
	/* $comment->setComment('Shipment method overridden from '.$order->getShippingDescription().' to '.$overrideName.' ('.$shippingOverride['cost'].')'); */
	$order->addStatusHistoryComment('Shipment method overridden from '.$order->getShippingDescription().' to '.$overrideName.' ('.$shippingOverride['cost'].')');
	$order->setShippingDescription($overrideName);
	$order->setShippingMethod($overrideCode);
	$order->save();
      }
    }
    
    parent::shipallAction();
  }

  private function _getShippingOverrides() {
    $shippingOverrides = array();
    $shippingOverridesStr = $this->getRequest()->getPost('shipping_override');
    foreach (explode(',', $shippingOverridesStr) as $shippingOverrideStr) {
      list($orderId, $carrierCode, $methodName, $methodCost) = explode('|', $shippingOverrideStr);
      $shippingOverrides[$orderId] = array('code' => $carrierCode,
					   'name' => $methodName,
					   'cost' => $methodCost);
    }
    return $shippingOverrides;
  }
}
?>