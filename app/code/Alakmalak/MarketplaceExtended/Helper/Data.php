<?php
namespace Alakmalak\MarketplaceExtended\Helper;

/**
 * Webkul Marketplace Helper Data.
 */
class Data extends \Webkul\Marketplace\Helper\Data
{
    public function getAllowedProductTypes()
    {
        $alloweds = explode(',', $this->getAllowedProductType());
        $data = [
            'simple' => __('Simple'),
            'downloadable' => __('Downloadable'),
            'virtual' => __('Service'),
            'configurable' => __('Configurable'),
            'grouped' => __('Grouped Product'),
            'bundle' => __('Bundle Product'),
        ];
        $allowedproducts = [];
        if (isset($alloweds)) {
            foreach ($alloweds as $allowed) {
                if (!empty($data[$allowed])) {
                    array_push(
                        $allowedproducts,
                        ['value' => $allowed, 'label' => $data[$allowed]]
                    );
                }
            }
        }

        return $allowedproducts;
    }
}
