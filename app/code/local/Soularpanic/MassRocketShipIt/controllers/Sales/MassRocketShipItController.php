<?php

require_once('Fooman/OrderManager/controllers/Sales/OrderManagerController.php');

class Soularpanic_MassRocketShipIt_Sales_RocketShipItController 
extends Fooman_OrderManager_Sales_OrderManagerController {
  function massAction() {
    Mage::log('rocketshipitcontroller firing',
	      null,
	      'rocketshipit_shipments.log');
    $this->_redirect('adminhtml/sales_order/');
  }

  function shipallAction() {
    Mage::log('massrocketshipitcontroller catching shipallAction',
	      null,
	      'rocketshipit_shipments.log');

    parent::shipallAction();
  }
}
?>