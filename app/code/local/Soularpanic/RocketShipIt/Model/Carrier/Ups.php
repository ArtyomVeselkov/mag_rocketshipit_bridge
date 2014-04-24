<?php

class Soularpanic_RocketShipIt_Model_Carrier_UPS
  extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{

  protected $_code = 'rocketshipit_ups';
  
  public function getCarrierSubCode()
  {
    return 'ups';
  }

  public function getMethods() {
    return array(
      '01' => 'Next Day Air',
      '02' => '2nd Day Air',
      '03' => 'Ground',
      '07' => 'Worldwide Express',
      '08' => 'Worldwide Expedited',
      '11' => 'Standard',
      '12' => '3 Day Select',
      '13' => 'Next Day Air Saver',
      '14' => 'Next Day Air Early A.M.',
      '54' => 'Worldwide Express Plus',
      '59' => 'Second Day Air A.M.',
      '65' => 'Worldwide Saver'
    );
  }

  public function getLabelFormats() {
    return array(
      'GIF' => 'Standard - Gif'
      ,'EPL' => 'Thermal - EPL'
    );
  }
}

