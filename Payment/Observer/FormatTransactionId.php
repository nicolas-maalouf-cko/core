<?php
namespace Df\Payment\Observer;
use Df\Payment\Method;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface as IMethod;
use Magento\Sales\Model\Order\Payment\Transaction;
/**
 * 2016-08-20
 * Событие: sales_order_payment_transaction_html_txn_id
 * @see \Magento\Sales\Model\Order\Payment\Transaction::getHtmlTxnId()
 * How is the «sales_order_payment_transaction_html_txn_id» event triggered and handled?
 * https://mage2.pro/t/1965
 */
class FormatTransactionId implements ObserverInterface {
	/**
	 * 2016-08-20
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param O $o
	 * @return void
	 */
	public function execute(O $o) {
		/** @var Transaction $t */
		$t = $o['data_object'];
		/** @var IMethod|Method $m */
		$m = dfp_method_by_trans($t);
		if (dfp_method_is_my($m)) {
			/** @used-by \Magento\Sales\Model\Order\Payment\Transaction::getHtmlTxnId() */
			$t['html_txn_id'] = $m->formatTransactionId($t->getTxnId());
		}
	}
}