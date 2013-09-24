<?php 

class Soularpanic_RocketShipIt_Block_Sales_Order_Shipment_View
extends Mage_Adminhtml_Block_Sales_Order_Shipment_View {
  public function __construct() {
    parent::__construct();
    $url = $this->getUrl('*/cancelshipment/cancelShipment',
			array('shipmentId' => $this->getShipment()->getId()));
    $this->_addButton('cancel', array(
      'label' => 'Cancel Shipment',
      'class' => 'save',
      'onclick' => "setLocation('$url')"
    ));
  }
}
?>
