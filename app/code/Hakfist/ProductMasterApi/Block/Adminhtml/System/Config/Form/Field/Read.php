<?php
namespace Hakfist\ProductMasterApi\Block\Adminhtml\System\Config\Form\Field;

class Read extends \Magento\Config\Block\System\Config\Form\Field
{    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // $element->setDisabled('disabled');
        $element->setData('readonly', 1);
        return $element->getElementHtml();

    }
}