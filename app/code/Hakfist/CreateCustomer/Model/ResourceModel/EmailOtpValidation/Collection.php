<?php

namespace Hakfist\CreateCustomer\Model\ResourceModel\EmailOtpValidation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Hakfist\CreateCustomer\Model\EmailOtpValidation as Model;
use Hakfist\CreateCustomer\Model\ResourceModel\EmailOtpValidation as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
