<?php

namespace Hakfist\CreateCustomer\Model;

use Magento\Framework\Model\AbstractModel;

class EmailOtpValidation extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Hakfist\CreateCustomer\Model\ResourceModel\EmailOtpValidation');
    }
}
