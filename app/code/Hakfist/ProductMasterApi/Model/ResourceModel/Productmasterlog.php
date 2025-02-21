<?php

namespace Hakfist\ProductMasterApi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Productmasterlog extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('productmasterapi_log', 'id'); // Productmaster is the database table
    }
}