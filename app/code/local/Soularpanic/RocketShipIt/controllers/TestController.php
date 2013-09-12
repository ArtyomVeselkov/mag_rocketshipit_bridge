<?php 
class Soularpanic_RocketShipIt_TestController
extends Mage_Core_Controller_Front_Action {
  public function testModelAction() {
    $params = $this->getRequest()->getParams();
    $order = Mage::getModel('rocketshipit/orderExtras');
    echo("Loading order with ID of ".$params['id']);
    $order->load($params['id']);
    $data = $order->getData();
    var_dump($data);
  }
}
 ?>
