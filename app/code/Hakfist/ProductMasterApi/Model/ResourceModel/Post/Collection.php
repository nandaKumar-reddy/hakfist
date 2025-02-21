<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hakfist\ProductMasterApi\Model\ResourceModel\Post;

use \Hakfist\ProductMasterApi\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hakfist\ProductMasterApi\Model\Productmasterlog', 'Hakfist\ProductMasterApi\Model\ResourceModel\Post');
        $this->_map['fields']['id'] = 'main_table.id';
    }
}
