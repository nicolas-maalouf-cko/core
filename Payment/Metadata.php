<?php
namespace Df\Payment;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
/** @method static Metadata s() */
class Metadata extends \Df\Config\SourceT {
	/**
	 * 2016-07-05
	 * @override
	 * @see \Df\Config\Source::keys()
	 * @return string[]
	 */
	public function keys() {return [
		'customer.name', 'order.id', 'order.items', 'store.domain', 'store.name', 'store.url'
	];}

	/**
	 * 2016-03-09
	 * @override
	 * @see \Df\Config\Source::map()
	 * @used-by \Df\Config\Source::toOptionArray()
	 * @used-by \Dfe\CheckoutCom\Method::charge()
	 * @return array(string => string)
	 */
	public function map() {return array_combine($this->keys(), [
		'Customer Name', 'Order ID', 'Order Items', 'Store Domain', 'Store Name', 'Store URL'
	]);}

	/**
	 * 2016-07-04
	 * @param Store $store
	 * @param Order $order
	 * @param string[] $keys
	 * @return array(string => string)
	 */
	public static function select(Store $store, Order $order, array $keys) {return
		array_combine(
			dfa_select(self::s()->map(), $keys)
			,dfa_select(self::vars($store, $order), $keys)
		)
	;}

	/**
	 * 2016-03-14
	 * @param Store $store
	 * @param Order $order
	 * @return array(string => string)
	 */
	public static function vars(Store $store, Order $order) {return
		array_combine(self::s()->keys(), [
			df_order_customer_name($order)
			, $order->getIncrementId()
			, df_oi_s($order)
			, df_domain($store)
			, $store->getFrontendName()
			, $store->getBaseUrl()
		])
	;}
}