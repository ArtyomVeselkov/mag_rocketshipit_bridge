<?php 
abstract class Soularpanic_RocketShipIt_Helper_Validation_Abstract
extends Soularpanic_RocketShipIt_Helper_Data {
  abstract function getCarrierCode();
  abstract function getValidateResponse($rsiValidate);
  abstract function parseValidateResponse($response, &$data);

  public function validate(array &$data) {
    $carrierCode = $this->getCarrierCode();
    $validator = $this->getRsiValidate($carrierCode, $data);
    $response = $this->getValidateResponse($validator);
    $errors = $this->parseValidateResponse($response, $data);
    return $errors;
  }


  public function getRsiValidate($carrierCode, array $data) {
    $validate = new \RocketShipIt\AddressValidate($carrierCode);

    $validate->setParameter('toAddr1', $data['street'][0]);
    $validate->setParameter('toAddr2', $data['street'][1]);
    $validate->setParameter('toCity', $data['city']);
    $validate->setParameter('toCode', $data['postcode']);
    $validate->setParameter('toCountry', $data['country_id']);

    $stateCode = null;
    $regionId = $data['region_id'];
    if (is_numeric($regionId)) {
      $regionModel = Mage::getModel('directory/region')->load($regionId);
      $stateCode = $regionModel->getCode();
    }
    $validate->setParameter('toState', $stateCode);

    return $validate;
  }
}
?>
