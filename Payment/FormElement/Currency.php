<?php
// 2016-09-03
namespace Df\Payment\FormElement;
use Magento\Config\Model\Config\Source\Locale\Currency as Source;
use Magento\Directory\Model\Currency as CurrencyM;
use Magento\Framework\App\ScopeInterface as S;
use Magento\Store\Model\Store;
class Currency extends \Df\Framework\Form\Element\Select2 {
	/**
	 * 2016-09-04
	 * @override
	 * @see \Df\Framework\Form\Element\Select::getValue()
	 * @used-by \Df\Framework\Form\Element\Select2::setRenderer()
	 * @return string|null
	 */
	public function getValue() {return parent::getValue() ?: self::$ORDER;}

	/**
	 * 2016-09-04
	 * @override
	 * @see \Df\Framework\Form\Element\Select2::getValues()
	 * @used-by \Df\Framework\Form\Element\Select2::setRenderer()
	 * Делаем по аналогии с @see \Magento\Config\Model\Config\Structure\Element\Field::getOptions()
	 * https://github.com/magento/magento2/blob/2.1.1/app/code/Magento/Config/Model/Config/Structure/Element/Field.php#L362-L379
	 *
	 * Наша реализация короче, чем здесь:
	 * @see \Magento\Config\Model\Config\Structure\Element\Field::_getOptionsFromSourceModel()
	 * https://github.com/magento/magento2/blob/2.1.1/app/code/Magento/Config/Model/Config/Structure/Element/Field.php#L421-L456
	 *
	 * И лучше, потому что @uses df_currencies_options() не вываливает список всех валют, как в ядре,
	 * а показывает только доступные для данной витрины валюты.
	 * Более того, проверяет, имеется ли в системе курс обмена данной валюты на учётную.
	 *
	 * Подумав, заметил, что вопрос-то тонкий...
	 * Мы можем находиться, например, на области действия настроек website или default,
	 * в то время как на области store настройки валют могут быть разными...
	 * Вызов @uses df_currencies_options() без параметров использует именно настройки по-умолчанию.
	 * Это неидеальное решение.
	 * Пока клиенты не жалуются (и неочевидно, будут ли вообще жаловаться) — пусть будет так.
	 * А если надо будет, то текущий scope можено получить так:
	 * df_scope($this['scope_id'], $this['scope']);
	 *
	 * @return array(array(string => string))
	 */
	public function getValues() {return dfc($this, function() {return
		df_map_to_options_t([
			self::$ORDER => 'Order Currency', self::$BASE => 'Base Currency'
		]) + df_currencies_options()
	;});}

	/**
	 * 2016-09-03
	 * @override
	 * @see \Df\Framework\Form\Element\Select2::onFormInitialized()
	 * @return void
	 */
	public function onFormInitialized() {
		parent::onFormInitialized();
		df_fe_init($this, __CLASS__);
	}

	/**
	 * 2016-09-05
	 * Текущая валюта может меняться динамически (в том числе посетителем магазина и сессией),
	 * поэтому мы используем параметр $store, а не $scope
	 * @param string $code
	 * @param null|string|int|S|Store $store [optional]
	 * @param CurrencyM|string|null $orderCurrency [optional]
	 * @return CurrencyM
	 */
	public static function v($code, $store = null, $orderCurrency = null) {return
		df_currency(dftr($code ?: self::$ORDER, self::map($store, $orderCurrency)))
	;}

	/**
	 * @override
	 * 2016-09-03
	 * Этот стиль присваивается:
	 * 1) Выпадающему списку select2.
	 * 2) Оригинальному элементу select (который при использовании select2 вроде бы роли не играет).
	 * 3) Родительскому контейнеру .df-field, который присутствует в том случае,
	 * если наш элемент управления был создан внутри нашего нестандартного филдсета,
	 * и осутствует, если наш элемент управления является элементом управления вернхнего уровня
	 * (то есть, указан в атрибуте «type» тега <field>).
	 * @see \Df\Framework\Form\Element\Select2::customCssClass()
	 * @used-by \Df\Framework\Form\Element\Select2::setRenderer()
	 * @return string
	 */
	protected function customCssClass() {return 'df-payment-currency';}

	/**
	 * 2016-09-05
	 * @used-by \Df\Payment\FormElement\Currency::v()
	 * @param null|string|int|S|Store $store [optional]
	 * @param CurrencyM|string|null $orderCurrency [optional]
	 * @return array(string => CurrencyM|string|null)
	 */
	private static function map($store = null, $orderCurrency = null) {return dfcf(
		function($store = null, $orderCurrency = null) {return [
			self::$BASE => df_currency_base($store)
			,self::$ORDER => $orderCurrency ?: df_currency_current($store)
		];}
	, func_get_args());}

	/**
	 * 2016-09-05
	 * @var string
	 */
	private static $BASE = 'base';
	/**
	 * 2016-09-05
	 * @var string
	 */
	private static $ORDER = 'order';
}