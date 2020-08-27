<?php

/**
 * @author     Apex Division <apexdivision@gmail.com>
 * @copyright  2019  Apex Division (https://apexdivision.com)
 * @license     Commercial
 */

namespace ApexDivision\CustomerAttribute\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface {

    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
            CustomerSetupFactory $customerSetupFactory,
            AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $setup->startSetup();
        
        $attributesInfo = [
            'tjuk_gstin' => [
                'label' => 'GSTIN',
                'type' => 'varchar',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'system' => 0,
                'user_defined' => true,
                'position' => 1000,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ],
            'tjuk_fssai' => [
                'label' => 'FSSAI',
                'type' => 'varchar',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'system' => 0,
                'user_defined' => true,
                'position' => 1001,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ],
            'tjuk_business_role' => [
                'label' => 'Business Role',
                'type' => 'varchar',
                'input' => 'select',
                'source' => 'ApexDivision\CustomerAttribute\Model\Customer\Attribute\Source\CustomerBusinessRoles',
                'visible' => true,
                'required' => false,
                'system' => 0,
                'user_defined' => true,
                'position' => 1002,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]
        ];
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeParams);
        }
        
        
        
        
        /*
        //You can use this attribute in the following forms
        adminhtml_checkout
        adminhtml_customer
        adminhtml_customer_address
        customer_account_create
        customer_account_edit
        customer_address_edit
        customer_register_address
        */
        $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_gstin');
        $magentoUsernameAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
        ]);
        $magentoUsernameAttribute->save();
        
        
        
        $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_fssai');
        $magentoUsernameAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
        ]);
        $magentoUsernameAttribute->save();
        
        
        
        $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_business_role');
        $magentoUsernameAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
        ]);
        $magentoUsernameAttribute->save();
        
        
        $setup->endSetup();
    }

}
