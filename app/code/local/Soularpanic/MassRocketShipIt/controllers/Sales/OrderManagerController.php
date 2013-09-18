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
    $shippingAddOns = $this->_getSimpleField('shipping_addOns');
    $shippingCustomsVals = $this->_getSimpleField('shipping_customs_value');
    $shippingCustomsQtys = $this->_getSimpleField('shipping_customs_qty');
    $shippingCustomsDesc = $this->_getSimpleField('shipping_customs_desc');
    $shippingServices = $this->_getSimpleField('shipping_services');
    foreach ($orderIds as $orderId) {
      $order = Mage::getModel('sales/order')->load($orderId);
      if (!($order->canShip())) {
	continue;
      }
      
      if ($order->getShippingAddress()->getCountryId() !== 'US') {
	$customsVal = $shippingCustomsVals[$orderId];
	$customsQty = $shippingCustomsQtys[$orderId];
	$customsDesc = $shippingCustomsDesc[$orderId];
	
	$orderDetails = Mage::getModel('rocketshipit/orderExtras')->load($orderId);
	$orderDetails->setOrderId($orderId);
	$orderDetails->setCustomsDesc($customsDesc);
	$orderDetails->setCustomsQty($customsQty);
	$orderDetails->setCustomsValue($customsVal);

	$orderDetails->save();
      }

      $shippingMethod = $order->getShippingMethod();
      $shippingOverride = $shippingOverrides[$orderId];
      $overrideCode = $shippingOverride['code'];
      if ($overrideCode != $shippingMethod) {
	$overrideName = $shippingOverride['name'];
	$order->addStatusHistoryComment('Shipment method overridden from '.$order->getShippingDescription().' to '.$overrideName.' ('.$shippingOverride['cost'].')');
	$order->setShippingDescription($overrideName);
	$order->setShippingMethod($overrideCode);
	$order->save();
      }
    }
    
    parent::shipallAction();
  }

  private function _getSimpleField($simpleFieldKey) {
    $simpleArr = array();
    $simpleFieldsStr = $this->getRequest()->getPost($simpleFieldKey);
    foreach (explode(',', $simpleFieldsStr) as $simpleFieldStr) {
      list($orderId, $val) = explode('|', $simpleFieldStr, 2);
      $simpleArr[$orderId] = $val;
    }
    return $simpleArr;
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
