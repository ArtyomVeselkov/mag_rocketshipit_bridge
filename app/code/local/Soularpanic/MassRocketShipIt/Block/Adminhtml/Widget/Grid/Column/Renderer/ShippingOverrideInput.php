<?php

class Soularpanic_MassRocketShipIt_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingOverrideInput
  extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
  
  public function _getValue(Varien_Object $row) { 
    $shippingMethod = $row->getShippingMethod();
    $shippingAddr = $row->getShippingAddress(); //Mage_Sales_Model_Order_Address
    $shippingWeight = $row->getShippingWeight();

    $rsiHelper = Mage::helper('rocketshipit');
    $useNegotiatedRate = $useNegotiatedRate = Mage::getStoreConfig('carriers/'.'rocketshipit_ups'.'/useNegotiatedRates');
    $rates = $rsiHelper->getSimpleRates('ups',
					$shippingAddr, 
					$useNegotiatedRate,
					$shippingWeight,
					0);
    $html = '<select>';
    foreach($rates->getAllRates() as $rate) {
      $selected = ('ups_'.$rate->getMethod() == $shippingMethod) ? 'selected="selected" ' : '';
      $html.= '<option '.$selected.'value="'.$rate->getMethod().'">'.$rate->getMethodTitle().' -- '.$rate->getCost().'</option>';
    }
    $html.='</select>';

    //return $shippingMethod;
    return $html;
  }

  public function getFilter() {
    return false;
  }
}
