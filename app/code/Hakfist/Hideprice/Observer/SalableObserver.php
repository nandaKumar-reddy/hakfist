<?php

namespace Hakfist\Hideprice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Hakfist\Hideprice\Helper\Data as ProductAvailableHelper;

class SalableObserver implements ObserverInterface
{
    /**
     * @var \Hakfist\Hideprice\Helper\Data
     */
    protected $_helper;

    /**
     * @param ProductAvailableHelper $helper
     */
    public function __construct(
		ProductAvailableHelper $helper
    ) {
		$this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		if ($this->_helper->hideAddToCart()) {
			$salable = $observer->getEvent()->getSalable();
			// $salable->setIsSalable(false);
		}
    }
}
