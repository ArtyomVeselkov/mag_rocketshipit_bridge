<?php

class Soularpanic_MassRocketShipIt_Block_Adminhtml_Widget_Grid_Column_Renderer_CarrierOverrideInput
  extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
  
  public function _getValue(Varien_Object $row) { 
    return "hi!";
  }

  public function getFilter() {
    return false;
  }
}
