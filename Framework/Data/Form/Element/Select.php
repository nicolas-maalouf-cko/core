<?php
namespace Df\Framework\Data\Form\Element;
use Df\Framework\Data\Form\ElementI;
use Magento\Framework\Data\Form\Element\Select as _Select;
abstract class Select extends _Select implements ElementI {
	/**
	 * 2015-11-28
	 * @override
	 * @see \Df\Framework\Data\Form\ElementI::onFormInitialized()
	 * @used-by \Df\Framework\Data\Form\Element\AbstractElementPlugin::afterSetForm()
	 * @return void
	 */
	abstract public function onFormInitialized();
}