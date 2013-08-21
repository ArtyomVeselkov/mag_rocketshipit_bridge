<?php
class Soularpanic_RocketShipIt_Helper_Data extends Mage_Core_Helper_Abstract {

  protected $_code = 'rocketshipit';

  function __construct() {
    $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'/path');
    require_once($rocketShipItPath.'/RocketShipIt.php');
  }

  public function getRSIRate($courier,
			     Mage_Shipping_Model_Rate_Request $request)
  {
    $rsiRate = new RocketShipRate($courier);

    $destZip = $request->getDestPostcode();
    $rsiRate->setParameter('toCode', $destZip);

    $destState = $request->getDestRegionCode();
    $rsiRate->setParameter('toState', $destState);

    $destCountry = $request->getCountryId();
    $rsiRate->setParameter('toCountry', $destCountry);
    
    $packageWeight = $request->getPackageWeight();
    $rsiRate->setParameter('weight', $packageWeight);

    $rsiRate->setParameter('residentialAddressIndicator','0');

    return $rsiRate;
  }

  public function asRSIShipment($courier, Mage_Sales_Model_Order_Address $address) {
    $rsiShipment = new RocketShipShipment($courier);

    $toName = $address->getName();
    $rsiShipment->setParameter('toCompany', $toName);
    
    $toPhone = $address->getTelephone();
    $rsiShipment->setParameter('toPhone', $toPhone);

    $toStreet1 = $address->getStreet1();
    $rsiShipment->setParameter('toAddr1', $toStreet1);

    $toStreet2 = $address->getStreet2();
    $rsiShipment->setParameter('toAddr2', $toStreet2);

    $toStreet3 = $address->getStreet3();
    $rsiShipment->setParameter('toAddr3', $toStreet3);

    $toCity = $address->getCity();
    $rsiShipment->setParameter('toCity', $toCity);

    $toState = $address->getRegionCode();
    $rsiShipment->setParameter('toState', $toState);

    $toZip = $address->getPostcode();
    $rsiShipment->setParameter('toCode', $toZip);

    return $rsiShipment;
  }

  /* public function populateRsiAddress($courier, Mage_Sales_Model_Order_Address $address, $rsiObj) { */
  /*   $toName = $address->getName(); */
  /*   $rsiObj->setParameter('toCompany', $toName); */
    
  /*   $toPhone = $address->getTelephone(); */
  /*   $rsiObj->setParameter('toPhone', $toPhone); */

  /*   $toStreet1 = $address->getStreet1(); */
  /*   $rsiObj->setParameter('toAddr1', $toStreet1); */

  /*   $toStreet2 = $address->getStreet2(); */
  /*   $rsiObj->setParameter('toAddr2', $toStreet2); */

  /*   $toStreet3 = $address->getStreet3(); */
  /*   $rsiObj->setParameter('toAddr3', $toStreet3); */

  /*   $toCity = $address->getCity(); */
  /*   $rsiObj->setParameter('toCity', $toCity); */

  /*   $toState = $address->getRegionCode(); */
  /*   $rsiObj->setParameter('toState', $toState); */

  /*   $toZip = $address->getPostcode(); */
  /*   $rsiObj->setParameter('toCode', $toZip); */

  /*   $toCountry = $address->getCountryId(); */
  /*   $rsiObj->setParameter('toCountry', $toCountry); */
    
  /*   return $rsiObj; */
  /* } */
}
?>