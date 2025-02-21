<?php

namespace Hakfist\ProductData\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductData implements ResolverInterface
{
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $stockRegistry;
    protected $storeManager;
    protected $configurable;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        Configurable $configurable
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->configurable = $configurable;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->getProductData($args);
    }

    private function getProductData($args)
    {

        if (empty($args['sku'])) {
            throw new \InvalidArgumentException('SKU argument is required');
        }

        $sku = $args['sku'];

        try {
            $product = $this->productRepository->get($sku);
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);

            // Get current currency code
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $customizableOptions = $this->getCustomizableOptions($product);
            // dd($customizableOptions);

            // Get child products if it's a configurable product
            $childProducts = [];
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $childProducts = $this->getChildProducts($product, $currencyCode);
            }

            return [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'currency' => $currencyCode,
                'description' => $product->getDescription(),
                'quantity' => $stockItem->getQty(),
                'created_at' => $product->getCreatedAt(),
                'updated_at' => $product->getUpdatedAt(),
                'status' => $product->getStatus(),
                'visibility' => $product->getVisibility(),
                'color' => $product->getColor(),
                'tax_class_id' => $product->getTaxClassId(),
                'capacity' => $product->getCustomAttribute('capacity') ? $product->getCustomAttribute('capacity')->getValue() : null,
                'size' => $product->getCustomAttribute('size') ? $product->getCustomAttribute('size')->getValue() : null,
                'filter_stock' => $product->getCustomAttribute('filter_stock') ? $product->getCustomAttribute('filter_stock')->getValue() : null,
                'label' => $product->getCustomAttribute('label') ? $product->getCustomAttribute('label')->getValue() : null,
                'uom' => $product->getCustomAttribute('uom') ? $product->getCustomAttribute('uom')->getValue() : null,
                'digitalspace' => $product->getCustomAttribute('digitalspace') ? $product->getCustomAttribute('digitalspace')->getValue() : null,
                'material' => $product->getCustomAttribute('material') ? $product->getCustomAttribute('material')->getValue() : null,
                'printing_techniques' => $product->getCustomAttribute('printing_techniques') ? $product->getCustomAttribute('printing_techniques')->getValue() : null,
                'gift_message_available' => $product->getCustomAttribute('gift_message_available') ? $product->getCustomAttribute('gift_message_available')->getValue() : null,
                'url_key' => $product->getUrlKey(),
                'options_container' => $product->getOptionsContainer(),
                'msrp_display_actual_price_type' => $product->getMsrpDisplayActualPriceType(),
                'dimensions' => $product->getCustomAttribute('dimensions') ? $product->getCustomAttribute('dimensions')->getValue() : null,
                'dimensionswidth' => $product->getCustomAttribute('dimensionswidth') ? $product->getCustomAttribute('dimensionswidth')->getValue() : null,
                'dimensionsvolume' => $product->getCustomAttribute('dimensionsvolume') ? $product->getCustomAttribute('dimensionsvolume')->getValue() : null,
                'dimensionsdepth' => $product->getCustomAttribute('dimensionsdepth') ? $product->getCustomAttribute('dimensionsdepth')->getValue() : null,
                'grossweight' => $product->getCustomAttribute('grossweight') ? $product->getCustomAttribute('grossweight')->getValue() : null,
                'cartonheight' => $product->getCustomAttribute('cartonheight') ? $product->getCustomAttribute('cartonheight')->getValue() : null,
                'cartonwidth' => $product->getCustomAttribute('cartonwidth') ? $product->getCustomAttribute('cartonwidth')->getValue() : null,
                'cartonlength' => $product->getCustomAttribute('cartonlength') ? $product->getCustomAttribute('cartonlength')->getValue() : null,
                'cartonvolume' => $product->getCustomAttribute('cartonvolume') ? $product->getCustomAttribute('cartonvolume')->getValue() : null,
                'cartongrossweight' => $product->getCustomAttribute('cartongrossweight') ? $product->getCustomAttribute('cartongrossweight')->getValue() : null,
                'cartonquantity' => $product->getCustomAttribute('cartonquantity') ? $product->getCustomAttribute('cartonquantity')->getValue() : null,
                'individualpackagingafterprinti' => $product->getCustomAttribute('individualpackagingafterprinti') ? $product->getCustomAttribute('individualpackagingafterprinti')->getValue() : null,
                'image' => $product->getImage(),
                'small_image' => $product->getSmallImage(),
                'thumbnail' => $product->getThumbnail(),
                'swatch_image' => $product->getSwatchImage(),
                'customizable_options' => $customizableOptions,
                'child_products' => $childProducts,
            ];
        } catch (NoSuchEntityException $e) {
            throw new \GraphQL\Error\UserError('Product with SKU ' . $sku . ' not found.');
        }
    }

    private function getCustomizableOptions($product)
    {
        $options = [];
        foreach ($product->getOptions() as $option) {
            $values = [];
            if ($option->getValues()) {
                foreach ($option->getValues() as $value) {
                    $values[] = [
                        'value_id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'price' => $value->getPrice(),
                        'price_type' => $value->getPriceType(),
                        'sku' => $value->getSku(),
                        'description' =>$value->getDescription(),

                    ];
                }
            }
            $options[] = [
                'option_id' => $option->getOptionId(),
                'title' => $option->getTitle(),
                'type' => $option->getType(),
                'image' => $option->getImage(),
                'is_special_offer' =>$option->getIsSpecialOffer(),
                'is_require' => $option->getIsRequire(),
                'sort_order' => $option->getSortOrder(),
                'values' => $values,
            ];
        }
        return $options;
    }

    private function getChildProducts($product,$currencyCode)
    {
        $children = [];
        $childProducts = $this->configurable->getUsedProducts($product);
        foreach ($childProducts as $childProduct) {
            $stockItem = $this->stockRegistry->getStockItemBySku($childProduct->getSku());
            $children[] = [
                'id' => $childProduct->getId(),
                'sku' => $childProduct->getSku(),
                'name' => $childProduct->getName(),
                'price' => $childProduct->getPrice(),
                'currency' => $currencyCode,
                'color' => $childProduct->getColor(),
                'quantity' => $stockItem->getQty(),
                'image' => $childProduct->getImage(),
                'small_image' => $childProduct->getSmallImage(),
                'thumbnail' => $childProduct->getThumbnail(),
                'swatch_image' => $childProduct->getSwatchImage(),
                'status' => $childProduct->getStatus(),
            ];
        }
        return $children;
    }
}
