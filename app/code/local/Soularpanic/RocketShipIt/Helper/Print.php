<?php 
class Soularpanic_RocketShipIt_Helper_Print
extends Mage_Core_Helper_Abstract {
  public function getThermalUrl() {
    $attrPath = 'carriers/rocketshipit_global/thermal_printer_address';
    $url = Mage::getStoreConfig($attrPath);
    return $url;
  }

  public function printThermal($page, $url) {
    if (!$url) {
      $url = $this->getThermalUrl();
    }

    $encodedPage = base64_encode($page);
    $postData = 'label='.$encodedPage;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
}
