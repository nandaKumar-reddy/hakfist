<?php

namespace Hakfist\ProductMasterApi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Productmaster extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('temp_products', 'id'); // Productmaster is the database table
    }
}