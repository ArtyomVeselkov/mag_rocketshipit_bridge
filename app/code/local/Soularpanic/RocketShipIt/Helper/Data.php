<?php
class Soularpanic_RocketShipIt_Helper_Data
extends Mage_Core_Helper_Abstract {

  protected $_code = 'rocketshipit';

  function __construct() {
    $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'_global'.'/path');
    require_once($rocketShipItPath.'/RocketShipIt.php');
  }

  function _extractAddrFromMageShippingModelRateRequest(Mage_Shipping_Model_Rate_Request $request) {
    $addr = array('zip' => $request->getDestPostcode(),
		  'state' => $request->getDestRegionCode(),
		  'country' => $request->getCountryId(),
		  'weight' => $request->getPackageWeight());
    return $addr;
  }

  function _extractAddrFromMageSalesModelOrderAddress(Mage_Sales_Model_Order_Address $addrObj) {
    $addr = array('zip' => $addrObj->getPostcode(),
		  'state' => $addrObj->getRegionCode(),
		  'country' => $addrObj->getCountryId());
    return $addr;
  }

  public function parseShippingMethod($shippingMethod) {
    
    $split = explode('_', $shippingMethod);
    return array('carrier' => $split[1],
		 'service' => $split[2]);
  }

  public function getFullCarrierCode($carrierSubCode) {
    return $this->_code.'_'.$carrierSubCode;
  }

  public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
    $rsiShipment = new RocketShipShipment($carrierCode);

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
}
?>

