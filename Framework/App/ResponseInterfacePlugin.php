<?php
namespace Df\Framework\App;
use \Magento\Framework\App\ResponseInterface;
class ResponseInterfacePlugin {
	/**
	 * 2015-12-07
	 * Цель плагина — поддержка события «df_controller_front_send_response_after».
	 * https://mage2.pro/t/288
	 * Incosistency: the event «controller_front_send_response_after» is removed from Magento 2,
	 * but the event «controller_front_send_response_before» is left.
	 * https://mage2.pro/t/287
	 * @see \Magento\Persistent\Observer\SynchronizePersistentInfoObserver
	 * is subscribed on the absent event «controller_front_send_response_after»,
	 * and so it is never called.
	 * @see ResponseInterface::sendResponse()
	 * @param ResponseInterface $subject
	 * @param int|void $result
	 * @return int|void
	 */
	public function afterSendResponse(ResponseInterface $subject, $result) {
		df_dispatch('df_controller_front_send_response_after');
		return $result;
	}
}
