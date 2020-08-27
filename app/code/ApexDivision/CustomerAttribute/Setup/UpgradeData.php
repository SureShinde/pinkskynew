<?php

/**
 * @author     Apex Division <apexdivision@gmail.com>
 * @copyright  2019  Apex Division (https://apexdivision.com)
 * @license     Commercial
 */

namespace ApexDivision\CustomerAttribute\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface {

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
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        if (version_compare($context->getVersion(), '1.2.0', '<')) {

            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $setup->startSetup();

            $attributesInfo = [
                'tjuk_customer_code' => [
                    'label' => 'Customer Code',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 999,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'tjuk_customer_vertical_code' => [
                    'label' => 'Vertical Code',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 1003,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'tjuk_customer_vertical_name' => [
                    'label' => 'Vertical Name',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 1004,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'tjuk_customer_website' => [
                    'label' => 'Website',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 1005,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'tjuk_customer_special_margin' => [
                    'label' => 'Special Margin',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 1006,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'tjuk_customer_available_credit' => [
                    'label' => 'Available Credit',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 1007,
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

            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_code');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $magentoUsernameAttribute->save();


            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_vertical_code');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $magentoUsernameAttribute->save();


            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_vertical_name');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $magentoUsernameAttribute->save();


            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_website');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            ]);
            $magentoUsernameAttribute->save();


            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_special_margin');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $magentoUsernameAttribute->save();


            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_available_credit');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $magentoUsernameAttribute->save();



            $setup->endSetup();

        }

        if (version_compare($context->getVersion(), '1.5.0', '<')) {

            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $setup->startSetup();

            $attributesInfo = [
                'tjuk_customer_company_name' => [
                    'label' => 'Company Name',
                    'type' => 'varchar',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'system' => 0,
                    'user_defined' => true,
                    'position' => 800,
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

            $magentoUsernameAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'tjuk_customer_company_name');
            $magentoUsernameAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            ]);
            $magentoUsernameAttribute->save();

            $setup->endSetup();
        }
    }

}
