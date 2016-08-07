<?php
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
/**
 * 2015-12-25
 * Этот загадочный метод призван заменить код вида:
 * is_array($arguments) ? $arguments : func_get_args()
 * Теперь можно писать так: df_args(func_get_args())
 * @param mixed[] $a
 * @return mixed[]
 */
function df_args(array $a) {return !$a || !is_array($a[0]) ? $a : $a[0];}

/**
 * 2015-12-30
 * Унифицирует вызов калбэков:
 * позволяет в качестве $method передавать как строковое название метода,
 * так и анонимную функцию, которая в качестве аргумента получит $object.
 * https://3v4l.org/pPGtA
 * @param object|mixed $object
 * @param string|callable|\Closure $method
 * @param mixed[] $params [optional]
 * @return mixed
 */
function df_call($object, $method, $params = []) {
	/** @var mixed $result */
	if (!is_string($method)) {
		// $method — инлайновая функция
		$result = call_user_func_array($method, array_merge([$object], $params));
	}
	else {
		/** @var bool $functionExists */
		$functionExists = function_exists($method);
		/** @var bool $methodExists */
		$methodExists = is_callable([$object, $method]);
		/** @var mixed $callable */
		if ($functionExists && !$methodExists) {
			$callable = $method;
			$params = array_merge([$object], $params);
		}
		else if ($methodExists && !$functionExists) {
			$callable = [$object, $method];
		}
		else if (!$functionExists) {
			df_error("Unable to call «{$method}».");
		}
		else {
			df_error("An ambiguous name: «{$method}».");
		}
		$result = call_user_func_array($callable, $params);
	}
	return $result;
}

/**
 * 2016-01-14
 * @param callable $callback
 * @param mixed[]|mixed[][] $arguments
 * @param mixed|mixed[] $paramsToAppend [optional]
 * @param mixed|mixed[] $paramsToPrepend [optional]
 * @param int $keyPosition [optional]
 * @return mixed|mixed[]
 */
function df_call_a(
	$callback, array $arguments, $paramsToAppend = [], $paramsToPrepend = [], $keyPosition = 0
) {
	if (1 === count($arguments)) {
		$arguments = $arguments[0];
	}
	return
		!is_array($arguments)
		? call_user_func_array($callback, array_merge($paramsToPrepend, [$arguments], $paramsToAppend))
		: df_map($callback, $arguments, $paramsToAppend, $paramsToPrepend, $keyPosition)
	;
}

/**
 * 2016-02-09
 * https://3v4l.org/iUQGl
	 function a($b) {return is_callable($b);}
	 a(function() {return 0;}); возвращает true
 * https://3v4l.org/MfmCj
 	is_callable('intval') возвращает true
 * @param mixed|callable $value
 * @param mixed[] $params [optional]
 * @return mixed
 */
function df_call_if($value, ...$params) {
	return
		is_callable($value) && !is_string($value) && !is_array($value)
		? call_user_func_array($value, $params)
		: $value
	;
}

/**
 * @param float|int $value
 * @return int
 */
function df_ceil($value) {return (int)ceil($value);}

/**
 * 2015-08-16
 * https://mage2.ru/t/95
 * https://mage2.pro/t/60
 * @param string $eventName
 * @param array(string => mixed) $data
 * @return void
 */
function df_dispatch($eventName, array $data = []) {
	/** @var \Magento\Framework\Event\ManagerInterface|\Magento\Framework\Event\Manager $manager */
	$manager = df_o(\Magento\Framework\Event\ManagerInterface::class);
	$manager->dispatch($eventName, $data);
}

/**
 * @param mixed $value
 * @return bool
 */
function df_empty_string($value) {return '' === $value;}

/**
 * @param mixed $value
 * @return mixed
 */
function df_empty_to_null($value) {return $value ? $value : null;}

/**
 * @param float|int $value
 * @return int
 */
function df_floor($value) {return (int)floor($value);}

/**
 * @see df_sc()
 * @param string $resultClass
 * @param string $expectedClass
 * @param array(string => mixed) $params [optional]
 * @return DataObject|object
 */
function df_ic($resultClass, $expectedClass, array $params = []) {
	/** @var DataObject|object $result */
	$result = df_create($resultClass, $params);
	df_assert_is($expectedClass, $result);
	return $result;
}

/**
 * Осуществляет ленивое ветвление.
 * @param bool $condition
 * @param mixed|callable $onTrue
 * @param mixed|null|callable $onFalse [optional]
 * @return mixed
 */
function df_if($condition, $onTrue, $onFalse = null) {
	return $condition ? df_call_if($onTrue) : df_call_if($onFalse);
}

/**
 * 2016-02-09
 * Осуществляет ленивое ветвление только для первой ветки.
 * @param bool $condition
 * @param mixed|callable $onTrue
 * @param mixed|null $onFalse [optional]
 * @return mixed
 */
function df_if1($condition, $onTrue, $onFalse = null) {
	return $condition ? df_call_if($onTrue) : $onFalse;
}

