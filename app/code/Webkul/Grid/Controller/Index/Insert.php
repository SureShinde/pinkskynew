<?php
namespace  Webkul\Grid\Controller\Index;

class Insert extends \Magento\Framework\App\Action\Action
{
     protected $_pageFactory;
     public function __construct(
          \Magento\Framework\App\Action\Context $context,
          \Magento\Framework\View\Result\PageFactory $pageFactory
          )
     {
          $this->_pageFactory = $pageFactory;
          return parent::__construct($context);
     }
 
     public function execute()
     {
          $data = $this->getRequest()->getPostValue();
          // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          // $model = $objectManager->create('Webkul\Grid\Model\Grid');
          // $model->setData($data);
          // $model->save();
          // $this->_redirect('grid/index/index');

          // echo "<pre>";
          // print_r($data);
          //$model = $this->gridFactory->create();
          //$model->title =$this->getRequest()->getPost('title');
          // echo $this->getRequest()->getPost();
          // $model = $this->gridFactory->create();
          // $model->title =$this->getRequest()->getPost('name');
          // $saveData = $model->save();
          return $this->_pageFactory->create();
     }
}