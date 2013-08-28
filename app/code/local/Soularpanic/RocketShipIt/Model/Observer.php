<?php
class Soularpanic_RocketShipIt_Model_Observer
{
  protected $_code = 'rocketshipit';

  function __construct() {
    /* $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'/path'); */
    /* require_once($rocketShipItPath.'/RocketShipIt.php'); */
  
  }

  /* public function addMassButtons($observer) { */
  /*   if ( */
  /* 	$observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction */
  /* 	|| $observer->getEvent()->getBlock() instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction */
  /* 	|| $observer->getEvent()->getBlock() instanceof Enterprise_SalesArchive_Block_Adminhtml_sales_orderManager_Grid_Massaction */
  /*       ) { */
  /*     $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false'; */
  /*     if($observer->getEvent()->getBlock()->getRequest()->getControllerName() =='sales_order' || */
  /* 	 $observer->getEvent()->getBlock()->getRequest()->getControllerName() =='adminhtml_sales_order') { */
  /* 	/\* $observer->getEvent()->getBlock()->addItem('ordermanager_invoiceall', array( *\/ */
  /* 	/\* 									    'label'=> Mage::helper('ordermanager')->__('Invoice Selected'), *\/ */
  /* 	/\* 									    'url'  => Mage::helper('adminhtml')->getUrl('adminhtml/sales_orderManager/invoiceall',$secure ? array('_secure'=>1) : array()), *\/ */
  /* 	/\* 									    )); *\/ */
  /* 	$observer->getEvent()->getBlock()->addItem('soularpanic_mass', array( */
  /* 									     'label' => 'Josh - Hi!', */
  /* 									     'url'   => Mage::helper('adminhtml')->getUrl('adminhtml/sales_rocketShipIt/mass',$secure ? array('_secure'=>1) : array()), */
  /* 									     )); */
  /* 	$observer->getEvent()->getBlock()->addItem('soularpanic_shipall', array( */
  /* 									     'label' => 'Josh - Ship All', */
  /* 									     'url'   => Mage::helper('adminhtml')->getUrl('adminhtml/sales_rocketShipIt/shipall',$secure ? array('_secure'=>1) : array()), */
  /* 									     )); */

  /*     } */
  /*   } */
  /* } */


  public function trackAndLabel(Varien_Event_Observer $observer)
  {
    Mage::log('rocketshipit observer firing',
	      null,
	      'rocketshipit_shipments.log');

    $helper = Mage::helper('rocketshipit');
    
    $shipment = $observer->getEvent()->getShipment();

    $destAddr = $shipment->getShippingAddress();
    $rsiShipment = $helper->asRSIShipment('UPS', $destAddr);


    $rsiPackage = new RocketShipPackage('UPS');
    $rsiPackage->setParameter('length','6');
    $rsiPackage->setParameter('width','6');
    $rsiPackage->setParameter('height','6');
    
    $weight = $shipment->getOrder()->getWeight();
    $rsiPackage->setParameter('weight', $weight);
    
    $rsiShipment->addPackageToShipment($rsiPackage);
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException($this->__('Label generation failed: '.$label));
    }

    $rsiTrackNo = $label['trk_main'];
    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle($shipment->getOrder()->getShippingDescription());
    $track->setNumber($rsiTrackNo);
    $track->setCarrierCode($shipment->getOrder()->getShippingMethod());
    $shipment->addTrack($track);
    
    $labelImg = $label['pkgs'][0]['label_img'];
    $labelImgDecoded = base64_decode($labelImg);
    $shipment->setShippingLabel($labelImgDecoded);
  }
}
?>