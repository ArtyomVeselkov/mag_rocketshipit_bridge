<?php 
class Soularpanic_RocketShipIt_Helper_Validation_Stamps
extends Soularpanic_RocketShipIt_Helper_Validation_Abstract {
  function getCarrierCode() {
    return 'stamps';
  }
  function getValidateResponse($rsiValidate) {
    return $rsiValidate->validate();
  }
  function parseValidateResponse($response, &$data) {
    $errors = array();
    if ($response->CityStateZipOK === false) { 
      $errors[] = "";
    }

    if ($response->AddressMatch === true) {
      $verifiedAddress = $response->Address;

      if (is_array($data['street'])) {
	$data['street'][0] = $verifiedAddress->Address1;
	$data['street'][1] = $verifiedAddress->Address2;
      }
      else {
	$data['street'] = $verifiedAddress->Address1;
      }
	  
      $data['city'] = $verifiedAddress->City;
      $data['postcode'] = $verifiedAddress->ZIPCode;
    }
    else {
      $errors[] = "Unable to verify your street address.  Please check it and try again.";
    }
    return $errors;
  }

}

