<?php

class QVC_ConfigurableAutoPricing_Model_PriceChanges
{
    /**
     * @var bool
     */
    protected $_isPriceSet;

    /**
     * @var float
     */
    protected $_price;

    /**
     * @var float
     */
    protected $_specialPrice;

    /**
     * @var string
     */
    protected $_specialFrom;

    /**
     * @var string
     */
    protected $_specialTo;

    /**
     * @var array
     */
    protected $_deltas = array();

    /**
     * @param float $price
     * @param float|null $specialPrice
     * @param string|null $specialFrom
     * @param string|null $specialTo
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPrice($price, $specialPrice = null, $specialFrom = null, $specialTo = null)
    {
        if ($price === null) {
            throw new InvalidArgumentException("Price can't be null");
        }
        if (!is_numeric($price)) {
            throw new InvalidArgumentException("Price must be numeric");
        }

        $this->_isPriceSet      = true;
        $this->_price           = $price;
        $this->_specialPrice    = $specialPrice;
        $this->_specialFrom     = $specialFrom;
        $this->_specialTo       = $specialTo;

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function applyPrice(Mage_Catalog_Model_Product &$product)
    {
        if ($this->_isPriceSet) {
            $product->setPrice($this->_price);
            $product->setSpecialPrice($this->_specialPrice);
            $product->setSpecialFromDate($this->_specialFrom);
            $product->setSpecialToDate($this->_specialTo);
        }
    }

    /**
     * @param $attributeCode
     * @param $attributeValue
     * @param $delta
     * @return $this
     */
    public function setPriceDelta($attributeCode, $attributeValue, $delta)
    {
        $this->_deltas[$attributeCode][$attributeValue] = $delta;

        return $this;
    }

    /**
     * @param $attributeCode
     * @param $attributeValue
     * @return null
     */
    public function getPriceDelta($attributeCode, $attributeValue)
    {
        if (isset($this->_deltas[$attributeCode]) && isset($this->_deltas[$attributeCode][$attributeValue])) {
            return $this->_deltas[$attributeCode][$attributeValue];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        return !empty($this->_deltas);
    }

    /**
     * @return array
     */
    public function getPriceDeltasArray()
    {
        return $this->_deltas;
    }
} 