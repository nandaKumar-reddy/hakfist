<?php

namespace Hakfist\Hideprice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Hakfist\Hideprice\Helper\Data as ProductAvailableHelper;

class CollectionObserver implements ObserverInterface
{
    /**
     * @var \Hakfist\Hideprice\Helper\Data
     */
    protected $helper;

    /**
     * @param ProductAvailableHelper $helper
     */
    public function __construct(
		ProductAvailableHelper $helper
    ) {
		$this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		if ($this->helper->hidePrice()) {
			$collection = $observer->getEvent()->getCollection();
			foreach ($collection as $product) {
				$product->setCanShowPrice(false);
			}
		}
    }
}
