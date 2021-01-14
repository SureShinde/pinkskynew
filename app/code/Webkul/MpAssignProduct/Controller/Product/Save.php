<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\Product as SellerProduct;

class Save extends \Magento\Framework\App\Action\Action
{
    const DEFAULT_STORE_ID = 0;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StockConfigurationInterface $stockConfiguration,
        MpProductCollection $mpProductCollectionFactory,
        StockRegistryInterface $stockRegistry,
        ProductRepositoryInterface $productRepository,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $productGallery
    ) {
        $this->_url = $url;
        $this->_date = $date;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->productCopier = $productCopier;
        $this->mpHelper = $mpHelper;
        $this->_mpProductFactory = $mpProductFactory;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->productFactory   = $productFactory;
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_assignHelper;
        $associateProducts = [];
        $currentStoreId = $helper->getStoreId();
        $data = $this->getRequest()->getParams();
        $data['image'] = '';
        if (!array_key_exists('product_id', $data)) {
            $this->messageManager->addError(__('Something went wrong.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        if ($result['error']) {
            $this->messageManager->addError(__($result['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {
            $newProduct = $this->productCopier->copy($product);
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
        $duplicateProduct->setSpecialPrice(null);
        $duplicateProduct->save();
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            $helper->processProductStatus($result);
            // try {
                $status1 = $this->mpHelper->getIsProductApproval() ?
                SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
                $sellerId = $this->mpHelper->getCustomerId();

                /* Update marketplace product for duplicate product*/
                $this->saveMaketplaceProductTable(
                    $newProductId,
                    $sellerId,
                    $status1,
                    0,
                    $associateProducts
                );
            // } catch (\Exception $e) {
            //     die($e->getMessage());
            // }
            $this->messageManager->addSuccess(__('Product is saved successfully.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/productlist');
        } else {
            $this->messageManager->addError(__('There was some error while processing your request.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', ['id' => $data['product_id']]);
        }
    }

    public function saveAssignedProduct($data)
    {
        $associateProducts = [];
        $helper = $this->_assignHelper;
        $currentStoreId = $helper->getStoreId();
        $data['image'] = '';
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        if ($result['error']) {
            $result['error'] = $result['msg'];
        }
        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {
            $newProduct = $this->productCopier->copy($product);
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
        $duplicateProduct->setSpecialPrice(null);
        $duplicateProduct->save();
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            $helper->processProductStatus($result);
            $status1 = $this->mpHelper->getIsProductApproval() ?
            SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
            $sellerId = $this->mpHelper->getCustomerId();

            /* Update marketplace product for duplicate product*/
            $this->saveMaketplaceProductTable(
                $newProductId,
                $sellerId,
                $status1,
                0,
                $associateProducts
            );
        } else {
            $result['error'] = 1;
        }
        return $result;
    }

    /**
     * Update Stock Data of Product
     *
     * @param integer $productId
     * @param integer $qty
     * @param integer $isInStock
     */
    public function updateStockData($sku, $qty = 0, $isInStock = 0)
    {
        try {
            $socpeConfiguration = $this->stockConfiguration;
            $scopeId = $socpeConfiguration->getDefaultScopeId();
            $stockRegistry = $this->stockRegistry;
            $stockItem = $stockRegistry->getStockItemBySku($sku, $scopeId);
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->setData('qty', $qty);
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_notify_stock_qty', 1);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage));
        }
    }
    /**
     * addAssociatedProducts for the configurable Products
     * @param int $productId [Product id of configurable Product]
     * @param mixed $data
     */
    public function addAssociatedProducts($productId, $data)
    {
        $helper = $this->_assignHelper;
        $storeId = $helper->getStoreId();
        $updatedProducts = [];
        if (isset($data['products'])) {
            foreach ($data['products'] as $existingProductId => $associatedProductData) {
                if (isset($associatedProductData['assign_product_id']) && $associatedProductData['assign_product_id']) {
                    $associatedProductData['new_product_id'] = $associatedProductData['assign_product_id'];
                    $product = $helper->getProduct($associatedProductData['assign_product_id']);
                    $associatedProductData['sku'] = $product->getSku();
                    if ($product->getPrice() != $associatedProductData['price']) {
                        $attributData = [
                        'price' => $associatedProductData['price']
                        ];
                        $helper->updateProductData([$product->getId()], $attributData, $storeId);
                    }
                    $updatedProducts[$existingProductId] = $associatedProductData;
                } else {
                    if ($associatedProductData['qty'] && $associatedProductData['price']) {
                        $product = $helper->getProduct($existingProductId);
                        $newProduct = $this->productCopier->copy($product);
                        $attributData = [
                        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                        'price' => $associatedProductData['price']
                        ];
                        $helper->updateProductData([$newProduct->getId()], $attributData, $storeId);
                        $associatedProductData['new_product_id'] = $newProduct->getId();
                        $associatedProductData['sku'] = $newProduct->getSku();
                        $updatedProducts[$existingProductId] = $associatedProductData;
                    }
                }
            }
        }
        return $updatedProducts;
    }
    /**
     * removeImages function is used to remove images of the assigned Product.
     */
    protected function removeImages($productId, $storeId = 0)
    {
        try {
            $product = $this->productRepository->getById(
                $productId,
                true
            );
            $images = $product->getMediaGalleryImages();
            foreach ($images as $child) {
                $this->imageProcessor->removeImage($product, $child->getFile());
                $this->productGallery->deleteGallery($child->getValueId());
            }
            $product->save();
          
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * adminStoreMediaImages function is used to store the images of the assigned Product.
     */
    protected function adminStoreMediaImages($productId, $wholedata, $storeId = 0)
    {
        if (!empty($wholedata['product']['media_gallery'])) {
            $catalogProduct = $this->productFactory->create()->load(
                $productId
            );
            $catalogProduct->addData($wholedata['product'])->save();
        }
    }

    /**
     * Set Product Records in marketplace_product table.
     *
     * @param int $mageProductId
     * @param int $sellerId
     * @param int $status
     * @param int $editFlag
     * @param array $associatedProductIds
     */
    private function saveMaketplaceProductTable(
        $mageProductId,
        $sellerId,
        $status,
        $editFlag,
        $associatedProductIds
    ) {
        $savedIsApproved = 0;
        $sellerProductId = 0;
        $helper = $this->mpHelper;
        if ($mageProductId) {
            $sellerProductColls = $this->_mpProductCollectionFactory->create()
            ->addFieldToFilter(
                'mageproduct_id',
                $mageProductId
            )->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($sellerProductColls as $sellerProductColl) {
                $sellerProductId = $sellerProductColl->getId();
                $savedIsApproved = $sellerProductColl->getIsApproved();
            }
            $collection1 = $this->_mpProductFactory->create()->load($sellerProductId);
            $collection1->setMageproductId($mageProductId);
            $collection1->setSellerId($sellerId);
            $collection1->setStatus($status);
            $isApproved = 1;
            if ($helper->getIsProductEditApproval()) {
                $collection1->setAdminPendingNotification(2);
            }
            if (!$editFlag) {
                $collection1->setCreatedAt($this->_date->gmtDate());
                if ($helper->getIsProductApproval()) {
                    $isApproved = 0;
                    $collection1->setAdminPendingNotification(1);
                }
            } elseif (!$helper->getIsProductEditApproval()) {
                $isApproved = $savedIsApproved;
            } else {
                $isApproved = 0;
            }
            $collection1->setIsApproved($isApproved);
            $collection1->setUpdatedAt($this->_date->gmtDate());
            $collection1->save();
        }

        foreach ($associatedProductIds as $associatedProductId) {
            if ($associatedProductId) {
                $sellerAssociatedProductId = 0;
                $sellerProductColls = $this->_mpProductCollectionFactory->create()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $associatedProductId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                foreach ($sellerProductColls as $sellerProductColl) {
                    $sellerAssociatedProductId = $sellerProductColl->getId();
                }
                $collection1 = $this->_mpProductFactory->create()->load($sellerAssociatedProductId);
                $collection1->setMageproductId($associatedProductId);
                if (!$editFlag) {
                    /* If new product is added*/
                    $collection1->setStatus(SellerProduct::STATUS_ENABLED);
                    $collection1->setCreatedAt($this->_date->gmtDate());
                }
                if ($editFlag) {
                    $collection1->setAdminPendingNotification(2);
                }
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->setSellerId($sellerId);
                $collection1->setIsApproved(1);
                $collection1->save();
            }
        }
    }
}
