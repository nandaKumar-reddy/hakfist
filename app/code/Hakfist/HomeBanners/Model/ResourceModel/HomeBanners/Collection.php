<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hakfist\HomeBanners\Model\ResourceModel\HomeBanners;

use \Hakfist\HomeBanners\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'homebanners_id';

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
        $this->_init('Hakfist\HomeBanners\Model\HomeBanners', 'Hakfist\HomeBanners\Model\ResourceModel\HomeBanners');
        $this->_map['fields']['homebanners_id'] = 'main_table.homebanners_id';
    }
}
