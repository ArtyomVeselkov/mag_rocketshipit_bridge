<?php 
class Soularpanic_RocketShipIt_Helper_Handling
extends Mage_Core_Helper_Abstract {

  const NO_HANDLING = 'none';
  const SIGN = 'sign';
  const SIGN_AND_INSURE = 'signAndInsure';

  const CONFIG_LEADER = 'carriers/rocketshipit_global/';
  const DISPLAY_KEY = 'display';
  const COST_KEY = 'cost';

  public function getHandlingOptions(Mage_Sales_Model_Quote_Address $quoteAddress) {
    $options = array();
    if (!$this->_getConfig('handling_active')) {
      return $options;
    }

    $options[self::NO_HANDLING] = array(
      self::DISPLAY_KEY => $this->getHandlingDisplay(self::NO_HANDLING),
      self::COST_KEY    => 0.00
    );
      
    if ($this->_getConfig('signature_active')) {
      $options[self::SIGN] = array(
	self::DISPLAY_KEY => $this->getHandlingDisplay(self::SIGN),
	self::COST_KEY    => $this->calculateSignCost($quoteAddress)
      );
    }

    if ($this->_getConfig('signAndInsure_active')) {
      $cost = $this->calculateSignCost($quoteAddress) + $this->calculateInsureCost($quoteAddress);
      $options[self::SIGN_AND_INSURE] = array(
	self::DISPLAY_KEY => $this->getHandlingDisplay(self::SIGN_AND_INSURE),
	self::COST_KEY    => $cost
      );
    }
    
    $this->_log("options: ".print_r($options, true));

    return $options;
  }

  public function getHandlingDisplay($handlingCode) {
    if ($handlingCode === self::NO_HANDLING
	|| $handlingCode === self::SIGN
	|| $handlingCode === self::SIGN_AND_INSURE) {
      return $this->_getConfig($handlingCode . '_display');
    }
    return null;
  }

  public function calculateHandling(Mage_Sales_Model_Quote_Address $quoteAddress) {
    $handlingCode = $quoteAddress->getHandlingCode();
    
    if (empty($handlingCode)) {
      return 0.00;
    }

    if ($handlingCode === self::SIGN) {
      return $this->calculateSignCost($quoteAddress);
    }
    elseif ($handlingCode === self::SIGN_AND_INSURE) {
      $sign = $this->calculateSignCost($quoteAddress);
      $insure = $this->calculateInsureCost($quoteAddress);
      $this->_log("sign + insure = $sign + $insure");
      return $sign + $insure;
    }
    return 0.00;
  }

  public function calculateSignCost(Mage_Sales_Model_Quote_Address $quoteAddress) {
    return $this->calculateCost($quoteAddress, 'signature');
  }

  public function calculateInsureCost(Mage_Sales_Model_Quote_Address $quoteAddress) {
    return $this->calculateCost($quoteAddress, 'insurance');
  }

  public function calculateCost(Mage_Sales_Model_Quote_Address $quoteAddress, $property) {
    $this->_log("Beginning to calculate $property cost");
    $type = $this->_getConfig($property.'_type');
    $val = $this->_getConfig($property.'_value');
    if (empty($val)) {
      return 0.00;
    }
    if ($type === Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_FIXED) {
      return $val;
    }
    if ($type === Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_PERCENT) {
      return ($val / 100) * $quoteAddress->getSubtotal();
    }
    return 0.00;
  }

  public function isActiveOption(Mage_Sales_Model_Quote_Address $address, $optionKey) {
    $activeOption = $address->getHandlingCode();
    if (empty($activeOption)) {
      return $optionKey === self::NO_HANDLING;
    }
    return $activeOption === $optionKey;
  }

  public function getSectionHeader() {
    return $this->_getConfig('handling_checkout_header');
  }

  private function _getConfig($property) {
    return Mage::getStoreConfig(self::CONFIG_LEADER.$property);
  }

  function _log($msg) {
    Mage::log($msg, null, 'rocketshipit_shipments.log');
  }
}

