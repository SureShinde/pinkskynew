<?php


namespace Webkul\Grid\Controller\Index;
 
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Save extends \Magento\Framework\App\Action\Action
{
     protected $_pageFactory;
     protected $_postFactory;
     protected $_filesystem;
 
     public function __construct(
          \Magento\Framework\App\Action\Context $context,
          \Magento\Framework\View\Result\PageFactory $pageFactory,
          \Webkul\Grid\Model\PostFactory $postFactory,
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


               // echo "<pre>";
               // print_r($input);
               // echo "<br/>";
               // print_r($_FILES['image']['name']);exit;

               $files = $_FILES['image'];
               $imageName = $files['name'];
               $input['image'] = $imageName;

               $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)

                  ->getAbsolutePath('images/');

               $fileName = $_FILES["image"]["name"];

               $baseFileName = basename($_FILES["image"]["name"]);
               move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir.$baseFileName);

          if($input['editRecordId']){
                    $post->load($input['editRecordId']);
                    $post->addData($input);
                    $post->setId($input['editRecordId']);
                    $post->save();
                    $this->messageManager->addSuccess(__('Category data has been successfully updated.'));
          }else{
               $post->setData($input)->save();
               $this->messageManager->addSuccess(__('Category data has been successfully added.'));
          }
 
              return $this->_redirect('grid/index/index');
          }
     }
}