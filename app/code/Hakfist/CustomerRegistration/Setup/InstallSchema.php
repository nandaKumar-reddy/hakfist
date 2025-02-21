<?php

namespace Hakfist\CustomerRegistration\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create table 'odoo_customer_error_log'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('odoo_customer_error_log'))
            ->addColumn(
                's_no',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Serial Number'
            )
            ->addColumn(
                'odoo_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Odoo ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Customer ID'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Customer Email'
            )
            ->addColumn(
                'error_status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Error Status'
            )
            ->setComment('Odoo Customer Error Log');
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'odoo_customer_success_log'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('odoo_customer_success_log'))
            ->addColumn(
                's_no',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Serial Number'
            )
            ->addColumn(
                'odoo_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Odoo ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Customer ID'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Customer Email'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Odoo Customer Success Log');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
