<?php 

require_once('Mage/Checkout/controllers/OnepageController.php');

class Soularpanic_RocketShipIt_OnepageController
extends Mage_Checkout_OnepageController {
  public function indexAction() {
    parent::indexAction();
  }
  public function progressAction() {
    parent::progressAction();
  }
  public function reviewAction() {
    parent::reviewAction();
  }
  public function saveMethodAction() {
    parent::saveMethodAction();
  }
  public function shippingMethodAction() {
    parent::shippingMethodAction();
  }
  public function saveShippingMethodAction() {
    Mage::log('Entering saveShippingMethodAction; post is: '.print_r($this->getRequest()->getPost(), true),
	      null, 'rocketshipit_shipments.log');
    // if ($this->_expireAjax()) {
    //   return;
    // }
    // if ($this->getRequest()->isPost()) {
    //   $request = $this->getRequest();
    //   $quote = $this->getOnepage()->getQuote();
 

    //   $addOnCode = $request->getPost('shipping_addons', '');
    //   $shippingAddr = $quote->getShippingAddress();
    //   $price = 0.0;
    //   if ($addOnCode === 'sign') { $price = 5.0; }
    //   elseif ($addOnCode === 'signAndInsure') { $price = 7.5; }
    //   $shippingAddr->setHandlingAmount($price);
    //   $shippingAddr->setHandlingCode($addOnCode);

    parent::saveShippingMethodAction();
    //}
  }
}
?>
