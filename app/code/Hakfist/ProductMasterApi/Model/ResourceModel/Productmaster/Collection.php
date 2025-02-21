<?php

namespace Hakfist\ProductMasterApi\Model\ResourceModel\Productmaster;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Hakfist\ProductMasterApi\Model\Productmaster',
            'Hakfist\ProductMasterApi\Model\ResourceModel\Productmaster'
        );
    }
}