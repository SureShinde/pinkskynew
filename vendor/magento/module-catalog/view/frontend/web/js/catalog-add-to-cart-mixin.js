define([
        'jquery',
        'mage/translate',
        'jquery/ui'
    ],
    function ($, $t) {
        'use strict';

        return function (target) {
            $.widget('mage.catalogAddToCart', target, {
                options: {
                    addToCartButtonTextWhileAdding: $t('Adding Testing...'),
                    addToCartButtonTextAdded: $t('Added Testing'),
                    addToCartButtonTextDefault: $t('Add to Cart Testing')
                }
            });

            return $.mage.catalogAddToCart;
        };
    });