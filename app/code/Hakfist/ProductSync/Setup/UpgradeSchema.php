<?php

namespace Hakfist\ProductSync\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		// if (version_compare($context->getVersion(), '1.4.0') < 0) {
			$setup->startSetup();
			$setup->getConnection()->addColumn(
				$setup->getTable('catalog_product_option'),
				'image',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => 255,
					'nullable' => true,
					'comment' => 'image',
				]
			);
			$setup->endSetup();
		// }
	}
}