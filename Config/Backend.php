<?php
namespace Df\Config;
use Magento\Config\Model\Config\Structure\AbstractElement as ConfigElement;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Config\Model\Config\Structure\ElementInterface as IConfigElement;
use Magento\Framework\Phrase;
/**
 * @method mixed|null getValue()
 * @method $this setStore($value)
 * @method $this setWebsite($value)
 *
 * 2016-08-03
 * Начиная с Magento 2.1.0 backend model создаётся только если данные присутствуют в базе данных
 * для конкретной области действия настроек (scope и scopeId).
 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Config/Block/System/Config/Form.php#L309-L327
 * Если данные отсутстствуют в БД для конкретной области действия настроек,
 * то backend model вообще не создаётся,
 * однако данные всё равно извлекаются из БД из общей области действия настроек:
 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Config/Block/System/Config/Form.php#L323-L327
 * Видимо, такое поведение дефектно: данные могут попасть в форму
 * в обход обработки и валидации их в backend model.
 *
 * Ранее (до версии 2.1.0) backend model создавалась в любом случае:
 * такое поведение я считаю более верным:
 * https://github.com/magento/magento2/blob/2.0.8/app/code/Magento/Config/Block/System/Config/Form.php#L330-L342
 */
class Backend extends \Magento\Framework\App\Config\Value {
	/**
	 * 2015-12-07
	 * Конечно, хотелось бы задействовать стандартные методы
	 * @see \Magento\Framework\Model\AbstractModel::beforeSave() и
	 * @see \Magento\Framework\Model\AbstractModel::afterSave()
	 * или же
	 * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::_beforeSave() и
	 * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::_afterSave()
	 * или же
	 * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::_serializeFields() и
	 * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::unserializeFields()
	 * однако меня смутило, что в случае исключительной ситуации
	 * модель может остаться в несогласованном состоянии:
	 * https://mage2.pro/t/283
	 * https://mage2.pro/t/284
	 * Поэтому разработал свои аналогичные методы.
	 *
	 * @override
	 * @see \Magento\Framework\App\Config\Value::save()
	 * @return $this
	 * @throws \Exception
	 */
	public function save() {
		try {
			$this->dfSaveBefore();
			parent::save();
		}
		catch (\Exception $e) {
			df_log($e);
			throw df_le($e);
		}
		finally {
			$this->dfSaveAfter();
		}
		return $this;
	}

	/**
	 * 2016-08-03
	 * @override
	 * @see \Magento\Framework\Model\AbstractModel::_afterLoad()
	 * @used-by \Magento\Framework\Model\AbstractModel::load()
	 * @return void
	 */
	protected function _afterLoad() {
		parent::_afterLoad();
		self::setProcessed($this->getPath());
	}

	/**
	 * 2016-08-02
	 * @return string
	 */
	protected function label() {
		if (!isset($this->{__METHOD__})) {
			/** @var string[] $pathA */
			$pathA = explode('/', $this->getPath());
			/** @var Phrase[] $resultA */
			$resultA = [];
			/** @var IConfigElement|ConfigElement|Section|null $e */
			while ($pathA && ($e = df_config_structure()->getElementByPathParts($pathA))) {
				$resultA[]= $e->getLabel();
				array_pop($pathA);
			}
			$resultA[]= df_config_tab_label($e);
			$resultA = array_reverse($resultA);
			$resultA[]= $this->labelShort();
			$this->{__METHOD__} = implode(' → ', df_quote_russian($resultA));
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-08-02
	 * @return Phrase
	 */
	protected function labelShort() {return __($this->fc('label'));}

	/**
	 * 2015-12-07
	 * @used-by \Df\Config\Backend::save()
	 * @return void
	 */
	protected function dfSaveAfter() {}

	/**
	 * 2015-12-07
	 * @used-by \Df\Config\Backend::save()
	 * @return void
	 */
	protected function dfSaveBefore() {}

	/**
	 * 2016-07-31
	 * @see \Df\Config\Backend::isSaving()
	 * @param string|null $key [optional]
	 * @param string|null|callable $default [optional]
	 * @return string|null|array(string => mixed)
	 */
	protected function fc($key = null, $default = null) {
		/** @var string|array(string => mixed) $result */
		$result = $this->field()->getData();
		return is_null($key) ? $result : dfa($result, $key, $default);
	}

	/**
	 * 2016-08-03
	 * @return Field
	 */
	protected function field() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_config_field($this->getPath());
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-31
	 * @see \Df\Config\Backend::fc()
	 * @return bool
	 */
	protected function isSaving() {return isset($this->_data['field_config']);}

	/**
	 * 2015-12-07
	 * 2016-01-01
	 * Сегодня заметил, что Magento 2, в отличие от Magento 1.x,
	 * допускает иерархическую вложенность групп настроек большую, чем 3, например:
	 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Cron/etc/adminhtml/system.xml#L14
	 * В Magento 1.x вложенность всегда такова: section / group / field.
	 * В Magento 2 вложенность может быть такой: section / group / group / field.
	 * @return array(string => mixed)
	 */
	protected function value() {return dfc($this, function() {
		/** @var string[] $pathA */
		$pathA = array_slice(df_explode_xpath($this->getPath()), 1);
		/** @var string $fieldName */
		$fieldName = array_pop($pathA);
		/** @var string $path */
		$path = 'groups/' . implode('/groups/', $pathA) . '/fields/' . $fieldName;
		/** @var array(string => mixed) $result */
		/**
		 * 2016-09-02
		 * При сохранении настроек вне области действия по умолчанию
		 * в результат попадает ключ «inherit». Удаляем его.
		 * https://code.dmitry-fedyuk.com/m2e/allpay/issues/24
		 */
		$result = dfa_unset(dfa_deep($this->_data, $path), 'inherit');
		df_result_array($result);
		return $result;
	});}

	/**
	 * 2016-08-03
	 * @used-by \Df\Framework\Plugin\Data\Form\Element\Fieldset::beforeAddField()
	 * @param string $path
	 * @return bool
	 */
	public static function isProcessed($path) {return isset(self::$_processed[$path]);}

	/**
	 * 2016-08-03
	 * @used-by \Df\Config\Backend::_afterLoad()
	 * @used-by \Df\Framework\Plugin\Data\Form\Element\Fieldset::beforeAddField()
	 * @param string $path
	 * @return void
	 */
	public static function setProcessed($path) {self::$_processed[$path] = true;}

	/**
	 * 2016-08-03
	 * @used-by \Df\Config\Backend::isProcessed()
	 * @used-by \Df\Config\Backend::setProcessed()
	 * @var array(string => bool)
	 */
	private static $_processed = [];
}