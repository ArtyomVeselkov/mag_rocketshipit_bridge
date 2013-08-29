<?php

class Soularpanic_MassRocketShipIt_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingOverrideInput
  extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
  
  public function _getValue(Varien_Object $row) { 
    $shippingMethod = $row->getShippingMethod();
    $shippingAddr = $row->getShippingAddress(); //Mage_Sales_Model_Order_Address
    $shippingWeight = $row->getShippingWeight();
    $rsiHelper = Mage::helper('rocketshipit');
    $rsiRate = $rsiHelper->getRSIRate('ups', $shippingAddr);
    $rsiRate->setParameter('weight', $shippingWeight);
    $rates = $rsiRate->getSimpleRates();
    return $shippingMethod;
  }

  public function getFilter() {
    return false;
  }
}
