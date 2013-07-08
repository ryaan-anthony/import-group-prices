<?php

class Ip_Import_CustomController extends Mage_Adminhtml_Controller_Action
{

    protected $_errors = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder"
    );

    public function indexAction()
    {
        $this->loadLayout();
         if($_FILES){
              if ($_FILES["file"]["error"] > 0){
                  Mage::getSingleton('core/session')->addError($this->_errors[$_FILES["file"]["error"]]);
              } else {
                  $method = "import_".$this->getRequest()->getParam('hidden_data');
                  if(method_exists($this, $method)){
                      call_user_func_array(array(&$this, $method), array());
                      Mage::getSingleton('core/session')->addSuccess("Import Complete!");
                  } else {
                      Mage::getSingleton('core/session')->addError('Import method not found: "'.$method.'"');
                  }
              }
              $this->_initLayoutMessages('core/session');
          }
        $this->renderLayout();
    }

    protected function import_group_price()
    {
        $file = fopen($_FILES['file']['tmp_name'], "r");
        $columns = array();
        while ($data = fgetcsv($file, 2000, ",")) {
            if(!$columns){
                $columns = $data;
            } else {
                $sku = $data[0];
                $group_id = $data[1];
                $group_price = $data[2];
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                $product->setData('group_price',array (
                    array (
                        "website_id" => Mage::app()->getStore()->getWebsiteId(),
                        "cust_group" => $group_id,
                        "price" => $group_price
                    )));
                $product->save();
            }
        }
    }


}