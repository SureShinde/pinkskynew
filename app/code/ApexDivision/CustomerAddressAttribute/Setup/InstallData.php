<?php

/**
 * @author     Apex Division <apexdivision@gmail.com>
 * @copyright  2020  Apex Division (https://apexdivision.com)
 * @license     Commercial
 */

namespace ApexDivision\CustomerAddressAttribute\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig            = $eavConfig;
        $this->_eavSetupFactory     = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute('customer_address', 'tjuk_customer_address_name', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Address Name',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
            'position' => 10,
            'sort_order' => 10
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'tjuk_customer_address_name');

        $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        );
        $customAttribute->save();

        $setup->endSetup();
    }
}
