<?php 
class Soularpanic_RocketShipIt_Model_Carrier_Source_Carriers {
  public function toOptionArray() {
    return array(
      array('value' => 'none',
	    'label' => 'None'),
      array('value' => 'ups',
	    'label' => 'UPS'),
      array('value' => 'stamps',
	    'label' => 'Stamps.com')
    );
  }
}

