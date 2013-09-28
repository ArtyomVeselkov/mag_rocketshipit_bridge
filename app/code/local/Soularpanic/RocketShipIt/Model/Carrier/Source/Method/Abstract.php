<?php
abstract class Soularpanic_RocketShipIt_Model_Carrier_Source_Method_Abstract {
  
  abstract function getCode();

  public function toOptionArray() {
    $code = $this->getCode();
    $model = Mage::getSingleton('rocketshipit/carrier_'.$code);
    $arr = array();
    foreach ($model->getMethods() as $k => $v) {
      $arr[] = array('value' => $k,
		     'label' => $v);
    }
    return $arr;
  }
}
?>
