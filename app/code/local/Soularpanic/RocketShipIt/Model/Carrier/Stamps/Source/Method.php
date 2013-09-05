<?php
class Soularpanic_RocketShipIt_Model_Carrier_Stamps_Source_Method {
  public function toOptionArray() {
    $stamps = Mage::getSingleton('rocketshipit/carrier_stamps');
    $arr = array();
    foreach ($stamps->getMethods() as $k => $v) {
      $arr[] = array('value' => $k,
		     'label' => $v);
    }
    return $arr;
  }
}
?>
