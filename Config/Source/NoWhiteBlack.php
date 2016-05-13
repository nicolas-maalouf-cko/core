<?php
namespace Df\Config\Source;
class NoWhiteBlack extends \Df\Config\SourceT {
	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Config\Source::map()
	 * @used-by \Df\Config\Source::toOptionArray()
	 * @return array(string => string)
	 */
	protected function map() {return [0 => 'No'] + $this->titles();}

	/**
	 * 2016-05-13
	 * @used-by \Df\Config\Source\NoWhiteBlack::map()
	 * @return string[]
	 */
	protected function titles() {return [self::$W => 'Whitelist', self::$B => 'Blacklist'];}

	/** @var string */
	protected static $B = 'blacklist';
	/** @var string */
	protected static $W = 'whitelist';

	/**
	 * 2016-05-13
	 * @used-by \Df\Payment\Method::canUseForCountry()
	 * @used-by \Df\Core\Settings::nwb()
	 * @param string|bool $listType
	 * @param string $element
	 * @param string[] $set
	 * @return bool
	 */
	public static function is($listType, $element, array $set) {
		return !$listType || (self::$B === $listType xor in_array($element, $set));
	}
}