/**
 * 2016-02-09
 * Осуществляет ленивое ветвление только для второй ветки.
 * @param bool $condition
 * @param mixed $onTrue
 * @param mixed|null|callable $onFalse [optional]
 * @return mixed
 */
function df_if2($condition, $onTrue, $onFalse = null) {
	return $condition ? $onTrue : df_call_if($onFalse);
}

/**
 * 2016-03-26
 * @param string|AbstractModel $model
 * Идентификатор необязательно является целым числом,
 * потому что объект может загружаться по нестандартному ключу
 * (с указанием этого ключа параметром $field).
 * Так же, и первичный ключ может не быть целым числом (например, при загрузке валют).
 * @param string|int $id
 * @param bool $throwOnAbsence [optional]
 * @param string|null $field [optional]
 * @return AbstractModel|null
 */
function df_load($model, $id, $throwOnAbsence = true, $field = null) {
	df_assert($id);
	if (!is_null($field)) {
		df_param_string($field, 3);
	}
	/** @var AbstractModel|null $result */
	$result = is_string($model) ? df_om()->create($model) : $model;
	df_assert($result instanceof AbstractModel);
	$result->load($id, $field);
	if (!$result->getId()) {
		if (!$throwOnAbsence) {
			$result = null;
		}
		else {
			df_error('A model of class «%s» with ID «%s» is absent.', get_class($result), $id);
		}
	}
	return $result;
}

// Глобальные константы появились в PHP 5.3.
// http://www.codingforums.com/php/303927-unexpected-t_const-php-version-5-2-17-a.html#post1363452
const RM_NULL = 'rm-null';

/**
 * @param mixed|string $value
 * @return mixed|null
 */
function df_n_get($value) {return (RM_NULL === $value) ? null : $value;}
/**
 * @param mixed|null $value
 * @return mixed|string
 */
function df_n_set($value) {return is_null($value) ? RM_NULL : $value;}

/**
 * @used-by \Df\Core\Model\Format\Html\Tag::getOpenTagWithAttributesAsText()
 * @param mixed $argument
 * @return mixed
 */
function df_nop($argument) {return $argument;}

/**
 * @param mixed|null $value
 * @param bool $skipEmptyCheck [optional]
 * @return mixed[]
 */
function df_nta($value, $skipEmptyCheck = false) {
	if (!is_array($value)) {
		if (!$skipEmptyCheck) {
			df_assert(empty($value));
		}
		$value = [];
	}
	return $value;
}

/**
 * @param mixed|null $value
 * @return mixed
 */
function df_nts($value) {return !is_null($value) ? $value : '';}

/**
 * 2016-08-04
 * @param mixed $value
 * @return bool
 */
function df_null_or_empty_string($value) {return is_null($value) || '' === $value;}

/**
 * @param object|DataObject $entity
 * @param string $key
 * @param mixed $default
 * @return mixed|null
 */
function df_ok($entity, $key, $default = null) {
	/**
	 * Раньше функция @see dfa() была универсальной:
	 * она принимала в качестве аргумента $entity как массивы, так и объекты.
	 * В 99.9% случаев в качестве параметра передавался массив.
	 * Поэтому ради ускорения работы системы
	 * вынес обработку объектов в отдельную функцию @see df_ok()
	 */
	/** @var mixed $result */
	if (!is_object($entity)) {
		df_error('Попытка вызова df_ok для переменной типа «%s».', gettype($entity));
	}
	/** @var mixed|null $result */
	$result = null;
	if ($entity instanceof DataObject) {
		$result = $entity->getData($key);
	}
	if (is_null($result)) {
		/**
		 * Например, @see stdClass.
		 * Используется, например, методом
		 * @used-by Df_Qiwi_Model_Action_Confirm::updateBill()
		 */
		$result = isset($entity->{$key}) ? $entity->{$key} : $default;
	}
	return $result;
}

/** @return \Df\Core\Helper\Output */
function df_output() {return \Df\Core\Helper\Output::s();}

/**
 * @param float|int $value
 * @return int
 */
function df_round($value) {return (int)round($value);}

/**
 * 2015-03-23
 * @see df_ic()
 * @param string $resultClass
 * @param string $expectedClass
 * @param array(string => mixed) $params [optional]
 * @param string $cacheKeySuffix [optional]
 * @return DataObject|object
 */
function df_sc($resultClass, $expectedClass, array $params = [], $cacheKeySuffix = '') {
	/** @var array(string => object) $cache */
	static $cache;
	/** @var string $key */
	$key = $resultClass . $cacheKeySuffix;
	if (!isset($cache[$key])) {
		$cache[$key] = df_ic($resultClass, $expectedClass, $params);
	}
	return $cache[$key];
}

/**
 * 2015-12-06
 * @param string|object $id
 * @param callable $job
 * @param float $interval [optional]
 * @return mixed
 */
function df_sync($id, $job, $interval = 0.1) {
	return \Df\Core\Sync::execute(is_object($id) ? get_class($id) : $id, $job, $interval);
}