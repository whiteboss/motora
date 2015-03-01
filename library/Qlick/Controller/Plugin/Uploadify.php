<?php
/**
* Controller plugin that restarts the session for uploadify.swf calls
*/

class Qlick_Controller_Plugin_Uploadify extends Zend_Controller_Plugin_Abstract
{
	/**
	 * PreDispatch Hook.
	 *
	 * Checks to see if the current request has been made by the uploadify.swf file
	 * if so restart up the php session and continue on
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @return void
	 * @see http://www.uploadify.com/faqs/how-do-i-send-the-session-id-to-the-back-end-script/
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$phpSessId = $request->getParam('session');
		if (!empty($phpSessId) && session_id() != $phpSessId) {
			session_destroy();
			session_id($phpSessId);
			session_start();
		}
	}
}
