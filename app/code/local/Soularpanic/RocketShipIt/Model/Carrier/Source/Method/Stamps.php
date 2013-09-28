<?php
class Soularpanic_RocketShipIt_Model_Carrier_Source_Method_Stamps 
extends Soularpanic_RocketShipIt_Model_Carrier_Source_Method_Abstract {

  const CODE = 'stamps';

  function getCode() {
    return self::CODE;
  }
  // public function toOptionArray() {
  //   $stamps = Mage::getSingleton('rocketshipit/carrier_stamps');
  //   $arr = array();
  //   foreach ($stamps->getMethods() as $k => $v) {
  //     $arr[] = array('value' => $k,
  // 		     'label' => $v);
  //   }
  //   return $arr;
  // }
}
?>
