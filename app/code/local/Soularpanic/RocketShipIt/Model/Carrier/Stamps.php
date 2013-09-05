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
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
    if (strpos($currentUrl, 'checkout') >= 0) {
      $allowedRateCodesStr = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/checkout_filter');
      $allowedRateCodes = explode(',', $allowedRateCodesStr);
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

  public function getMethods() {
    return array(
      'US-FC:Postcard' => 'USPS First Class Mail - Postcard',
      'US-FC:Letter' => 'USPS First Class Mail - Letter',
      'US-FC:Large_Envelope_or_Flat' => 'USPS First Class Mail - Large Envelope or Flat',
      'US-FC:Large_Package' => 'USPS First Class Mail - Large Package',
      'US-FC:Thick_Envelope' => 'USPS First Class Mail - Thick Envelope',
      'US-FC:Package' => 'USPS First Class Mail - Package',
      'US-MM:Large_Envelope_or_Flat' => 'USPS Media Mail - Large Envelope or Flat',
      'US-MM:Thick_Envelope' => 'USPS Media Mail - Thick Envelope',
      'US-MM:Large_Package' => 'USPS Media Mail - Large Package',
      'US-MM:Package' => 'USPS Media Mail - Package',
      'US-PM:Flat_Rate_Envelope' => 'USPS Priority Mail - Flat Rate Envelope',
      'US-PM:Flat_Rate_Padded_Envelope' => 'USPS Priority Mail - Flat Rate Padded Envelope',
      'US-PM:Small_Flat_Rate_Box' => 'USPS Priority Mail - Small Flat Rate Box',
      'US-PM:Legal_Flat_Rate_Envelope' => 'USPS Priority Mail - Legal Flat Rate Envelope',
      'US-PM:Letter' => 'USPS Priority Mail - Letter',
      'US-PM:Large_Envelope_or_Flat' => 'USPS Priority Mail - Large Envelope or Flat',
      'US-PM:Thick_Envelope' => 'USPS Priority Mail - Thick Envelope',
      'US-PM:Package' => 'USPS Priority Mail - Package',
      'US-PP:Thick_Envelope' => 'USPS Parcel Post - Thick Envelope',
      'US-PP:Package' => 'USPS Parcel Post - Package',
      'US-PM:Regional_Rate_Box_A' => 'USPS Priority Mail - Regional Rate Box A',
      'US-PM:Regional_Rate_Box_B' => 'USPS Priority Mail - Regional Rate Box B',
      'US-PM:Flat_Rate_Box' => 'USPS Priority Mail - Flat Rate Box',
      'US-PM:Large_Flat_Rate_Box' => 'USPS Priority Mail - Large Flat Rate Box',
      'US-PP:Large_Package' => 'USPS Parcel Post - Large Package',
      'US-XM:Legal_Flat_Rate_Envelope' => 'USPS Express Mail - Legal Flat Rate Envelope',
      'US-XM:Flat_Rate_Envelope' => 'USPS Express Mail - Flat Rate Envelope',
      'US-XM:Flat_Rate_Padded_Envelope' => 'USPS Express Mail - Flat Rate Padded Envelope',
      'US-XM:Large_Envelope_or_Flat' => 'USPS Express Mail - Large Envelope or Flat',
      'US-XM:Letter' => 'USPS Express Mail - Letter',
      'US-XM:Large_Package' => 'USPS Express Mail - Large Package',
      'US-XM:Thick_Envelope' => 'USPS Express Mail - Thick Envelope',
      'US-XM:Package' => 'USPS Express Mail - Package',
      'US-PM:Regional_Rate_Box_C' => 'USPS Priority Mail - Regional Rate Box C',
      'US-PM:Large_Package' => 'USPS Priority Mail - Large Package',
      'US-XM:Flat_Rate_Box' => 'USPS Express Mail - Flat Rate Box',
      'US-PP:Oversized_Package' => 'USPS Parcel Post - Oversized Package'
    );
  }
}
?>
