<?php

namespace Hakfist\ProductMasterApi\Model;;

use Magento\Framework\Model\AbstractModel;

class Productmaster extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Hakfist\ProductMasterApi\Model\ResourceModel\Productmaster');
    }
}