<?php
namespace Df\Payment\Block;
use Df\Payment\Info\Dictionary;
use Df\Payment\Method;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Payment\Model\Info as I;
use Magento\Payment\Model\InfoInterface as II;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
/**
 * 2016-05-06
 * По аналогии с @see \Magento\Braintree\Block\Info
 * https://github.com/magento/magento2/blob/135f967/app/code/Magento/Braintree/Block/Info.php
 * https://mage2.pro/t/898/3
 *
 * 2016-08-29
 * Класс @see \Magento\Payment\Block\ConfigurableInfo присутствует уже в Magento 2.0.0:
 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Payment/Block/ConfigurableInfo.php
 * Поэтому мы можем от него наследоваться без боязни сбоев.
 */
class Info extends \Magento\Payment\Block\ConfigurableInfo {
	/**
	 * 2016-05-21
	 * @override
	 * @see \Magento\Framework\View\Element\AbstractBlock::escapeHtml()
	 * @param array|string $data
	 * @param null $allowedTags
	 * @return array|string
	 */
	public function escapeHtml($data, $allowedTags = null) {return $data;}

	/**
	 * 2016-08-29
	 * В родительской реализации меня не устраивает такой код:
		$store = $method->getStore();
		if (!$store) {
			return false;
		}
	 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Payment/Block/Info.php#L132-L135
	 * В моём случае на витрине $method->getStore() возвращает null (не разбирался, почему)
	 * и тогда, соответственно, @see \Magento\Payment\Block\Info::getIsSecureMode() возвразает false,
	 * т.е. система считает, что мы находимся в административной части, что неверно.
	 *
	 * @override
	 * @see \Magento\Payment\Block\ConfigurableInfo::getIsSecureMode()
	 * @return bool
	 */
	public function getIsSecureMode() {return !df_is_backend();}

	/**
	 * 2016-05-23
	 * @override
	 * @see \Magento\Framework\View\Element\Template::getTemplate()
	 * @see \Magento\Payment\Block\Info::$_template
	 * @return string
	 */
	public function getTemplate() {
		/** @var string $pr */
		$pr = parent::getTemplate();
		return $this->isBackend() && 'Magento_Payment::info/default.phtml' === $pr
			? 'Df_Payment::info.phtml' : $pr
		;
	}

	/**
	 * 2016-07-19
	 * @return array(string => string)
	 */
	public function getSpecificInformation() {return dfc($this, function() {
		/**
		 * 2016-08-09
		 * К сожалению, мы не можем делать нецелые веса:
		 * ttp://php.net/manual/function.usort.php
		 * «Returning non-integer values from the comparison function, such as float,
		 * will result in an internal cast to integer of the callback's return value.
		 * So values such as 0.99 and 0.1 will both be cast to an integer value of 0,
		 * which will compare such values as equal.»
		 * Нецелые веса позволили бы нам гарантированно запихнуть
		 * безвесовые записи между весовыми, но увы...
		 */
		$this->dic()->addA(parent::getSpecificInformation());
		$this->prepareDic();
		return $this->dic()->get();
	});}

	/**
	 * 2016-05-21
	 * @used-by vendor/mage2pro/core/Payment/view/adminhtml/templates/info/default.phtml
	 * @param string|null $key [optional]
	 * @return II|I|OP|mixed
	 */
	public function ii($key = null) {
		/** @var II|I|OP $result */
		$result = $this->getInfo();
		return is_null($key) ? $result : $result[$key];
	}

	/**
	 * 2016-05-23
	 * @used-by https://code.dmitry-fedyuk.com/m2e/2checkout/blob/1.0.4/view/frontend/templates/info.phtml#L5
	 * @used-by \Dfe\TwoCheckout\Block\Info::_prepareSpecificInformation()
	 * @return bool
	 */
	public function isTest() {return dfc($this, function() {return $this->iia(Method::II__TEST);});}

	/**
	 * 2016-07-13
	 * @return string
	 */
	public function title() {return df_cc_s(
		$this->escapeHtml($this->getMethod()->getTitle())
		,!$this->isTest() ? null : sprintf("(%s)", __($this->testModeLabelLong()))
	);}

	/**
	 * 2016-08-09
	 * @return Dictionary
	 */
	protected function dic() {return dfc($this, function() {return df_create(Dictionary::class);});}

	/**
	 * 2016-05-06
	 * @override
	 * @see \Magento\Payment\Block\ConfigurableInfo::getLabel()
	 * @used-by \Magento\Payment\Block\ConfigurableInfo::setDataToTransfer()
	 * @param string $field
	 * @return Phrase
	 */
	protected function getLabel($field) {return __($field);}

	/**
	 * 2016-05-21
	 * @param string[] ...$keys
	 * @return mixed|array(string => mixed)
	 */
	protected function iia(...$keys) {
		return !$keys ? $this->ii()->getAdditionalInformation() : (
			1 === count($keys)
			? $this->ii()->getAdditionalInformation(df_first($keys))
			: dfa_select_ordered($this->ii()->getAdditionalInformation(), $keys)
		);
	}

	/**
	 * 2016-08-20
	 * @return bool
	 */
	protected function isBackend() {return df_is_backend();}

	/**
	 * 2016-08-20
	 * @return bool
	 */
	protected function isFrontend() {return !df_is_backend();}

	/** @return Method */
	protected function m() {return $this->ii()->getMethodInstance();}

	/**
	 * 2016-07-13
	 * @param DataObject $result
	 */
	protected function markTestMode(DataObject $result) {
		!$this->isTest() ?: $result->setData('Mode', __($this->testModeLabel()));
	}

	/**
	 * 2016-08-09
	 * @used-by \Df\Payment\Block\Info::getSpecificInformation()
	 * @return void
	 */
	protected function prepareDic() {}

	/**
	 * 2016-07-13
	 * @return string
	 */
	protected function testModeLabel() {return 'Test';}

	/**
	 * 2016-07-13
	 * @return string
	 */
	protected function testModeLabelLong() {return 'Test Mode';}

	/**
	 * 2016-08-20
	 * @return T
	 */
	protected function transF() {return dfc($this, function() {return
		df_trans_by_payment_first($this->ii())
	;});}

	/**
	 * 2016-08-20
	 * @return T
	 */
	protected function transL() {return dfc($this, function() {return
		df_trans_by_payment_last($this->ii())
	;});}
}