<?php
// 2016-08-29
namespace Df\Payment\R;
/** @method Method m() */
class BlockInfo extends \Df\Payment\Block\Info {
	/**
	 * 2016-07-18
	 * @param string|null $key [optional]
	 * @return Response|string|null
	 */
	protected function responseF($key = null) {return $this->m()->responseF($key);}

	/**
	 * 2016-07-18
	 * @param string|null $key [optional]
	 * @return Response|string|null
	 */
	protected function responseL($key = null) {return $this->m()->responseL($key);}
}