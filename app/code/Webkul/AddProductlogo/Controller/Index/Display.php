<?php
/**
 * Grid Record Index Controller.
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AddProductlogo\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

class Display extends \Magento\Framework\App\Action\Action
{
     protected $_pageFactory;
 
     public function __construct(
          \Magento\Framework\App\Action\Context $context,
          \Magento\Framework\View\Result\PageFactory $pageFactory,
          \Webkul\AddProductlogo\Model\PostFactory $postFactory
          )
     {
          $this->_pageFactory = $pageFactory;
          $this->_postFactory = $postFactory;
          return parent::__construct($context);
     }
 
     public function execute()
     {
          $post = $this->_postFactory->create();
          $collection = $post->getCollection();
          // foreach($collection as $item){
          //      echo "<pre>";
          //      print_r($item->getData());
          //      echo "</pre>";
          // }
          return $this->_pageFactory->create();

          // $block = $post->getLayout()->getBlock('crud_index_index');
          // $block->setData('custom_parameter', 'Data from the Controller');
          // return $post;
     }
}