<?php

namespace Hakfist\CreateCustomer\Setup;

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
         * Create table 'email_otp_validation'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('email_otp_validation'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Serial Number'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default'  => null,],
                'Customer Email'
            )
            ->addColumn(
                'otp',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true,   'default'  => null, 'unsigned' => true],
                'OTP'
            )
            ->addColumn(
                'validate',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default'  => null,],
                'Validate'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Email Otp Validation');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
