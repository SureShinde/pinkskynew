<?php


namespace ApexDivision\CustomerAttribute\Model\Customer\Attribute\Source;

class CustomerBusinessRoles extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => 'None', 'label' => __('Select an option')],
                ['value' => 'Purchase Head', 'label' => __('Purchase Head')],
                ['value' => 'Purchase Manager', 'label' => __('Purchase Manager')],
                ['value' => 'Purchase Executive', 'label' => __('Purchase Executive')],
                ['value' => 'F&B Head', 'label' => __('F&B Head')],
                ['value' => 'F&B Manager', 'label' => __('F&B Manager')],
                ['value' => 'Other', 'label' => __('Other')]
            ];
        }
        return $this->_options;
    }
}