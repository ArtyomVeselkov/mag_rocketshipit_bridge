<?php 
class Soularpanic_RocketShipIt_TestController
extends Mage_Core_Controller_Front_Action {
  public function testModelAction() {
    $params = $this->getRequest()->getParams();
    $order = Mage::getModel('rocketshipit/orderExtras');
    echo("Loading order with ID of ".$params['id']);
    $order->load($params['id']);
    $data = $order->getData();
    echo('hi!');
    echo('is it null? '.var_export(is_null($data), true));
    var_dump($data);
    
  }

  public function testCreateAction() {
    $params = $this->getRequest()->getParams();
    $id = $params['id'];
    //isObjectNew
    $order = Mage::getModel('rocketshipit/orderExtras')->load($id);
    $order->setCustomsDesc('test created!');
    $order->setCustomsQty(10.0);
    $order->setOrderId($id);
    $order->save();
    echo($order->getId());
  }

  public function testImagesAction() {
    $labelUrls = 'https://swsim.stamps.com/Label/label.ashx/label-200.gif?AQAAAFDPKeDagdAILVwQL3qVCwakA-mvqBqmsJbyyYN4nNNItWIQCHdi9QqP7nbo_vJ4yRtBu3KB5nu2D3dGunrPCrRvZsy_0cxnYGYUbGhubGxkYWhkbsLqmJMTZqQM1CoekpGqEJRaUpSfllmiEJxfWpScquCZl_z__39RI0NjUwWX1LT8_CIFj8ycnGKFoBQ99uDSzJJUBY___9kdS3IS80oSmdwdWY0NjA0t_nMHh_gHucYHePj7uTIDDWf4_1_EM68ktSgvsSQzPy8xRyE4M7cgJ5XPyEzB3d_HxdPPXcExzBVoF6-Tq4-vv1-Igp9_UIgHs19wOIuRkYUBUIbTsbS4pCgxJzORy9DQ0MjIyBgI_gswCDBALBVT5mdAAkwMDPGsK3NCzptmv_mS-Ouof_rcHpnFSQl9LXNT9lg8WnVs1-uwb7xuzAz7a19o31bUb_zT_JDD6ZCEI69nmcRB7sKPS5PuyrvITXGJX78w5KHBx8QvUvfuamTtZ-L0ve2x5tSdu3tPM7IWlxdn5nI7exlAgSlDg9mltoYLHFC6g4EQYGPhciwtyVfwScwtKL7jiOwFFjSlLMBQBNH_kXQwGrJaWupZWjIKgP0cGszIwMAIVo0RGiiAkYEZzpYF8RkZ-EODA4IVQoIcnb1B0aEMMQgUe__RAQM7A5uZgq6CoQELAHV-tqc= https://swsim.stamps.com/Label/label.ashx/label-200.gif?AQAAAJBrKuDagdAIug5qw1Cg9P_064sawh2XY8E3WTl4nNNItWIQCHdi9QqP7nbo_vJ4yRtBu3KB5nu2D3dGunrPCrRvZsy_0cxnYGYUbGhubGxkYWhkbsLqmJMTZqQM1CoekpGqEJRaUpSfllmiEJxfWpScquCZl_z__39RI0NjUwWX1LT8_CIFj8ycnGKFoBQ99uDSzJJUBY___9kdS3IS80oSmdwdWY0NjA0t_nMHh_gHucYHePj7uTIDDWf4_1_EM68ktSgvsSQzPy8xRyE4M7cgJ5XPyEzB3d_HxdPPXcExzBVoF6-Tq4-vv1-Igp9_UIgHs19wOIuRkYUBUIbTsbS4pCgxJzORy9DQ0MjIyBgI_gswCDBALBVT5mdAAkwMDPGsK3NCzptmv_mS-Ouof_rcHpnFSQl9LXNT9lg8WnVs1-uwb7xuzAz7a19o31bUb_zT_JDD6ZCEI69nmcRB7sKPS5PuyrvITXGJX78w5KHBx8QvUvfuamTtZ-L0ve2x5tSdu3tPM7IWlxdn5nI7exlAgSlDg9mltoYLHFC6g4EQYGPhciwtyVfwScwtKL7jiOwFFjSlLMBQBNH_kXQwGrJaWupZWjIKgP0cGswI9jkQYIQGCmBkYIazZUF8Rgb-0OCAYIWQIEdnb1B0KIMUMTCAYu8_OmBgZ2AzU9BVMDRgAQB167ao https://swsim.stamps.com/Label/label.ashx/label-200.gif?AQAAAMDgKuDagdAIxYMPKk-6TWV77vqpFDfY38rUxLF4nNNItWIQCHdi9QqP7nbo_vJ4yRtBu3KB5nu2D3dGunrPCrRvZsy_0cxnYGYUbGhubGxkYWhkbiLomJMTZuSZV1xSVJpckpmfV6wMNEY8JCNVISi1pCg_LbNEITi_tCg5VcEzL_n___-iRobGpgouqWn5-UUKHpk5OcUKQSl67MGlmSWpCh7__7M7luQk5pUkMrk7shobGBta_OcODvEPco0P8PD3c2UGGs7w_7-IZ15JalFeIsjCxByF4MzcgpxUPiMzBXd_HxdPP3cFxzBXoF28Tq4-vv5-IQp-_kEhHsx-weEsRkYWBkAZTsdSoIsTczITuQwNDY2MjIyB4L8AgwADxFIxZX4GJMDEwBDPujIn5Lxp9psvib-O-qfP7ZFZnJTQ1zI3ZY_Fo1XHdr0O-8brxsywv_aF9m1F_cY_zQ85nA5JOPJ6lkkc5C78uDTprryL3BSX-PULQx4afEz8InXvrkbWfiZO39sea07dubv3NCNrcXlxZi63s5cBFJgyNJhdamu4wAGlOxgIATYWLsfSknwFn8TcguI7jsheYEFTygIMRRD9H0kHoyGrpaWepSWjANjPocGMDAzMYNUYoYECGKGqQEAWxGdk4A8NDghWCAlydPYGRYcySBHQMGDs_UcHDOwMbGYKugqGBiwA-_S7yg==';
    $labelImgs = array();
    $labelPaths = array();
    foreach (explode(' ', $labelUrls) as $labelUrl) {
      $curlObj = curl_init();
      // $filename = Mage::getBaseDir('tmp').'/'.rand().'.gif';
      // $out = fopen($filename,"w");
      // if($out == false) {
      // 	echo('fail');
      // 	exit;
      // }
      curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      //curl_setopt($curlObj, CURLOPT_FILE, $out);
      curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
      $labelStr = curl_exec($curlObj);
      $labelImgs[] = $labelStr;
      //$labelPaths[] = $filename;
      curl_close($curlObj);
    }
    $labelResources = array();
    foreach ($labelImgs as $labelImg) {
      $resource = imagecreatefromstring($labelImg);
      $labelResources[] = $resource;
      // $filename = Mage::getBaseDir('tmp').'/'.rand().'.gif';
      // imagegif($resource, $filename);
    }
    //foreach ($labelPaths as $labelPath) {
      //$labelResources[] = imagecreatefromgif($labelPath);
    //}
    $x = imagesx($labelResources[0]);
    $y = 0;

    foreach ($labelResources as $labelResource) {
      $y += imagesy($labelResource);
    }

    $pageOne = $labelResources[0];
    $pageTwo = $labelResource[1];
    $twoPage = imagecreatetruecolor($x, 2 * imagesy($pageOne));
    imagecopyresampled($twoPage, $pageOne, 0, 0, imagesx($pageOne), imagesy($pageOne), imagesx($pageOne), imagesy($pageOne));
    $filename = Mage::getBaseDir('tmp').'/'.rand().'.gif';
    imagegif($twoPage, $filename);

    $bigImg = imagecreatetruecolor($x, $y);
    $yOffset = 0;
    foreach ($labelResources as $labelResource) {
      $ySize = imagesy($labelResource);
      imagecopyresampled($bigImg, $labelResource, 0, $yOffset, 0, 0, $x, $ySize, $x, $ySize);
      $yOffset += $ySize;
    }

    ob_start();
    imagegif($bigImg);
    $stringdata = ob_get_contents(); // read from buffer
    ob_end_clean(); // delete buffer

    $filename = Mage::getBaseDir('tmp').'/'.rand().'.gif';
    // $out = fopen($filename,"w");
    // if($out == false) {
    //   echo('fail');
    //   exit;
    // }
    // fclose($out);
    imagegif($bigImg, $filename);

    echo('hi!');
    echo("<img src='$filename'/>");
  }
}
 ?>
