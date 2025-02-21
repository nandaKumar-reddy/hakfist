<?php

namespace Hakfist\ProductMasterApi\Block\Adminhtml\System\Config;

class Date extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat(null);
        // return parent::render($element);
        return "06032020_10:55:11";
    }
}