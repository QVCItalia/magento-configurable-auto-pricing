## Overview

This extension is for who may have a configurable product with different prices for the children products and who either update the catalog manually or automatically through the api.

It let you to set the different prices on the simple products and have the related configurable updated with the price differences.

There's another module that let you just use the simple products prices in the catalog: [Magento Configurable Simple](https://github.com/organicinternet/magento-configurable-simple).
It does the job but it also extends a lot of classes that in an already customized installation may have been extended, so the integration may take a lot of time and has a high risk of breaking things.

The approach used here is to just update the price differences on the configurable product depending on the children products prices.

The big deal is that everything is done through an observer in the before_save event, so you won't have to integrate with your installation (unless you have changed something of the Magento core like the prices, but that's unlikely)

## Requirements

Magento EE 1.12-1.13

The extension was tested with these Magento versions, but I'd like to hear from you if you tried on other versions.

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
* Set the "Is split value" flag of the parent product, in tab "Prices".
* Make sure to save the parent product after you have modified the children products prices.
* That's all!!

You can set special price or normal price on the children products and if the special price is actually valid, then it will be used to calculate the differences.

On the parent product will be set the **lowest** price found in the children. Either price, special_price, special_from_date and special_to_date will be set.
From the configuration panel you can either disable this feature or specify other attributes to copy from the child with the lowest price to the parent.

**BE CAREFUL**, if you have some children products with the special_price and some without then the parent will have the settings of the child with the lowest price, that maybe incoherent with the other children. If you won't have this situation, you won't have any trouble.

Eg:

If you have the following product

| Product Type | Sku | Attribute Size | Price |
| ------------ | --- | --------- | ----- |
| Configurable | 1000 |          | 21    |
| Simple       | 1000-A | Small    | 21    |
| Simple       | 1000-B | Big  | 31      |

when you save the configurable product, it will update the pricing value of **Big** attribute to **10** (just for this product, obviously).

## Configuration

In your backend, if you go under System > Configuration > Catalog > Configurable Auto Pricing you can edit a few settings:

| Label | Type | Default | Description |
| ----- | --- | --------- | ----- |
| Enable | Yes/No | Yes | Activate the module |
| Parent price from children | Yes/No | Yes | Copy the attributes price, special_price, special_from_date, special_to_date from the child having the minimum price to the parent, along with the additional attributes you can specify here below. |
| Attributes to copy to parent product | Multivalue | - | These attributes will be copied from the child with the lowest price to the parent product, along with price, special_price, special_from_date, special_to_date. This has no effect if flag "parent price from children" is false |

## Develop

When the price updates are applied on the parent product (this is done in before_save event) another event is triggered: *configurableautopricing_after_apply_price*.
You can attach your observer to that event to edit everything else your customization needs.

## Limitation

To have the price deltas calculation done correctly the price changes **MUST** be based just on 1 attribute.



Example

if I have an item with attributes size and colors then the price can change for every different size or for every different color but not for both.

A product **non-suitable** to this extension:

| Product Type | Sku | Attribute Size | Attribute Color | Price |
| ------------ | --- | --------- | ----- | ----- |
| Configurable | 1000 |          | | 21    |
| Simple       | 1000-A | Small    | Silver | 21    |
| Simple       | 1000-A | Small    | Gold | 23    |
| Simple       | 1000-B | Big  | Silver | 31      |
| Simple       | 1000-B | Big  | Gold | 33      |

*The changes are based both on Size attribute and Color attribute*



Example of a product **suitable** to this extension:

| Product Type | Sku | Attribute Size | Attribute Color | Price |
| ------------ | --- | --------- | ----- | ----- |
| Configurable | 1000 |          | | 21    |
| Simple       | 1000-A | Small    | Silver | 21    |
| Simple       | 1000-A | Small    | Gold | 21    |
| Simple       | 1000-B | Big  | Silver | 31      |
| Simple       | 1000-B | Big  | Gold | 31      |

*The changes are based just on Size attribute*