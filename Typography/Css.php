<?php
namespace Df\Typography;
class Css extends \Df\Core\O {
	/**
	 * 2015-12-16
	 * @return string
	 */
	public function render() {
		return df_cc_n(df_map_k($this->_blocks, function($selector, array $rules) {
			/** @var string $selector */
			/** @var string[] $rules */
			/** @var string $rulesS */
			$rulesS = df_tab_multiline(df_cc_n($rules));
			return "{$selector} {\n{$rulesS}\n}";
		}));
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $selector [optional]
	 * @return void
	 */
	public function rule($name, $value, $selector = '') {
		if ('' !== $value && false !== $value) {
			$this->_blocks[$this->prefix() . $selector][]= "{$name}: {$value} !important;";
		}
	}

	/** @return string */
	private function prefix() {return $this[self::$P__PREFIX];}

	/**
	 * 2015-12-16
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__PREFIX, DF_V_STRING, false);
	}

	/** @var array(string => string[]) */
	private $_blocks = [];

	/** @var string */
	private static $P__PREFIX = 'selector';
	/**
	 * 2015-12-21
	 * @param string $prefix [optional]
	 * @return string
	 */
	public static function i($prefix = '') {return new self([self::$P__PREFIX => $prefix]);}
}