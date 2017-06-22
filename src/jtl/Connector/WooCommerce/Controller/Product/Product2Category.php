<?php
/**
 * @author    Sven Mäurer <sven.maeurer@jtl-software.com>
 * @copyright 2010-2013 JTL-Software GmbH
 */

namespace jtl\Connector\WooCommerce\Controller\Product;

use jtl\Connector\Model\Identity;
use jtl\Connector\Model\Product as ProductModel;
use jtl\Connector\Model\Product2Category as Product2CategoryModel;
use jtl\Connector\WooCommerce\Controller\BaseController;
use jtl\Connector\WooCommerce\Logger\WpErrorLogger;
use jtl\Connector\WooCommerce\Utility\IdConcatenation;

class Product2Category extends BaseController
{
    public function pullData(\WC_Product $product)
    {
        $productCategories = [];

        if (!$product->is_type('variation')) {
            $categories = $product->get_category_ids();

            if ($categories instanceof \WP_Error) {
                WpErrorLogger::getInstance()->logError($categories);

                return [];
            }

            foreach ($categories as $category) {
                $productCategory = (new Product2CategoryModel())
                    ->setId(new Identity(IdConcatenation::link([$product->get_id(), $category])))
                    ->setProductId(new Identity($product->get_id()))
                    ->setCategoryId(new Identity($category));

                $productCategories[] = $productCategory;
            }
        }

        return $productCategories;
    }

    public function pushData(ProductModel $product, array $model)
    {
        $wcProduct = \wc_get_product($product->getId()->getEndpoint());
        $wcProduct->set_category_ids($this->getCategoryIds($product->getCategories()));
        $wcProduct->save();
    }

    private function getCategoryIds(array $categories)
    {
        $productCategories = [];

        /** @var Product2CategoryModel $category */
        foreach ($categories as $category) {
            $categoryId = $category->getCategoryId()->getEndpoint();

            if (!empty($categoryId)) {
                $productCategories[] = (int)$categoryId;
            }
        }

        return $productCategories;
    }
}
