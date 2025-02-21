<?php
namespace Hakfist\ProductMasterApi\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('productmasterapi_log'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'jsonresponse',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Jsonresponse'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'message'
            )
            ->addColumn(
                'typeofapi',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Typeofapi'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false,'default' => ''],
                'Status'
            )
            ->addColumn(
                'requesteid',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false,'default' => ''],
                'Requesteid'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('productmaster_api Log table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}