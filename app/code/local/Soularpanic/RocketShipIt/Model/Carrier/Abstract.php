<?php

abstract class Soularpanic_RocketShipIt_Model_Carrier_Abstract
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_superCode = 'rocketshipit';

  abstract public function getCarrierSubCode();

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    if(!Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/active')) {
      return false;
    }

    $carrierCode = $this->getCarrierSubCode();
    $useNegotiatedRate = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/useNegotiatedRates');
    $handling = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/handling');

    $helper = Mage::helper('rocketshipit/rates');
    $rateMask = $this->getAllowedRateMask();

    $simpleRates = $helper->getSimpleRates($carrierCode,
					   $request,
					   $useNegotiatedRate,
					   null,
					   $handling,
					   $rateMask);
    
    return $simpleRates;
  }

  public function getAllowedMethods() {
    return array('rocketshipit' => $this->getConfigData('name'));
  }

  public function getFullCarrierCode()
  {
    return $this->_superCode.'_'.$this->getCarrierSubCode();
  }

  function getAllowedRateMask() {
    $filterConfigAttr = $this->_getFilterConfigAttr();
    if ($filterConfigAttr === null) {
      return null;
    }

    $allowedRateCodesStr = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/'.$filterConfigAttr);
    if (empty($allowedRateCodesStr)) {
      return null;
    }

    $allowedRateCodes = explode(',', $allowedRateCodesStr);
    return $allowedRateCodes;
  }

  function _getFilterConfigAttr() {
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
    $filterConfigAttr = null;
    if (strpos($currentUrl, 'checkout') !== false) {
      $filterConfigAttr = 'checkout_filter';
    }
    elseif (strpos($currentUrl, 'admin') !== false) {
      $filterConfigAttr = 'admin_filter';
    }
    return $filterConfigAttr;
  }
}
?>
