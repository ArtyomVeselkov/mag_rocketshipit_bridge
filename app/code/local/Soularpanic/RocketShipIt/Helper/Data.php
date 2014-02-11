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

  /**
   *    EMERG   = 0;  // Emergency: system is unusable
   *    ALERT   = 1;  // Alert: action must be taken immediately
   *    CRIT    = 2;  // Critical: critical conditions
   *    ERR     = 3;  // Error: error conditions
   *    WARN    = 4;  // Warning: warning conditions
   *    NOTICE  = 5;  // Notice: normal but significant condition
   *    INFO    = 6;  // Informational: informational messages
   *    DEBUG   = 7;  // Debug: debug messages
   */
  public function log($msg, $level = null) {
    Mage::log($msg, $level, 'rocketshipit_shipments.log');
  }

  public function fetchMapEntry($entryKey, $entryValue, $map) {
    $foundEntry = null;
    foreach ($map as $entry) {
      if ($entry[$entryKey] === $entryValue) {
	$foundEntry = $entry;
	break;
      }
    }

    return $foundEntry;
  }
}
