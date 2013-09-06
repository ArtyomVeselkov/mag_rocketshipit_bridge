<?php

class Soularpanic_RocketShipIt_Model_Carrier_Stamps
extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  public function getCarrierSubCode()
  {
    return 'stamps';
  }
  
  public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
    $rates = parent::collectRates();
    $allowedRateCodes = $this->_getAllowedRateCodes();
    if ($allowedRateCodes != null) {
      $filteredRates = Mage::getModel('shipping/rate_result');
      foreach ($rates->getAllRates() as $rate) {
	if (in_array($rate->getMethod(), $allowedRateCodes)) {
	  $filteredRates->append($rate);
	}
      }
      return $filteredRates;
    }
    return $rates;
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

  function _getAllowedRateCodes() {
    $filterConfigAttr = $this->_getFilterConfigAttr();
    if ($filterConfigAttr === null) {
      return null;
    }
    $allowedRateCodesStr = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/'.$filterConfigAttr);
    $allowedRateCodes = explode(',', $allowedRateCodesStr);
    return $allowedRateCodes;
  }

  public function getMethods() {
    return array(
      'US-FC:Postcard' => 'USPS First Class Mail - Postcard',
      'US-FC:Letter' => 'USPS First Class Mail - Letter',
      'US-FC:Large-Envelope-or-Flat' => 'USPS First Class Mail - Large Envelope or Flat',
      'US-FC:Large-Package' => 'USPS First Class Mail - Large Package',
      'US-FC:Thick-Envelope' => 'USPS First Class Mail - Thick Envelope',
      'US-FC:Package' => 'USPS First Class Mail - Package',
      'US-MM:Large-Envelope-or-Flat' => 'USPS Media Mail - Large Envelope or Flat',
      'US-MM:Thick-Envelope' => 'USPS Media Mail - Thick Envelope',
      'US-MM:Large-Package' => 'USPS Media Mail - Large Package',
      'US-MM:Package' => 'USPS Media Mail - Package',
      'US-PM:Flat-Rate-Envelope' => 'USPS Priority Mail - Flat Rate Envelope',
      'US-PM:Flat-Rate-Padded-Envelope' => 'USPS Priority Mail - Flat Rate Padded Envelope',
      'US-PM:Small-Flat-Rate-Box' => 'USPS Priority Mail - Small Flat Rate Box',
      'US-PM:Legal-Flat-Rate-Envelope' => 'USPS Priority Mail - Legal Flat Rate Envelope',
      'US-PM:Letter' => 'USPS Priority Mail - Letter',
      'US-PM:Large-Envelope-or-Flat' => 'USPS Priority Mail - Large Envelope or Flat',
      'US-PM:Thick-Envelope' => 'USPS Priority Mail - Thick Envelope',
      'US-PM:Package' => 'USPS Priority Mail - Package',
      'US-PP:Thick-Envelope' => 'USPS Parcel Post - Thick Envelope',
      'US-PP:Package' => 'USPS Parcel Post - Package',
      'US-PM:Regional-Rate-Box-A' => 'USPS Priority Mail - Regional Rate Box A',
      'US-PM:Regional-Rate-Box-B' => 'USPS Priority Mail - Regional Rate Box B',
      'US-PM:Flat-Rate-Box' => 'USPS Priority Mail - Flat Rate Box',
      'US-PM:Large-Flat-Rate-Box' => 'USPS Priority Mail - Large Flat Rate Box',
      'US-PP:Large-Package' => 'USPS Parcel Post - Large Package',
      'US-XM:Legal-Flat-Rate-Envelope' => 'USPS Express Mail - Legal Flat Rate Envelope',
      'US-XM:Flat-Rate-Envelope' => 'USPS Express Mail - Flat Rate Envelope',
      'US-XM:Flat-Rate-Padded-Envelope' => 'USPS Express Mail - Flat Rate Padded Envelope',
      'US-XM:Large-Envelope-or-Flat' => 'USPS Express Mail - Large Envelope or Flat',
      'US-XM:Letter' => 'USPS Express Mail - Letter',
      'US-XM:Large-Package' => 'USPS Express Mail - Large Package',
      'US-XM:Thick-Envelope' => 'USPS Express Mail - Thick Envelope',
      'US-XM:Package' => 'USPS Express Mail - Package',
      'US-PM:Regional-Rate-Box-C' => 'USPS Priority Mail - Regional Rate Box C',
      'US-PM:Large-Package' => 'USPS Priority Mail - Large Package',
      'US-XM:Flat-Rate-Box' => 'USPS Express Mail - Flat Rate Box',
      'US-PP:Oversized-Package' => 'USPS Parcel Post - Oversized Package'
    );
  }
}
?>
