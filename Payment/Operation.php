<?php
namespace Df\Payment;
use Magento\Payment\Model\Info as I;
use Magento\Payment\Model\InfoInterface as II;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Store\Model\Store;
// 2016-08-30
abstract class Operation extends \Df\Core\O {
	/**
	 * 2016-08-30
	 * @used-by \Df\Payment\Operation::amount()
	 * @return float
	 */
	abstract protected function amountFromDocument();

	/**
	 * 2016-09-07
	 * @used-by \Dfe\TwoCheckout\LineItem\Product::price()
	 * @param float $amount
	 * @return float|int|string
	 */
	final public function cFromOrderF($amount) {return
		$this->amountFormat($this->m()->cFromOrder($amount))
	;}

	/**
	 * 2016-08-31
	 * @used-by \Dfe\TwoCheckout\LineItem\Product::price()
	 * @return Method
	 */
	public function m() {return $this[self::$P__METHOD];}

	/**
	 * 2016-09-05
	 * Размер транзакции в платёжной валюте: «Mage2.PRO» → «Payment» → <...> → «Payment Currency».
	 * @return float
	 */
	final protected function amount() {return dfc($this, function() {return
		$this[self::$P__AMOUNT] ?: $this->cFromOrder($this->amountFromDocument())
	;});}

	/**
	 * 2016-09-07
	 * Размер транзакции в платёжной валюте: «Mage2.PRO» → «Payment» → <...> → «Payment Currency».
	 * @return float|int|string
	 */
	final protected function amountF() {return dfc($this, function() {return
		$this->amountFormat($this->amount())
	;});}

	/**
	 * 2016-09-07
	 * Конвертирует денежную величину (в валюте платежа) из обычного числа в формат платёжной системы.
	 * В частности, некоторые платёжные системы хотят денежные величины в копейках (Checkout.com),
	 * обязательно целыми (allPay) и т.п.
	 * @used-by \Df\Payment\Operation::amountF()
	 * @param float $amount
	 * @return float|int|string
	 */
	protected function amountFormat($amount) {return $this->m()->amountFormat($amount);}

	/**
	 * 2016-09-08
	 * Конвертирует денежную величину из формата платёжной системы в обычное число.
	 * Обратная операция по отношению к @see amountFormat()
	 * @param float|int|string $amount
	 * @return float
	 */
	protected function amountParse($amount) {return $this->m()->amountParse($amount);}

	/**
	 * 2016-08-17
	 * Код платёжной валюты: «Mage2.PRO» → «Payment» → <...> → «Payment Currency».
	 * @return string
	 */
	final protected function currencyC() {return $this->m()->cPayment();}

	/**
	 * 2016-09-06
	 * Конвертирует денежную величину из валюты заказа в валюту платежа.
	 * @param float $amount
	 * @return float
	 */
	final protected function cFromOrder($amount) {return $this->m()->cFromOrder($amount);}

	/**
	 * 2016-08-08
	 * @see \Df\Payment\Method::iia()
	 * @param string[] ...$keys
	 * @return mixed|array(string => mixed)
	 */
	final protected function iia(...$keys) {return dfp_iia($this->payment(), $keys);}

	/** @return Order */
	final protected function o() {return $this->payment()->getOrder();}

	/**
	 * 2016-09-06
	 * @return string
	 */
	final protected function oii() {return $this->o()->getIncrementId();}

	/** @return II|I|OP */
	final protected function payment() {return $this->m()->getInfoInstance();}

	/**
	 * 2016-09-06
	 * @return Settings
	 */
	protected function settings() {return $this->m()->s();}

	/** @return Store */
	final protected function store() {return $this->o()->getStore();}

	/**
	 * 2016-08-30
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__AMOUNT, DF_V_FLOAT, false)
			->_prop(self::$P__METHOD, Method::class)
		;
	}
	/**
	 * 2016-09-05
	 * Размер транзакции в валюте платёжных транзакций,
	 * которая настраивается администратором опцией
	 * «Mage2.PRO» → «Payment» → <...> → «Payment Currency».
	 * @see \Df\Payment\Settings::currency()
	 * @var string
	 */
	protected static $P__AMOUNT = 'amount';
	/** @var string */
	protected static $P__METHOD = 'method';
}