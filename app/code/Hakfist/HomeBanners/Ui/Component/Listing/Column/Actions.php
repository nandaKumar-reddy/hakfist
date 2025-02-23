<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hakfist\HomeBanners\Ui\Component\Listing\Column;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PageActions
 */
class Actions extends Column {
	/** Url path */
	const URL_PATH_EDIT = 'homebanners/index/edit';
	const URL_PATH_DELETE = 'homebanners/index/delete';

	/** @var UrlBuilder */
	protected $actionUrlBuilder;

	/** @var UrlInterface */
	protected $urlBuilder;

	/**
	 * @var string
	 */
	private $editUrl;

	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param UrlBuilder $actionUrlBuilder
	 * @param UrlInterface $urlBuilder
	 * @param array $components
	 * @param array $data
	 * @param string $editUrl
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlBuilder $actionUrlBuilder,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = [],
		$editUrl = self::URL_PATH_EDIT
	) {
		$this->urlBuilder = $urlBuilder;
		$this->actionUrlBuilder = $actionUrlBuilder;
		$this->editUrl = $editUrl;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				$name = $this->getData('name');
				if (isset($item['homebanners_id'])) {
					$item[$name]['edit'] = [
						'href' => $this->urlBuilder->getUrl($this->editUrl, ['homebanners_id' => $item['homebanners_id']]),
						'label' => __('Edit'),
					];
					/*$item[$name]['delete'] = [
						                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['homebanners_id' => $item['homebanners_id']]),
						                        'label' => __('Delete'),
						                        'confirm' => [
						                            'title' => __('Delete ${ $.$data.title }'),
						                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
						                        ]
					*/
				}
			}
		}

		return $dataSource;
	}
}
