<?php

namespace Hakfist\ProductMasterApi\Model\ResourceModel\Productmasterlog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Hakfist\ProductMasterApi\Model\Productmasterlog',
            'Hakfist\ProductMasterApi\Model\ResourceModel\Productmasterlog'
        );
    }
}