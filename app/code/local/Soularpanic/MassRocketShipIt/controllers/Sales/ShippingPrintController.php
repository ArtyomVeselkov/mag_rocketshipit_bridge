<?php 
class Soularpanic_MassRocketShipIt_Sales_ShippingPrintController 
extends Mage_Adminhtml_Controller_Action {
  public function batchlabelsAction() {
    $orderIds = $this->getRequest()->getPost('order_ids');
    $flag = false;
    if (!empty($orderIds)) {
      foreach ($orderIds as $orderId) {
        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
				   ->setOrderFilter($orderId)
				   ->load();
        if ($shipments->getSize()) {
          $flag = true;
          if (!isset($pdf)){
            $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
          } else {
            $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
            $pdf->pages = array_merge ($pdf->pages, $pages->pages);
          }
        }
      }


      $dateStr = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
      $archiveFileName = 'shipping_docs_'.$dateStr.'.zip';
      $archivePath = Mage::getBaseDir('tmp').'/'.$archiveFileName;

      $archive = new ZipArchive;
      if ($archive->open($archivePath, ZIPARCHIVE::CREATE) !== TRUE) {
	die('could not open archive');
      }
      $archive->addFromString("packingslip{$dateStr}.pdf", $pdf->render());
      $archive->close();

      if ($flag) {
        /* return $this->_prepareDownloadResponse(
        'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
        'application/pdf'
        ); */
	//$downloadContent = new ZipArchive;
	//$downloadContent->open($archivePath);
	/* $this->getResponse()
	->setHeader('Content-Transfer-Encoding', 'binary', true)
	->setHeader('Content-Description', 'File Transfer' ,true)
	->setHeader('Expires', '0', true)
	->setHeader('Cache-Control', 'public', true); */
	$this->_prepareDownloadResponse($archiveFileName,
					array(
	    'type' => 'filename',
	    'value' => $archivePath
	  ));
						 //'application/x-zip-compressed');
	/* $archiveContent = file_get_contents($archivePath);
	return $this->_prepareDownloadResponse($archiveFileName,
	$archiveContent); */
      } else {
        $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
        $this->_redirect('*/*/');
      }
    }
    $this->_redirect('*/*/');
  }
}
