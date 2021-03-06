<?php
namespace Df\Core;
class Visitor extends O {
	/**
	 * На английском языке. Например: «Moscow».
	 * @return string|null
	 */
	public function city() {return $this->r('city');}

	/**
	 * На английском языке. Например: «Russia».
	 * @return string
	 */
	public function countryName() {return $this->r('country_name');}

	/** @return string|null */
	public function iso2() {return $this->r('country_code');}

	/**
	 * Например: «55.752».
	 * @return string|null
	 */
	public function latitude() {return $this->r('latitude');}

	/**
	 * Например: «37.616».
	 * @return string|null
	 */
	public function longitude() {return $this->r('longitude');}

	/**
	 * Например: «101194».
	 * @return string|null
	 */
	public function postCode() {return $this->r('zip_code');}

	/**
	 * Например: «MOW».
	 * @return string|null
	 */
	public function regionCode() {return $this->r('region_code');}

	/**
	 * На английском языке. Например: «Moscow».
	 * @return string|null
	 */
	public function regionName() {return $this->r('region_name');}

	/**
	 * Например: «Europe/Moscow».
	 * @return string|null
	 */
	public function timeZone() {return $this->r('time_zone');}

	/**
	 * @param string $key
	 * @return string|null
	 */
	private function r($key) {return dfa($this->responseA(), $key);}

	/**
	 * 2016-05-31
	 * Сегодня заметил, что при запросе из PHP freegeoip.net перестал возвращать мне значение,
	 * а при запросе из браузера — по прежнему возвращает.
	 * Видимо, freegeoip.net забанил мой User-Agent?
	 * В любом случае, нельзя полагаться, что freegeoip.net вернёт непустой ответ.
	 *
	 * Стандартное время ожидание ответа сервера задаётся опцией default_socket_timeout:
	 * http://php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
	 * Её значение по-умолчанию равно 60 секундам.
	 * Конечно, при оформлении заказа негоже заставлять покупателя ждать 60 секунд
	 * только ради узнавания его страны вызовом @see df_visitor(),
	 * поэтому уменьшил время ожидания ответа до 5 секунд.
	 *
	 * @return array(string => mixed)
	 */
	private function responseA() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_http_json(
				'https://freegeoip.net/json/' . $this[self::$P__IP], [], 5
			);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-05-20
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__IP, DF_V_STRING_NE);
	}

	/** @var string */
	private static $P__IP = 'ip';

	/**
	 * 2016-05-20
	 * @param string|null $ip [optional]
	 * @return $this
	 */
	public static function sp($ip = null) {return dfcf(function($ip = null) {return
		new self([self::$P__IP => $ip ?: df_visitor_ip()])
	;}, func_get_args());}
}