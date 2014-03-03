## Overview

This extension is for who may have a configurable product with different prices for the children products and who either update the catalog manually or automatically through the api.

It let you to set the different prices on the simple products and have the related configurable updated with the price differences.

There's another module that let you just use the simple products prices in the catalog: [Magento Configurable Simple](https://github.com/organicinternet/magento-configurable-simple).
It does the job but it also extends a lot of classes that in an already customized installation may have been extended, so the integration may take a lot of time and has a high risk of breaking things.

The approach used here is to just update the price differences on the configurable product depending on the children products prices.

The big deal is that everything is done through an observer in the before_save event, so you won't have to integrate with your installation (unless you have changed something of the Magento core like the prices, but that's unlikely)

## Requirements

Magento EE 1.12

The extension was tested just with this Magento version, but I'd like to hear from you if you tried this on other versions.

## Installation
### Modman

Go in your project root folder and run

    $ git submodule add git://github.com/QVCItalia/magento-configurable-auto-pricing.git .modman/QVC_ConfigurableAutoPricing
    $ modman deploy QVC_ConfigurableAutoPricing

Clean the cache

### Manually

* Download latest version [here](https://github.com/QVCItalia/magento-configurable-auto-pricing/archive/master.zip)
* Unzip in Magento root folder
* Clean the cache

## Usage

* Set the individual prices on the children products.
* Make sure to save the parent product after you have modified the children products prices.
* That's all!!

You can set special price or normal price on the children products and if the special price is actually valid, then it will be used to calculate the differences.

If on the children products you set either a special price **AND** a normal price, on the configurable product will be set the lowest price of all as a special price and the special_from_date and the special_to_date of the first child with a special price found.

**BE CAREFUL**, you should avoid a situation like that. If you're setting a special price then all the children should have a special price and the special_from_date and the special_to_date should be the same. In this way the configurable product will be 100% coherent with the children.

Eg:

If you have the following product

| Product Type | Sku | Attribute | Price |
| ------------ | --- | --------- | ----- |
| Configurable | 1000 |          | 21    |
| Simple       | 1000-A | Small    | 21    |
| Simple       | 1000-B | Big  | 31      |

when you save the configurable product, it will update the pricing value of **Big** attribute to **10** (just for this product, obviously).
