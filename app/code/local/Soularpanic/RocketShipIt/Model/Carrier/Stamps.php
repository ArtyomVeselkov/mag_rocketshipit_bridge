<?php

class Soularpanic_RocketShipIt_Model_Carrier_Stamps
extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_code = 'rocketshipit_stamps';

  public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
    return parent::collectRates($request);
  }

  public function getCarrierSubCode()
  {
    return 'stamps';
  }
  
  public function getMethods() {
    return array(
      'US-FC:Postcard' => 'First Class Mail - Postcard',
      'US-FC:Letter' => 'First Class Mail - Letter',
      'US-FC:Large-Envelope-or-Flat' => 'First Class Mail - Large Envelope or Flat',
      'US-FC:Large-Package' => 'First Class Mail - Large Package',
      'US-FC:Thick-Envelope' => 'First Class Mail - Thick Envelope',
      'US-FC:Package' => 'First Class Mail - Package',
      'US-MM:Large-Envelope-or-Flat' => 'Media Mail - Large Envelope or Flat',
      'US-MM:Thick-Envelope' => 'Media Mail - Thick Envelope',
      'US-MM:Large-Package' => 'Media Mail - Large Package',
      'US-MM:Package' => 'Media Mail - Package',
      'US-PM:Flat-Rate-Envelope' => 'Priority Mail - Flat Rate Envelope',
      'US-PM:Flat-Rate-Padded-Envelope' => 'Priority Mail - Flat Rate Padded Envelope',
      'US-PM:Small-Flat-Rate-Box' => 'Priority Mail - Small Flat Rate Box',
      'US-PM:Legal-Flat-Rate-Envelope' => 'Priority Mail - Legal Flat Rate Envelope',
      'US-PM:Letter' => 'Priority Mail - Letter',
      'US-PM:Large-Envelope-or-Flat' => 'Priority Mail - Large Envelope or Flat',
      'US-PM:Thick-Envelope' => 'Priority Mail - Thick Envelope',
      'US-PM:Package' => 'Priority Mail - Package',
      'US-PP:Thick-Envelope' => 'Parcel Post - Thick Envelope',
      'US-PP:Package' => 'Parcel Post - Package',
      'US-PM:Regional-Rate-Box-A' => 'Priority Mail - Regional Rate Box A',
      'US-PM:Regional-Rate-Box-B' => 'Priority Mail - Regional Rate Box B',
      'US-PM:Flat-Rate-Box' => 'Priority Mail - Flat Rate Box',
      'US-PM:Large-Flat-Rate-Box' => 'Priority Mail - Large Flat Rate Box',
      'US-PP:Large-Package' => 'Parcel Post - Large Package',
      'US-XM:Legal-Flat-Rate-Envelope' => 'Express Mail - Legal Flat Rate Envelope',
      'US-XM:Flat-Rate-Envelope' => 'Express Mail - Flat Rate Envelope',
      'US-XM:Flat-Rate-Padded-Envelope' => 'Express Mail - Flat Rate Padded Envelope',
      'US-XM:Large-Envelope-or-Flat' => 'Express Mail - Large Envelope or Flat',
      'US-XM:Letter' => 'Express Mail - Letter',
      'US-XM:Large-Package' => 'Express Mail - Large Package',
      'US-XM:Thick-Envelope' => 'Express Mail - Thick Envelope',
      'US-XM:Package' => 'Express Mail - Package',
      'US-PM:Regional-Rate-Box-C' => 'Priority Mail - Regional Rate Box C',
      'US-PM:Large-Package' => 'Priority Mail - Large Package',
      'US-XM:Flat-Rate-Box' => 'Express Mail - Flat Rate Box',
      'US-PP:Oversized-Package' => 'Parcel Post - Oversized Package',
      'US-FCI:Letter' => 'First Class International Mail - Letter',
      'US-FCI:Large-Envelope-or-Flat' => 'First Class International Mail - Large Envelope or Flat',
      'US-FCI:Thick-Envelope' => 'First Class International Mail - Thick Envelope',
      'US-FCI:Package' => 'First Class International Mail - Package',
      'US-FCI:Large-Package' => 'First Class International Mail - Large Package',
      'US-FCI:Oversized-Package' => 'First Class International Mail - Oversized Package',
      'US-PMI:Large-Envelope-or-Flat' => 'Priority Mail International - Large Envelope or Flat',
      'US-PMI:Thick-Envelope' => 'Priority Mail International - Thick Envelope',
      'US-PMI:Package' => 'Priority Mail International - Package',
      'US-PMI:Flat-Rate-Box' => 'Priority Mail International - Flat Rate Box',
      'US-PMI:Small-Flat-Rate-Box' => 'Priority Mail International - Small Flat Rate Box',
      'US-PMI:Large-Flat-Rate-Box' => 'Priority Mail International - Large Flat Rate Box',
      'US-PMI:Flat-Rate-Envelope' => 'Priority Mail International - Flat Rate Envelope',
      'US-PMI:Flat-Rate-Padded-Envelope' => 'Priority Mail International - Flat Rate Padded Envelope',
      'US-PMI:Large-Package' => 'Priority Mail International - Large Package',
      'US-PMI:Oversized-Package' => 'Priority Mail International - Oversized Package',
      'US-PMI:Legal-Flat-Rate-Envelope' => 'Priority Mail International - Legal Flat Rate Envelope',
      'US-EMI:Large-Envelope-or-Flat' => 'Express Mail International - Large Envelope or Flat',
      'US-EMI:Thick-Envelope' => 'Express Mail International - Thick Envelope',
      'US-EMI:Package' => 'Express Mail International - Package',
      'US-EMI:Flat-Rate-Box' => 'Express Mail International - Flat Rate Box',
      'US-EMI:Flat-Rate-Envelope' => 'Express Mail International - Flat Rate Envelope',
      'US-EMI:Flat-Rate-Padded-Envelope' => 'Express Mail International - Flat Rate Padded Envelope',
      'US-EMI:Large-Package' => 'Express Mail International - Large Package',
      'US-EMI:Oversized-Package' => 'Express Mail International - Oversized Package',
      'US-EMI:Legal-Flat-Rate-Envelope' => 'Express Mail International - Legal Flat Rate Envelope'
    );
  }

  public function getLabelFormats() {
    return array(
      'Gif' => 'Standard - Gif'
      ,'Epl' => 'Thermal - EPL'
    );
  }
}

