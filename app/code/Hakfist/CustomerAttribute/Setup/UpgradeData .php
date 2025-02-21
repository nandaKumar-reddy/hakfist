<?php

namespace Hakfist\CustomerAttribute\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'odoo_status',
                [
                    'type' => 'varchar',
                    'label' => 'Odoo Status',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => true,
                    'sort_order' => 1000,
                    'position' => 1000,
                    'system' => 0,
                ]
            );

            $attribute = $eavSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'odoo_status');
            $attribute->setData(
                'used_in_forms',
                ['adminhtml_customer']
            );
            $attribute->save();
        }

        $setup->endSetup();
    }
}
