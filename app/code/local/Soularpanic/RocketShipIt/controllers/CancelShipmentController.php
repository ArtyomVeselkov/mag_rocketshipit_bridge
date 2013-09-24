<?php 
class Soularpanic_RocketShipIt_CancelShipmentController
extends Mage_Adminhtml_Controller_Action {
  public function cancelShipmentAction() {
    $shipmentId = $this->getRequest()->getParam('shipmentId');
    $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

    $order = $shipment->getOrder();
    $dataHelper = Mage::helper('rocketshipit/data');
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());
    $carrier = $shippingMethod['carrier'];

    echo("carrier is $carrier </br>");

    $voidCode = $shipment->getRocketshipitId();

    echo("voidCode is $voidCode </br>");
    if (empty($voidCode)) {
      //Mage::throwException("There is no recorded RocketShipIt Transaction ID for this shipment ({$shipment->getIncrementId()}); I cannot void it.");
       echo("Error: There is no recorded RocketShipIt Transaction ID for this shipment ({$shipment->getIncrementId()}); I cannot void it.");
      return;
    }

    $void = new \RocketShipIt\Void($carrier);
    try {
      $voidResp = $void->voidShipment($voidCode);
    }
    catch (Exception $e) {
      echo ("Error: exception! ".print_r($e, true)."</br>");
      return;
    }
    echo("void response: " . print_r($voidResp, true) . "</br>");
    
    //$x = $voidResp['VoidShipmentResponse']['Response']['Error'];
    //echo("x is ".print_r($x,true)." </br>");

    $error = $voidResp['VoidShipmentResponse']['Response']['Error'];
    if (!empty($error)) {
      $reason = $error['ErrorDescription'];
      echo("Error: Could not void this shipment: $reason.");
    }

  }
}
?>
