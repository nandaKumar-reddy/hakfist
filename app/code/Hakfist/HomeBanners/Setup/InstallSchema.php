<?php

namespace Hakfist\HomeBanners\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
        
        
        
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
                
                
        $installer = $setup;
        $installer->startSetup();
                
                
        /**
        * Create table 'Hakfist_homebanners'
        */
                
        $table = $installer->getConnection()->newTable($installer->getTable('Hakfist_homebanners'))
                                ->addColumn(
                                    'homebanners_id',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                    null,
                                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                    'HomeBanners ID'
                                )
                                ->addColumn(
                                    'title',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                    255,
                                    ['nullable' => true, 'default' => null],
                                    'Title'
                                )
                                ->addColumn(
                                    'content',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                    '5k',
                                    ['nullable' => false, 'default' => null],
                                    'Content'
                                )
                                ->addColumn(
                                    'homebannersimage',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                    255,
                                    [],
                                    'HomeBanners Image'
                                )
                                ->addColumn(
                                    'homebannerssort',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                    null,
                                    ['nullable' => false, 'unsigned' => true, 'default' => '0'],
                                    'HomeBanners Sort Order'
                                )
                                ->addColumn(
                                    'homebannerssceneimg',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                    255,
                                    [],
                                    'HomeBanners Scene7 Image'
                                )
                                // ->addColumn(
                                //     'homebannershoverimage',
                                //     \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                //     255,
                                //     [],
                                //     'HomeBanners Hover Image'
                                // )
                                ->addColumn(
                                    'homebannerslink',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                    255,
                                    [],
                                    'HomeBanners Link'
                                )
                                ->addColumn(
                                    'status',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                    1,
                                    ['nullable' => false, 'default' => '1'],
                                    'HomeBanners Status'
                                )
                                ->addColumn(
                                    'created_time',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                    null,
                                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                    'Creation Time'
                                )
                                ->addColumn(
                                    'update_time',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                    null,
                                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                                    'Update Time'
                                );
                
                
        $installer->getConnection()->createTable($table);
                
        $installer->endSetup();
    }
}
