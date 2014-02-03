<?php 
class Soularpanic_MassRocketShipIt_Sales_ShippingPrintController 
extends Mage_Adminhtml_Controller_Action {

  const UPS_SLIPS = 'UPS_PACKING_SLIPS';
  const USPS_SLIPS = 'USPS_PACKING_SLIPS';
  const UPS_LABELS = 'UPS_SHIPPING_LABELS';
  const USPS_LABELS = 'USPS_SHIPPING_LABELS';

  public function batchlabelsAction() {
    $orderIds = $this->getRequest()->getPost('order_ids');

    $documents = array();
    if (!empty($orderIds)) {
      foreach ($orderIds as $orderId) {
	$order = Mage::getModel('sales/order')->load($orderId);
	$documents = $this->_sortDocuments($documents, $order);
      }

      if ($documents) {
	$this->_prepareDocumentsForDownload($documents);
      } else {
	$this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
	$this->_redirect('*/*');
      }
    }
    $this->_redirect('*/*');
  }

  function _sortDocuments($documents, $order) {
    $shipments = $order->getShipmentsCollection();
    if (!$shipments->getSize()) {
      return $documents;
    }
    
    $shippingMethod = Mage::helper('rocketshipit')
		   ->parseShippingMethod($order->getShippingMethod());
    $carrier = strtoupper($shippingMethod['carrier']);
    
    if ($carrier === 'STAMPS') {
      $slipKey = self::USPS_SLIPS;
      $labelKey = self::USPS_LABELS;
    }
    elseif ($carrier === 'UPS') {
      $slipKey = self::UPS_SLIPS;
      $labelKey = self::UPS_LABELS;
    }
    else {
      Mage::throwException("Order {$order->getIncrementId()} had an invalid carrier code ({$carrier}) associated with it.");
    }
    
    $slips = $documents[$slipKey];
    if (!isset($slips)){
      $documents[$slipKey] = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
    } else {
      $addlSlips = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
      $slips->pages = array_merge ($slips->pages, $addlSlips->pages);
    }

    $tmpLabels = array();
    foreach ($shipments as $shipment) {
      $labels = $tmpLabels[$labelKey];
      $shippingLabel = Zend_Pdf::parse($shipment->getShippingLabel());
      if (!isset($labels)) {
	$tmpLabels[$labelKey] = $shippingLabel;
      }
      else {
	$labels->pages = array_merge($labels->pages, $shippingLabel->pages);
      }
    }

    foreach ($tmpLabels as $tmpLabelKey => $tmpLabelValue) {
      $documents[$tmpLabelKey] = new Zend_Pdf();
      foreach ($tmpLabelValue->pages as $page) {
	$documents[$tmpLabelKey]->pages[] = new Zend_Pdf_Page(clone $page);
      }
    }

    return $documents;
  }

  function _prepareDocumentsForDownload($documents) {
    $dateStr = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
    $archiveFileName = 'shipping_docs_'.$dateStr.'.zip';
    $archivePath = Mage::getBaseDir('tmp').'/'.$archiveFileName;

    $this->_prepareArchive($archivePath, $documents);

    $this->_prepareDownloadResponse($archiveFileName,
				    array('type' => 'filename',
					  'value' => $archivePath
					  ));
  }

  function _prepareArchive($path, $documents) {
    $archive = new ZipArchive;
    if ($archive->open($path, ZIPARCHIVE::CREATE) !== TRUE) {
      Mage::throwException("Failed to create zip archive on disk.");
    }

    $dateStr = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
    if ($documents[self::USPS_SLIPS]) {
      $archive->addFromString("packslips_USPS_{$dateStr}.pdf",
			      $documents[self::USPS_SLIPS]->render());
    }
    if ($documents[self::UPS_SLIPS]) {
      $archive->addFromString("packslips_UPS_{$dateStr}.pdf",
			      $documents[self::UPS_SLIPS]->render());
    }
    if ($documents[self::USPS_LABELS]) {
      $archive->addFromString("shiplabels_USPS_{$dateStr}.pdf",
			      $documents[self::USPS_LABELS]->render());
    }
    if ($documents[self::UPS_LABELS]) {
      $archive->addFromString("shiplabels_UPS_{$dateStr}.pdf",
			      $documents[self::UPS_LABELS]->render());
    }
    $archive->close(); 
  }

}
