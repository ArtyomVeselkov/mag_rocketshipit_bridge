<?php 
class Soularpanic_RocketShipIt_TestController
extends Mage_Core_Controller_Front_Action {
  public function testDiacriticAction() {
    $str = 'Vučetićev prilaz 3 Zagreb, Zagrebačka, 10000';
    $maskArr = array(0x80, 0x10ffff, 0, 0xffffff);
    $toUtf = mb_encode_numericentity($str, $maskArr, 'UTF-8');
    $toIso = mb_encode_numericentity($str, $maskArr, 'ISO-8859-1');
    $fromUtfToIso = mb_encode_numericentity(utf8_decode($str), $maskArr, 'ISO-8859-1');
    echo("original string: $str <br/>");
    echo("toUtf: $toUtf <br/>");
    echo("toIso: $toIso <br/>");
    echo("fromUtfToIso: $fromUtfToIso <br/>");
  }


  public function testFooAction() {
    $dataHelper = Mage::helper('rocketshipit/data'); // load rocketshipit libs
    $shipment = new \RocketShipIt\Shipment('ups');
    foreach ($shipment->parameters as $key=>$value) {
      $newVal = print_r($value, true).' hey!';
      $shipment->setParameter($key, $newVal);
      echo("[$key] : {$value}<br/>");
    }
    echo(' o hai');
  }
}
?>
