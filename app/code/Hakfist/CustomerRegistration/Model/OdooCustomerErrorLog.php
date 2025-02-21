<?php

namespace Hakfist\CustomerRegistration\Model;

use Magento\Framework\Model\AbstractModel;

class OdooCustomerErrorLog extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerErrorLog');
    }
}
