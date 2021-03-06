define([
	'df'
   	,'jquery'
	,'Magento_Catalog/js/price-utils'
	,'Magento_Checkout/js/model/quote'
	/**
	 * 2016-04-17
	 * How to get the customer's data on the frontend checkout page's client side (with JavaScript)
	 * using the «Magento_Customer/js/model/customer» object?
	 * https://mage2.pro/t/1245
	 *
	 * The «Magento_Customer/js/model/customer» JavaScript object interface
	 * https://mage2.pro/t/1252
	 */
	,'Magento_Customer/js/model/customer'
	/**
	 * 2016-04-17
	 * How is the «Magento_Customer/js/customer-data» object implemented and used?
	 * https://mage2.pro/t/1246
	 */
	,'Magento_Customer/js/customer-data'
	/**
	 * 2016-04-17
	 * How is the «Magento_Checkout/js/checkout-data» object implemented and used?
	 * https://mage2.pro/t/1293
	 *
	 * How to get the checkout data on the frontend checkout page's client side (with JavaScript)?
	 * https://mage2.pro/t/1292
	 *
	 * https://mage2.pro/t/1294
	 * The «Magento_Checkout/js/checkout-data» JavaScript object interface and its implementation
	 */
	,'Magento_Checkout/js/checkout-data'
], function (df, $, priceUtils, quote, customer, customerData, checkoutData) {
    'use strict';
	return {
		/**
		 * 2016-09-30
		 * @returns {Object=}
		 */
		addressB: function() {return quote.billingAddress();},
		/**
		 * 2016-09-30
		 * @returns {Object=}
		 */
		addressS: function() {return quote.shippingAddress();},
		/**
		 * 2016-08-25
		 * 3-значный код валюты заказа (не учётной)
		 * @type {String}
		 */
		currency: window.checkoutConfig.quoteData.quote_currency_code,
		/**
		 * 2016-04-20
		 * How to get the current customer's email on the frontend checkout screen?
		 * https://mage2.pro/t/1295
		 * @returns {String}
		 */
		email: function() {
			/**
			 * 2016-04-20
			 * How to programmatically check on the frontend checkout screen client side (with JavaScript) whether the customer is authenticated (logged in)?
			 * https://mage2.pro/t/1303
			 */
 			return (
				window.isCustomerLoggedIn
				? window.customerData.email
				/**
				 * 2016-06-01
				 * Брать надо именно getValidatedEmailValue(), а не getInputFieldEmailValue():
				 *
				 * What is the difference between «Magento_Checkout/js/checkout-data»'s
				 * getValidatedEmailValue() and getInputFieldEmailValue() methods?
				 * https://mage2.pro/t/1733
				 *
				 * How are the «Magento_Checkout/js/checkout-data»'s
				 * setValidatedEmailValue() and setInputFieldEmailValue() methods
				 * implemeted and used? https://mage2.pro/t/1734
				 */
				: checkoutData.getValidatedEmailValue()
			);
		},
		/**
		 * 2016-08-07
		 * How to format a money value (e.g. price) in JavaScript?  https://mage2.pro/t/1932
		 * @param {Number} amount
		 * @param {Object=} format [optional]
		 * @returns {String}
		 */
		formatMoney: function(amount, format) {return priceUtils.formatPrice(
			amount, df.arg(format, quote.getPriceFormat())
		);},
		/**
		 * 2016-09-30
		 * @returns {jqXHR}
		 */
		geo: df.c(function() {return $.getJSON('//freegeoip.net/json/');}),
		/**
		 * 2016-07-16
		 * Returns the current quote's grand total value.
		 * By analogy with https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Checkout/view/frontend/web/js/view/summary/grand-total.js#L20-L26
		 * How to get the current quote's grant total value
		 * on the frontend checkout page's client side?  https://mage2.pro/t/1873
		 * @returns {Number}
		 */
		grandTotal: function() {
			/** @type {Object} */
			var totals = quote.getTotals()();
			/**
			 * 2016-08-07
			 * Раньше здесь стоял и правильно работал код totals['grand_total']
			 * однако сегодня заметил, что он стал давать неправильный результат:
			 * не учитывает налог. Поэтому изменил код на другой.
			 *
			 * Можно было ещё использовать модель 'Magento_Checkout/js/model/totals'
			 * и получить результат так: totals.getSegment('grand_total').value
			 * Однако у getSegment алгоритм неоптимален: https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Checkout/view/frontend/web/js/model/totals.js#L32-L50
			 *
			 * 2016-09-06
			 * Изучил описанную выше ситуацию глубже.
			 * Оказалось, что «grand_total» не включает налог,
			 * в то время как «base_grand_total» включает налог, например:
			 * base_grand_total: 83.83
			 * grand_total: 74.7
			 * base_tax_amount: 9.13
			 * tax_amount: 9.13
			 *
			 * Поэтому для метода grandTotalBase() (расположен ниже)
			 * мы используем более короткий алгоритм.
			 */
			/** @type {Object[]} */
			var segments = totals['total_segments'];
			return segments[segments.length - 1].value;
		},
		/**
		 * 2016-09-06
		 * @returns {Number}
		 */
		grandTotalBase: function() {return quote.getTotals()()['base_grand_total'];}
	};
});