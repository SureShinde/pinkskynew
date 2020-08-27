<?php
/**
 * @author     Apex Division <apexdivision@gmail.com>
 * @copyright  2020  Apex Division (https://apexdivision.com)
 * @license     Commercial
 */

namespace ApexDivision\ProductDeliveryEstimate\Block\Catalog\Product\View;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class DeliveryEstimate extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_stockResolver;
    protected $_storeManager;
    protected $_getProductSalableQty;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_stockResolver = $stockResolver;
        $this->_storeManager = $storeManager;
        $this->_getProductSalableQty = $getProductSalableQty;
        parent::__construct($context, $data);
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getSalableQty()
    {
        $current_product = $this->getCurrentProduct();
        $sku = $current_product->getSku();
        $stockId = $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $this->_storeManager->getWebsite()->getCode())->getStockId();
        return (int)$this->_getProductSalableQty->execute($sku, $stockId);
    }
}
