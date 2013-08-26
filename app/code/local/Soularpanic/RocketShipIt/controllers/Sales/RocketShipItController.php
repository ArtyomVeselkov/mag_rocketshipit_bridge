<?php
class Soularpanic_RocketShipIt_Sales_RocketShipItController extends Mage_Adminhtml_Controller_Action {
  function massAction() {
    Mage::log('rocketshipitcontroller firing',
	      null,
	      'rocketshipit_shipments.log');
    $this->_redirect('adminhtml/sales_order/');
  }
}
?>