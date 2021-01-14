<?php


namespace Webkul\AddProductlogo\Controller\Index;
 
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Update extends \Magento\Framework\App\Action\Action
{
     protected $_pageFactory;
     protected $_postFactory;
     protected $_filesystem;
 
     public function __construct(
          \Magento\Framework\App\Action\Context $context,
          \Magento\Framework\View\Result\PageFactory $pageFactory,
          \Webkul\AddProductlogo\Model\PostFactory $postFactory,
          \Magento\Framework\Filesystem $filesystem
          )
     {
          $this->_pageFactory = $pageFactory;
          $this->_postFactory = $postFactory;
          $this->_filesystem = $filesystem;
          return parent::__construct($context);
     }
 
     public function execute()
     {
        
        if ($this->getRequest()->isPost()) {
            $input = $this->getRequest()->getPostValue();
            $post = $this->_postFactory->create();

            $om = \Magento\Framework\App\ObjectManager::getInstance();  
               $customerSession = $om->get('Magento\Customer\Model\Session');  
               $customerData = $customerSession->getCustomer()->getData(); //get all data of customerData
               $customerData = $customerSession->getCustomer()->getId();//get id of customer
            // echo "<pre>";
            // print_r($input);
            // echo "<br/>";
            // print_r($_FILES['image']['name']);exit;

            $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)

            ->getAbsolutePath('productlogo/');
            $arr['user_id']=$customerData;
            if(!empty($_FILES["logo"]["name"])){
                    $fileName = $_FILES["logo"]["name"];               
                    $baseFileName = basename($_FILES["logo"]["name"]);
                    move_uploaded_file($_FILES["logo"]["tmp_name"], $targetDir.$baseFileName);
                    $arr['featured_image'] = $fileName;
               }
               

               if(!empty($_FILES["logo_one"]["name"])){
                    $fileName_one = $_FILES["logo_one"]["name"];                    
                    $baseFileName_one = basename($_FILES["logo_one"]["name"]);
                    move_uploaded_file($_FILES["logo_one"]["tmp_name"], $targetDir.$baseFileName_one);
                    $arr['featured_image_one'] = $fileName_one;
               }

               if(!empty($_FILES["logo_two"]["name"])){
                    $fileName_two = $_FILES["logo_two"]["name"];                    
                    $baseFileName_two = basename($_FILES["logo_two"]["name"]);
                    move_uploaded_file($_FILES["logo_two"]["tmp_name"], $targetDir.$baseFileName_two);
                    $arr['featured_image_two'] = $fileName_two;
               }
               if(!empty($_FILES["logo_three"]["name"])){
                    $fileName_three = $_FILES["logo_three"]["name"];                    
                    $baseFileName_three = basename($_FILES["logo_three"]["name"]);
                    move_uploaded_file($_FILES["logo_three"]["tmp_name"], $targetDir.$baseFileName_three);
                    $arr['featured_image_three'] = $fileName_three;
               }
               if(!empty($_FILES["logo_four"]["name"])){
                    $fileName_four = $_FILES["logo_four"]["name"];                    
                    $baseFileName_four = basename($_FILES["logo_four"]["name"]);
                    move_uploaded_file($_FILES["logo_four"]["tmp_name"], $targetDir.$baseFileName_four);
                    $arr['featured_image_four'] = $fileName_four;
               }

            if($input['editRecordId']){
                        $post->load($input['editRecordId']);
                        $post->addData($arr);
                        $post->setId($input['editRecordId']);
                        $post->save();
                        $this->messageManager->addSuccess(__('Product Logo has been successfully updated.'));

                        return $this->_redirect('addproductlogo/index/display');
            }
        }          
     }
}