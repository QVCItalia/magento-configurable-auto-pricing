<?php

class QVC_ConfigurableAutoPricing_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Array of children collections stored by parent id
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection[] $_children
     */
    protected $_children = array();

    /**
     * Array of price deltas stored by parent id
     *
     * @var array
     */
    protected $_deltas = array();

    /**
     * Set on the product the configurable attributes data for setting the price changes passed
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $priceChanges
     * @return $this
     */
    public function setConfigurableAttributesPricing(Mage_Catalog_Model_Product &$product, $priceChanges = array())
    {
        $children = $this->getProductChildren($product);

        if (!$product->isConfigurable() || empty($children) || empty($priceChanges)) {
            return $this;
        }

        $productType = $product->getTypeInstance(true);
        $productType->setProduct($product);
        $attributesData = $productType->getConfigurableAttributesAsArray();

        /**
         * Apply the deltas to the configurable attributes array
         */
        foreach ($attributesData as &$attribute) {
            $attributeCode = $attribute['attribute_code'];

            foreach ($attribute['values'] as &$value) {
                $valueIndex = $value['value_index'];
                if (isset($priceChanges[$attributeCode]) && isset($priceChanges[$attributeCode][$valueIndex])) {
                    $priceChange = $priceChanges[$attributeCode][$valueIndex];
                    $value['pricing_value'] = $priceChange;
                }
            }
        }

        $product->setConfigurableAttributesData($attributesData);

        /**
         * Set the price and eventually the special from and the special to date to the parent
         */
        if ($priceChanges['_cIsSpecialPrice']) {
            $product->setSpecialPrice($priceChanges['_cMinPrice']);
            $product->setSpecialFromDate($priceChanges['_cSpecialFrom']);
            $product->setSpecialToDate($priceChanges['_cSpecialTo']);
        }
        else {
            $product->setPrice($priceChanges['_cMinPrice']);
        }

        return $this;
    }

    /**
     * Get a price deltas array
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getPriceDeltas(Mage_Catalog_Model_Product $product)
    {
        if (isset($this->_deltas[$product->getId()])) {
            return $this->_deltas[$product->getId()];
        }

        if (!$product->isConfigurable()) {
            return null;
        }

        /**
         * Get product attributes
         */
        $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);
        $attributesArray = array();
        foreach ($attributes as $attribute) {
            $attributeObject = Mage::getModel('eav/entity_attribute')->load($attribute->getAttributeId());
            $attributesArray[] = $attributeObject->getAttributeCode();
        }

        /**
         * Parse children to get absolute prices
         */
        $prices = array();
        $minPrice = null;
        $isSpecialPrice = false;
        $specialFrom = '';
        $specialTo = '';

        $children = $this->getProductChildren($product,
            array_merge($attributesArray, array(
                'price',
                'special_price',
                'special_from_date',
                'special_to_date')));

        if (empty($children)) {
            return null;
        }

        foreach ($children as $child) {
            $price = $this->getActualPrice($child);

            foreach ($attributesArray as $attributeCode) {
                $prices[$attributeCode][$child->getData($attributeCode)][] = $price;
            }

            if ($price<$minPrice || $minPrice===null) {
                $minPrice = $price;
            }
            if (!$isSpecialPrice && $this->isActualPriceSpecial($child)) {
                $isSpecialPrice = true;
                $specialFrom = $child->getSpecialFromDate();
                $specialTo = $child->getSpecialToDate();
            }
        }

        /**
         * Parse absolute prices to get price deltas
         */
        $priceChanges = array();
        foreach ($prices as $attributeCode => $attribute) {
            foreach ($attribute as $attributeValue => $attributePrices) {
                $attributePrices = array_unique($attributePrices);
                if (count($attributePrices)==1 && $attributePrices[0] != $minPrice) {
                    $delta = $attributePrices[0]-$minPrice;
                    $priceChanges[$attributeCode][$attributeValue] = $delta;
                }
            }
        }

        if (!empty($priceChanges)) {
            $priceChanges['_cMinPrice'] = $minPrice;
            $priceChanges['_cIsSpecialPrice'] = $isSpecialPrice;
            $priceChanges['_cSpecialFrom'] = $specialFrom;
            $priceChanges['_cSpecialTo'] = $specialTo;
        }

        $this->_deltas[$product->getId()] = $priceChanges;

        return $priceChanges;
    }

    /**
     * Get the actual price for the product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getActualPrice(Mage_Catalog_Model_Product $product)
    {
        if ($this->isActualPriceSpecial($product)) {
            return $product->getSpecialPrice();
        }

        return $product->getPrice();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isActualPriceSpecial(Mage_Catalog_Model_Product $product)
    {
        return $product->getSpecialPrice()
          && Mage::app()->getLocale()->isStoreDateInInterval(Mage::app()->getStore(), $product->getSpecialFromDate(), $product->getSpecialToDate());
    }

    /**
     * Get the collection of children products
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $attributesToSelect
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductChildren(Mage_Catalog_Model_Product $product, array $attributesToSelect = array())
    {
        if (isset($this->_children[$product->getId()])) {
            return $this->_children[$product->getId()];
        }

        $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

        if (empty($childrenIds) || empty($childrenIds[0])) {
            $children = null;
        }
        else {
            $children = Mage::getModel('catalog/product')->getCollection()
                ->addIdFilter($childrenIds)
                ->addAttributeToSelect($attributesToSelect);
        }

        return $this->_children[$product->getId()] = $children;
    }
} 