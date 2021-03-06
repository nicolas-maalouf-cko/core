<?php
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface as IConfig;
use Magento\Framework\App\Config\Data as ConfigData;
use Magento\Framework\App\Config\DataInterface as IConfigData;
use Magento\Framework\App\Config\ScopePool;
use Magento\Framework\App\ScopeInterface as ScopeA;
use Magento\Config\Model\ResourceModel\Config as RConfig;
use Magento\Store\Model\ScopeInterface as ScopeS;
use Magento\Store\Model\Store;
/**
 * @uses \Magento\Framework\App\Config\Data::getValue()
 * https://github.com/magento/magento2/blob/2335247d4ae2dc1e0728ee73022b0a244ccd7f4c/lib/internal/Magento/Framework/App/Config/Data.php#L47-L62
 *
 * 2015-12-26
 * https://mage2.pro/t/357
 * «The @uses \Magento\Framework\App\Config::getValue() method
 * has a wrong PHPDoc type for the $scopeCode parameter».
 *
 * Метод возвращает null или $default, если данные отсутствуют:
 * @see \Magento\Framework\App\Config\Data::getValue()
 * https://github.com/magento/magento2/blob/6ce74b2/lib/internal/Magento/Framework/App/Config/Data.php#L47-L62
 *
 * 2015-10-09
 * https://mage2.pro/t/128
 * https://github.com/magento/magento2/issues/2064
 *
 * @param string $key
 * @param null|string|int|ScopeA|Store|IConfigData|ConfigData $scope [optional]
 * @param mixed|callable $default [optional]
 * @return array|string|null|mixed
 */
function df_cfg($key, $scope = null, $default = null) {
	/** @var array|string|null|mixed $result */
	$result =
		$scope instanceof IConfigData
		? $scope->getValue($key)
		: df_cfg_m()->getValue($key, ScopeS::SCOPE_STORE, $scope)
	;
	return df_if(is_null($result) || '' === $result, $default, $result);
}

/**
 * 2016-08-03
 * @see df_cfg_save()
 * @param string $path		E.g.: «web/unsecure/base_url»
 * @param string $scope		E.g.: «default»
 * @param int $scopeId		E.g.: «0»
 * @return void
 */
function df_cfg_delete($path, $scope, $scopeId) {df_cfg_r()->deleteConfig($path, $scope, $scopeId);}

/**
 * 2016-02-09
 * https://mage2.pro/t/639
 * The default implementation of the @see \Magento\Framework\App\Config\ScopeConfigInterface
 * is @see \Magento\Framework\App\Config
 * @return IConfig|Config
 */
function df_cfg_m() {return df_o(IConfig::class);}

/**
 * 2016-08-03
 * @return RConfig
 */
function df_cfg_r() {return df_o(RConfig::class);}

/**
 * 2016-08-03
 * How to save a config option programmatically? https://mage2.pro/t/289
 * @see df_cfg_delete()
 * @param string $path		E.g.: «web/unsecure/base_url»
 * @param string $value
 * @param string $scope		E.g.: «default»
 * @param int $scopeId		E.g.: «0»
 * @return void
 */
function df_cfg_save($path, $value, $scope, $scopeId) {
	df_cfg_r()->saveConfig($path, $value, $scope, $scopeId);
}


