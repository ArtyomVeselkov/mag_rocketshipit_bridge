<?php

abstract class Soularpanic_RocketShipIt_Model_Carrier_Abstract
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_superCode = 'rocketshipit';

  abstract public function getCarrierSubCode();

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    /* if(!Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/active')) {
    return false;
    } */

    if (!$this->getConfigData('active')) {
      return false;
    }

    if (!$this->mayShipToDestination($request)) {
      return false;
    }

    $carrierCode = $this->getCarrierSubCode();
    $useNegotiatedRate = $this->_getUseNegotiatedRates();
    
    $handling = $this->_getHandlingCost();

    $helper = Mage::helper('rocketshipit/rates');
    $rateMask = $this->getAllowedRateMask();
    $freeRates = $this->getFreeRateMask();

    $simpleRates = $helper->getSimpleRates($carrierCode,
					                       $request,
					                       $useNegotiatedRate,
					                       null,
					                       $handling,
					                       $rateMask,
					                       $freeRates);
    
    return $simpleRates;
  }

  public function getAllowedMethods() {
    return array('rocketshipit' => $this->getConfigData('name'));
  }

  public function getFullCarrierCode()
  {
    return $this->_superCode.'_'.$this->getCarrierSubCode();
  }

  function mayShipToDestination(Mage_Shipping_Model_Rate_Request $request) {
    if (!$this->getConfigData('sallowspecific')) {
      return true;
    }

    $allowedCountries = explode(',', $this->getConfigData('specificcountry'));
    $destCountry = $request->getDestCountryId();

    return in_array($destCountry, $allowedCountries);
  }

  function getFreeRateMask() {
    $freeRateStr = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/free_shipping_filter');
    if (!$freeRateStr) {
      return null;
    }

    $freeRateArr = explode(',', $freeRateStr);
    return $freeRateArr;
  }

  function getAllowedRateMask() {
    $filterConfigAttr = $this->_getFilterConfigAttr();
    if ($filterConfigAttr === null) {
      return null;
    }

    $allowedRateCodesStr = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/'.$filterConfigAttr);
    if (!$allowedRateCodesStr) {
      return null;
    }

    $allowedRateCodes = explode(',', $allowedRateCodesStr);
    return $allowedRateCodes;
  }

  function _getHandlingCost() {
    $consumerType = $this->_getRateConsumerType();
    if ($consumerType === 'customer') {
      $handling = Mage::getStoreConfig('carriers/rocketshipit_global/handling_base');
      return $handling;
    }
    return 0;
  }

  function _getUseNegotiatedRates() {
    $consumerType = $this->_getRateConsumerType();
    $useNegotiatedRate = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/'.$consumerType.'_useNegotiatedRates');    
    return $useNegotiatedRate;
  }

  function _getFilterConfigAttr() {
    $consumerType = $this->_getRateConsumerType();
    $filterConfigAttr = null;
    if ($consumerType === 'customer') {
      $filterConfigAttr = 'checkout_filter';
    }

    if ($consumerType === 'admin') {
      $filterConfigAttr = 'admin_filter';
    }
    
    return $filterConfigAttr;
  }

  function _getRateConsumerType() {
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
    $consumerType = 'customer';
    if (strpos($currentUrl, 'checkout') !== false
	    || strpos($currentUrl, 'paypal') !== false) {
      $consumerType = 'customer';
    }
    elseif (strpos($currentUrl, 'admin') !== false) {
      $consumerType = 'admin';
    }
    return $consumerType;
  }
}
