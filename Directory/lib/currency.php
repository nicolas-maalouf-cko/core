<?php
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\Data as ConfigData;
use Magento\Framework\App\Config\DataInterface as IConfigData;
use Magento\Framework\App\ScopeInterface as ScopeA;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Sales\Model\Order as O;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
/**
 * 2015-12-28
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return string[]
 */
function df_currencies_codes_allowed($store = null) {
	return df_store($store)->getAvailableCurrencyCodes(true);
}

/**
 * 2015-12-28
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return array(string => string)
 */
function df_currencies_ctn($store = null) {return dfcf(function($store = null) {
	$store = df_store($store);
	/** @var Currency $currency */
	$currency = df_o(Currency::class);
	/** @var string[] $codes */
	$codes = df_currencies_codes_allowed($store);
	// 2016-02-17
	// $rates ниже не содержит базовую валюту.
	/** @var string $baseCode */
	$baseCode = $store->getBaseCurrency()->getCode();
	/** @var array(string => float) $rates */
	$rates = $currency->getCurrencyRates($store->getBaseCurrency(), $codes);
	/** @var array(string => string) $result */
	$result = [];
	foreach ($codes as $code) {
		/** @var string $code */
		if ($baseCode === $code || isset($rates[$code])) {
			$result[$code] = df_currency_name($code);
		}
	}
	return $result;
}, func_get_args());}

/**
 * 2015-12-28
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return array(array(string => string))
 */
function df_currencies_options($store = null) {return dfcf(function($store = null) {return
	df_map_to_options(df_currencies_ctn($store))
;}, func_get_args());}

/**
 * 2016-07-04
 * «How to load a currency by its ISO code?» https://mage2.pro/t/1840
 * @param Currency|string|null $currency [optional]
 * @return Currency
 */
function df_currency($currency = null) {
	/** @var Currency $result */
	if (!$currency) {
		$result = df_currency_base();
	}
	else if ($currency instanceof Currency) {
		$result = $currency;
	}
	else {
		/** @var array(string => Currency) $cache */
		static $cache;
		if (!isset($cache[$currency])) {
			$cache[$currency] = df_create(Currency::class)->load($currency);
		}
		$result = $cache[$currency];
	}
	return $result;
}

/**
 * 2016-07-04
 * «How to programmatically get the base currency's ISO code for a store?» https://mage2.pro/t/1841
 * @param null|string|int|ScopeA|Store|ConfigData|IConfigData|O $scope [optional]
 * @return Currency
 */
function df_currency_base($scope = null) {
	if ($scope instanceof O) {
		$scope = $scope->getStore();
	}
	/** @var string $code */
	$code = df_cfg(Currency::XML_PATH_CURRENCY_BASE, $scope);
	df_assert_string_not_empty($code);
	return df_currency($code);
}

/**
 * 2016-09-05
 * @param null|string|int|ScopeA|Store|ConfigData|IConfigData $scope [optional]
 * @return string
 */
function df_currency_base_c($scope = null) {return df_currency_base($scope)->getCode();}

/**
 * 2016-07-04
 * @param Currency|string|null $currency [optional]
 * @return string
 */
function df_currency_code($currency = null) {return df_currency($currency)->getCode();}

/**
 * 2016-07-04
 * «How to programmatically convert a money amount from a currency to another one?» https://mage2.pro/t/1842
 * 2016-09-05
 * Обратите внимание, что перевод из одной валюты в другую
 * надо осуществлять только в направлении 'базовая валюта' => 'второстепенная валюта',
 * но не наоборот
 * (Magento не умеет выполнять первод 'второстепенная валюта' => 'базовая валюта'
 * даже при наличии курса 'базовая валюта' => 'второстепенная валюта',
 * и возбуждает исключительную ситуацию).
 *
 * Курс валюты сау на себя в системе всегда есть:
 * @see \Magento\Directory\Model\ResourceModel\Currency::getRate()
 * https://github.com/magento/magento2/blob/2.1.1/app/code/Magento/Directory/Model/ResourceModel/Currency.php#L56-L58
 *
 * @uses \Magento\Directory\Model\Currency::convert() прекрасно понимает нулевой $to:
 * https://github.com/magento/magento2/blob/2.1.1/app/code/Magento/Directory/Model/Currency.php#L216-L217
 *
 * @param float $amount
 * @param Currency|string|null $from [optional]
 * @param Currency|string|null $to [optional]
 * @param null|string|int|ScopeA|Store|ConfigData|IConfigData $scope [optional]
 * @return float
 */
function df_currency_convert($amount, $from = null, $to = null, $scope = null) {return
	df_currency_convert_from_base(df_currency_convert_to_base($amount, $from, $scope), $to, $scope)
;}

/**
 * 2016-09-05
 * @param float $amount
 * @param Currency|string|null $to
 * @param null|string|int|ScopeA|Store|ConfigData|IConfigData $scope [optional]
 * @return float
 */
function df_currency_convert_from_base($amount, $to, $scope = null) {return
	df_currency_base($scope)->convert($amount, $to)
;}

/**
 * 2016-09-05
 * @param float $amount
 * @param Currency|string|null $from
 * @param null|string|int|ScopeA|Store|ConfigData|IConfigData $scope [optional]
 * @return float
 */
function df_currency_convert_to_base($amount, $from, $scope = null) {return
	$amount / df_currency_base($scope)->convert(1, $from)
;}

/**
 * 2016-08-08
 * http://magento.stackexchange.com/a/108013
 * В отличие от @see df_currency_base() здесь мы вынуждены использовать не $scope, а $store,
 * потому что учётную валюту можно просто считать из настроек,
 * а текущая валюта может меняться динамически (в том числе посетителем магазина и сессией).
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return Currency
 */
function df_currency_current($store = null) {return df_store($store)->getCurrentCurrency();}

/**
 * 2016-09-05
 * В отличие от @see df_currency_base_с() здесь мы вынуждены использовать не $scope, а $store,
 * потому что учётную валюту можно просто считать из настроек,
 * а текущая валюта может меняться динамически (в том числе посетителем магазина и сессией).
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return string
 */
function df_currency_current_c($store = null) {return df_currency_current($store)->getCode();}

/**
 * 2016-06-30
 * «How to programmatically check whether a currency is allowed
 * and has an exchange rate to the base currency?» https://mage2.pro/t/1832
 * @param string $iso3
 * @param int|string|null|bool|StoreInterface $store [optional]
 * @return string[]
 */
function df_currency_has_rate($iso3, $store = null) {return !!dfa(df_currencies_ctn($store), $iso3);}

/**
 * 2016-06-30
 * «How to programmatically get a currency's name by its ISO code?» https://mage2.pro/t/1833
 * @param string|Currency $currency
 * @return string
 */
function df_currency_name($currency = null) {
	/** @var \ResourceBundle $rb */
	static $rb;
	if (!isset($rb))  {
		$rb = (new CurrencyBundle())->get(df_locale())['Currencies'];
	}
	/** @var string $code */
	$code = is_string($currency) ? $currency : df_currency_code($currency);
	return $rb[$code][1] ?: $code;
}

/**
 * 2016-08-08
 * @return float
 */
function df_currency_rate_to_current() {return df_currency_base()->getRate(df_currency_current());}

