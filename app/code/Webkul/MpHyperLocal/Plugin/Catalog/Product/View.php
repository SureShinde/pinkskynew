<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Plugin\Catalog\Product;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpHyperLocal\Helper\Data;

class View
{
    /**
     * @param \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param Data $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->helper = $helper;
    }
    
    /**
     * @see \Magento\Catalog\Controller\Product\View::execute()
     */
    public function afterExecute(\Magento\Catalog\Controller\Product\View $subject, $result)
    {
        $productId = (int) $subject->getRequest()->getParam('id');

        $savedAddress = $this->helper->getSavedAddress();
        if ($savedAddress) {
            $sellerIds = $this->helper->getNearestSellers();
            $allowedProList = $this->helper->getNearestProducts($sellerIds);
            if (!in_array($productId,$allowedProList)) {
                $this->messageManager->addNotice(__('You are not authorized to view this product.'));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');        
            }
        }
        return $result;
    }
}