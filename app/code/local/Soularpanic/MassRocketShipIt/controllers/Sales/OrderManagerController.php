<?php

require_once('Fooman/OrderManager/controllers/Sales/OrderManagerController.php');

class Soularpanic_MassRocketShipIt_Sales_OrderManagerController 
extends Fooman_OrderManager_Sales_OrderManagerController {

  public function shipallAction() {
    Mage::log('sp mass order manager controller catching shipallAction',
	      null,
	      'rocketshipit_shipments.log');

    $this->_processPost();
    parent::shipallAction();
  }

  public function invoiceandshipallAction() {
    Mage::log('sp mass order manager controller catching invoiceandshipallAction',
	      null,
	      'rocketshipit_shipments.log');

    $this->_processPost();
    parent::shipallAction();
  }

  public function captureandshipallAction() {
    Mage::log('sp mass order manager controller catching captureandshipallAction',
	      null,
	      'rocketshipit_shipments.log');

    $this->_processPost();
    parent::shipallAction();
  }

  function invoicecaptureshipallAction() {
    Mage::log('sp mass order manager controller catching invoicecaptureshipallAction',
	      null,
	      'rocketshipit_shipments.log');

    $this->_processPost();
    parent::shipallAction();
  }

  private function _processPost() {
    $orderIds = $this->getRequest()->getPost('order_ids');
    $shippingOverrides = $this->_getShippingOverrides();
    $shippingCustomsVals = $this->_getSimpleField('shipping_customs_value');
    $shippingCustomsQtys = $this->_getSimpleField('shipping_customs_qty');
    $shippingCustomsDesc = $this->_getSimpleField('shipping_customs_desc');
    $shippingServices = $this->_getSimpleField('shipping_services');
    foreach ($orderIds as $orderId) {
      $order = Mage::getModel('sales/order')->load($orderId);
      if (!($order->canShip())) {
	continue;
      }
      
      $customsVal = $shippingCustomsVals[$orderId];
      $customsQty = $shippingCustomsQtys[$orderId];
      $customsDesc = $shippingCustomsDesc[$orderId];
      $shippingService = $shippingServices[$orderId];

      $order->setCustomsDesc($customsDesc);
      $order->setCustomsQty($customsQty);
      $order->setCustomsValue($customsVal);
      $order->setHandlingCode($shippingService);

      $order->save();

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
