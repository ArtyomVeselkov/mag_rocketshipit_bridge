<?php

class Soularpanic_RocketShipIt_Model_Carrier_Stamps
extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  public function getCarrierSubCode()
  {
    return 'stamps';
  }

  public function getMethods() {
    return array(
/*
=> 'USPS First Class Mail - Postcard',
=> 'USPS First Class Mail - Letter',
=> 'USPS First Class Mail - Large Envelope or Flat',
=> 'USPS First Class Mail - Large Package',
=> 'USPS First Class Mail - Thick Envelope',
=> 'USPS First Class Mail - Package',
=> 'USPS Media Mail - Large Envelope or Flat',
=> 'USPS Media Mail - Thick Envelope',
=> 'USPS Media Mail - Large Package',
=> 'USPS Media Mail - Package',
=> 'USPS Priority Mail - Flat Rate Envelope',
=> 'USPS Priority Mail - Flat Rate Padded Envelope',
=> 'USPS Priority Mail - Small Flat Rate Box',
=> 'USPS Priority Mail - Legal Flat Rate Envelope',
=> 'USPS Priority Mail - Letter',
=> 'USPS Priority Mail - Large Envelope or Flat',
=> 'USPS Priority Mail - Thick Envelope',
=> 'USPS Priority Mail - Package',
=> 'USPS Parcel Post - Thick Envelope',
=> 'USPS Parcel Post - Package',
=> 'USPS Priority Mail - Regional Rate Box A',
=> 'USPS Priority Mail - Regional Rate Box B',
=> 'USPS Priority Mail - Flat Rate Box',
=> 'USPS Priority Mail - Large Flat Rate Box',
=> 'USPS Parcel Post - Large Package',
=> 'USPS Express Mail - Legal Flat Rate Envelope',
=> 'USPS Express Mail - Flat Rate Envelope',
=> 'USPS Express Mail - Flat Rate Padded Envelope',
=> 'USPS Express Mail - Large Envelope or Flat',
=> 'USPS Express Mail - Letter',
=> 'USPS Express Mail - Large Package',
=> 'USPS Express Mail - Thick Envelope',
=> 'USPS Express Mail - Package',
=> 'USPS Priority Mail - Regional Rate Box C',
=> 'USPS Priority Mail - Large Package',
=> 'USPS Express Mail - Flat Rate Box',
=> 'USPS Parcel Post - Oversized Package'
*/
    );
  }
}
?>
