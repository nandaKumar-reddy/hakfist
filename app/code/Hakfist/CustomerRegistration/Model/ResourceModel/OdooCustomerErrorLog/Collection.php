<?php

namespace Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerErrorLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Hakfist\CustomerRegistration\Model\OdooCustomerErrorLog as Model;
use Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerErrorLog as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
