<?php
namespace Df\Customer\Setup;
// 2016-08-21
final class UpgradeSchema extends \Df\Framework\SetupA\Schema {
	/**
	 * 2016-08-21
	 * @override
	 * @see \Df\Framework\SetupA\Schema::_process()
	 * @used-by \Df\Framework\SetupA\Schema::process()
	 * @return void
	 */
	protected function _process() {
		if ($this->v('1.7.2')) {
			$this->c()->addColumn($this->t('customer_entity'), self::F__DF, 'text');
		}
		/**
		 * 2016-08-22
		 * Помимо добавления поля в таблицу «customer_entity» надо ещё добавить атрибут,
		 * иначе данные не будут сохраняться: https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Eav/Model/Entity/AbstractEntity.php#L1262-L1265
		 */
		if ($this->v('1.7.4')) {
			df_eav_setup()->addAttribute('customer', self::F__DF, [
				'type' => 'static',
				'label' => 'Mage2.PRO',
				'input' => 'text',
				'sort_order' => 1000,
				'position' => 1000,
				'visible' => false,
				'system' => false,
				'required' => false
			]);
		}
	}

	/**
	 * 2016-08-21
	 * @used-by \Df\Customer\Setup\UpgradeSchema::_process()
	 * @used-by df_customer_info_add()
	 * @used-by df_customer_info_get()
	 * @var string
	 */
	const F__DF = 'df';
}