<?php

/**
 * Grid Grid Collection.
 *
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Grid\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'wk_grid_records_collection';
    // protected $_eventPrefix = 'webkul_category_collection';
    protected $_eventObject = 'category_collection';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\Grid\Model\Grid',
            'Webkul\Grid\Model\ResourceModel\Grid'
        );
    }
}
