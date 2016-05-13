<?php
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order as O;
/**
 * @param string $code
 * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
 */
function df_customer_attribute($code) {return df_eav_config()->getAttribute(df_eav_customer(), $code);}

/**
 * 2016-04-05
 * How to get a customer by his ID? https://mage2.pro/t/1136
 * How to get a customer by his ID with the @uses \Magento\Customer\Model\CustomerRegistry::retrieve()?
 * https://mage2.pro/t/1137
 * How to get a customer by his ID with the @see \Magento\Customer\Api\CustomerRepositoryInterface::getById()?
 * https://mage2.pro/t/1138
 * @param string $id
 * @return Customer
 * @throws NoSuchEntityException
 */
function df_customer_get($id) {return df_customer_registry()->retrieve($id);}

/** @return GroupManagementInterface|GroupManagement */
function df_customer_group_m() {return df_o(GroupManagementInterface::class);}

/**
 * 2016-03-15
 * How to programmatically check whether a customer is new or returning? https://mage2.pro/t/1617
 * @param int|null $id
 * @return bool
 */
function df_customer_is_new($id) {
	/** @var array(int => bool) $cache */
	static $cache;
	if (!isset($cache[$id])) {
		/** @var bool $result */
		$result = !$id;
		if ($id) {
			/** @var \Magento\Framework\DB\Select $select */
			$select = df_select()->from(df_table('sales_order'), 'COUNT(*)')
				->where('? = customer_id', $id)
				->where('state IN (?)', [O::STATE_COMPLETE, O::STATE_PROCESSING])
			;
			$result = !df_conn()->fetchOne($select);
		}
		$cache[$id] = $result;
	}
	return $cache[$id];
}

/**
 * 2015-11-09
 * Сегодня заметил странную ситуацию, что метод @uses \Magento\Customer\Model\Session::isLoggedIn()
 * для авторизованных посетителей стал почему-то возвращать false
 * в контексте вызова из @used-by \Dfe\Facebook\Block\Login::toHtml().
 * Также заметил, что стандартный блок авторизации в шапке страницы
 * определяет авторизованность посетителя совсем по-другому алгоритму:
 * @see \Magento\Customer\Block\Account\AuthorizationLink::isLoggedIn()
 * Вот именно этот алгоритм мы сейчас и задействуем.
 * @return bool
 */
function df_customer_logged_in() {
	return df_session_customer()->isLoggedIn() || df_customer_logged_in_2();
}

/**
 * 2015-11-09
 * Этот способ определения авторизованности посетителя
 * использует стандартный блок авторизации в шапке страницы:
 * @see \Magento\Customer\Block\Account\AuthorizationLink::isLoggedIn()
 * @return bool
 */
function df_customer_logged_in_2() {
	/** @var \Magento\Framework\App\Http\Context $context */
	$context = df_o(\Magento\Framework\App\Http\Context::class);
	return $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
}

/**
 * 2016-04-05
 * @return CustomerRegistry
 */
function df_customer_registry() {return df_o(CustomerRegistry::class);}

/**
 * 2016-04-05
 * @return CustomerRepositoryInterface|CustomerRepository
 */
function df_customer_repository() {return df_o(CustomerRepositoryInterface::class);}

/**
 * @param Customer $customer
 * @return void
 */
function df_customer_save(Customer $customer) {df_customer_repository()->save($customer->getDataModel());}

/**
 * @param string $code
 * @return bool
 */
function df_is_customer_attribute_required($code) {return df_customer_attribute($code)->getIsRequired();}

/** @return \Magento\Customer\Model\Session */
function df_session_customer() {return df_o(\Magento\Customer\Model\Session::class);}