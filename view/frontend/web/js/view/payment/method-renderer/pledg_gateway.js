define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'mage/url'
], function ($, Component, url) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: false,

        defaults: {
            template: 'Pledg_PledgPaymentGateway/payment/form'
        },

        afterPlaceOrder: function () {
            window.location.replace(url.build('pledg/checkout/pay'));
        },

        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].title;
        },

        getDescription: function () {
            return window.checkoutConfig.payment[this.getCode()].description;
        },

        getPledgLogo: function () {
            return window.checkoutConfig.payment[this.getCode()].logo;
        },
    });
});


