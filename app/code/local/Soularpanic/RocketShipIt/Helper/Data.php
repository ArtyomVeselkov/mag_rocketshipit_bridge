<?php
class Soularpanic_RocketShipIt_Helper_Data
extends Mage_Core_Helper_Abstract {

  protected $_code = 'rocketshipit';

  function __construct() {
    $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'_global'.'/path');
    require_once($rocketShipItPath.'/autoload.php');
  }

  function _extractAddrFromMageShippingModelRateRequest(Mage_Shipping_Model_Rate_Request $request) {
    $addr = array('zip' => $request->getDestPostcode(),
		  'state' => $request->getDestRegionCode(),
		  'country' => $request->getDestCountryId(),
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
}
?>

