<?php
abstract class Soularpanic_RocketShipIt_Model_Carrier_Source_Abstract {
  
  abstract function getCode();
  abstract function getSourceArray($model);

  public function toOptionArray() {
    $code = $this->getCode();
    $model = Mage::getSingleton('rocketshipit/carrier_'.$code);
    $arr = array();
    foreach ($this->getSourceArray($model) as $k => $v) {
      $arr[] = array('value' => $k,
		     'label' => $v);
    }
    return $arr;
  }
}

