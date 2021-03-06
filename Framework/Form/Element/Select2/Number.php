<?php
// 2016-08-10
namespace Df\Framework\Form\Element\Select2;
class Number extends \Df\Framework\Form\Element\Select2 {
	/**
	 * 2016-08-10
	 * @override
	 * @see \Df\Framework\Form\Element\Select2::onFormInitialized()
	 * @return void
	 */
	public function onFormInitialized() {
		parent::onFormInitialized();
		df_fe_init($this, __CLASS__, [], [], 'select2/number');
	}

	/**
	 * 2016-08-10
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
	protected function customCssClass() {return 'df-select2-number';}

	/**
	 * 2016-08-12
	 * @override
	 * @see \Df\Framework\Form\Element\Select2::width()
	 * @used-by \Df\Framework\Form\Element\Select2::onFormInitialized()
	 * @return string
	 */
	protected function width() {return '5em';}
}