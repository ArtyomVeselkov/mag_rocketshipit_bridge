<?php 
class Soularpanic_RocketShipIt_Helper_Validation_Ups
extends Soularpanic_RocketShipIt_Helper_Validation_Abstract {
  function getCarrierCode() {
    return 'ups';
  }

  function getValidateResponse($rsiValidate) {
    return $rsiValidate->validateStreetLevel();
  }
  
  function parseValidateResponse($response, &$data) {
    $errors = array();
    $addrResp = $response['AddressValidationResponse'];
    if ($addrResp['Response']['ResponseStatusCode'] === '1') { 
      // ups
      // or maybe $addrResp['ResponseStatusDescription' === 'Success'
      $verifiedAddress = $addrResp['AddressKeyFormat'];
      if ($verifiedAddress === null) { 
	// this seems to happen when the street addr is botched
	$errors[] = "Unable to verify your street address.  Please check it and try again.";
      }
      else {
	if (is_array($data['street'])) {
	  $data['street'][0] = $verifiedAddress['AddressLine'];
	}
	else {
	  $data['street'] = $varifiedAddress['AddressLine'];
	}
	$data['city'] = $verifiedAddress['PoliticalDivision2'];
	$verifiedStateCode = $verifiedAddress['PoliticalDivision1'];
	if ($verifiedStateCode !== $stateCode) {
	  $correctedRegion = Mage::getModel('directory/region')
			 ->load($verifiedStateCode, 'code');
	  $data['region_id'] = $correctedRegion->getId();
	}
	$data['postcode'] = $verifiedAddress['PostcodePrimaryLow'];
      }
    }
    else {
      $errors[] = $addrResp['Response']['Error']['ErrorDescription'];
    }

    return $errors;
  }
}
?>
