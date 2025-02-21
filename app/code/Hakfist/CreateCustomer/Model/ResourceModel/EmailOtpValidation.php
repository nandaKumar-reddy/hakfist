<?php

namespace Hakfist\CreateCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EmailOtpValidation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('email_otp_validation', 'id'); // Initialize table and primary key field
    }
}
