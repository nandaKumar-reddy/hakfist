<?php

namespace Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerSuccessLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Hakfist\CustomerRegistration\Model\OdooCustomerSuccessLog as Model;
use Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerSuccessLog as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
