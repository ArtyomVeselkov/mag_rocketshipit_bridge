<?php 
abstract class Soularpanic_RocketShipIt_Helper_Validation_Abstract
extends Soularpanic_RocketShipIt_Helper_Data {
  abstract function getCarrierCode();
  abstract function getValidateResponse($rsiValidate);
  abstract function parseValidateResponse($response, &$data);

  public function validate(array &$data) {
    if (!$this->_containsAddressData($data)) {
      return true;
    }

    $carrierCode = $this->getCarrierCode();
    $validator = $this->getRsiValidate($carrierCode, $data);
    $response = $this->getValidateResponse($validator);
    $errors = $this->parseValidateResponse($response, $data);
    return $errors;
  }


  public function getRsiValidate($carrierCode, array $data) {
    $validate = new \RocketShipIt\AddressValidate($carrierCode);

    $street = $data['street'];
    $addr1 = '';
    $addr2 = '';
    if (is_array($street)) {
      $addr1 = $street[0];
      $addr2 = $street[1];
    }
    else {
      $addr1 = $street;
    }

    $validate->setParameter('toAddr1', $addr1);
    $validate->setParameter('toAddr2', $addr2);
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

  function _containsAddressData(array $data) {
    return (array_key_exists('city', $data)
	    && array_key_exists('postcode', $data)
	    && array_key_exists('country_id', $data));
  }
}

