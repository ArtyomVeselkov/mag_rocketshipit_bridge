<?php

class Soularpanic_RocketShipIt_Sales_RocketShipItController 
extends Mage_Adminhtml_Controller_Action {
/* extends Fooman_OrderManager_Sales_OrderManagerController { */
  function massAction() {
    Mage::log('rocketshipitcontroller firing',
	      null,
	      'rocketshipit_shipments.log');
    $this->_redirect('adminhtml/sales_order/');
  }

  function shipallAction() {
    Mage::log('rocketshipitcontroller catching shipallAction',
	      null,
	      'rocketshipit_shipments.log');
    /* $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_orderManager/shipall', array('_secure'=>1)); */
    $args = $this->getRequest()->getPost();
    /* $this->_redirectUrl($url, $args); */
    $this->_redirect('adminhtml/sales_orderManager/shipall', $args);
  }
}
?>