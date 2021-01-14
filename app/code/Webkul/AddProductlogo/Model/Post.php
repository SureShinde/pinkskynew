<?php
namespace Webkul\AddProductlogo\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'webkul_addproductlogo_post';

	protected $_cacheTag = 'webkul_addproductlogo_post';

	protected $_eventPrefix = 'webkul_addproductlogo_post';

	protected function _construct()
	{
		$this->_init('Webkul\AddProductlogo\Model\ResourceModel\Post');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}