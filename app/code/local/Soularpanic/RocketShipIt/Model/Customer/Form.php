<?php 
class Soularpanic_RocketShipIt_Model_Customer_Form
extends Mage_Customer_Model_Form {
  public function validateData(array &$data) {
    $validationCarrierCode = Mage::getStoreConfig('carriers/rocketshipit_global/validation_carrier');
    $errors = array();
    if ($validationCarrierCode !== null && $validationCarrierCode !== 'none') {
      $helper = Mage::helper('rocketshipit/validation_'.$validationCarrierCode);
      $errors = $helper->validate($data);
    }

    $parentValidation =  parent::validateData($data);
    if ($parentValidation !== true) {
      $errors = array_merge($errors, $parentValidaton);
    }

    if (empty($errors)) {
      return true;
    }

    return $errors;
  }
}
?>
