<?php

require_once 'Zend/Log/Writer/Abstract.php';

class DnaRaygunLogWriter extends RaygunLogWriter {

	private $ignored_errors = array(
		'Broken jobs were found in the job queue'
	);

	private $ignored_exceptions = array();

	function exception_handler($exception) {
		if (Director::isDev()) {
			return false;
		}

		if ($this->is_message_blocked($exception->getMessage(), 'exceptions')) {
			return false;
		}

		$appName = Config::inst()->get('RaygunLogWriter', 'app_name');
		$message = array();
		$message['errstr'] = $exception->getMessage();
		$message['errno'] = $exception->getCode();
		$message['errcontext'] = $exception->getTrace();
		$message['errfile'] = $exception->getFile();
		$message['errline'] = $exception->getLine();

		$message['errstr'] = sprintf("[%s] %s",
			$appName,
			$message['errstr']
		);

		$ex = new ReportedException($message);

		parent::exception_handler($ex);
	}

	function error_handler($errno, $errstr, $errfile, $errline, $tags) {
		if (Director::isDev()) {
			return false;
		}

		if ($this->is_message_blocked($exception->getMessage(), 'errors')) {
			return false;
		}

		$appName = Config::inst()->get('RaygunLogWriter', 'app_name');
		$errstr = sprintf("[%s] %s",
			$appName,
			$errstr
		);

		parent::error_handler($errno, $errstr, $errfile, $errline, $tags);
	}

	function is_message_blocked($message, $type = 'exceptions') {
		$blocked = false;
		$collection = $this->get_ignored_messages($type);
		foreach($collection as $blocked_message) {
			if (strpos($message, $blocked_message) !== false ) {
				$blocked = true;
				break;
			}
		}

		return $blocked;
	}

	function get_ignored_messages($type) {
		$config = Config::inst()->get('DnaRaygunLogWriter','ignored_'.$type );
		$collections = array();
		$ignored_type = 'ignored_'.$type;

		if ($config && is_array($config)) {
			if (is_array($config)) {
				 $collections = array_merge($config, $this->$ignored_type);
			} else if (is_string($config)) {
				$collection = $this->$ignored_type;
				array_push($collection, $config);
			}
		}

		return $collections;
	}
}

/**
 * Tracks SS error logs
 */
class DnaRaygunLogWriter_Zend extends Zend_Log_Writer_Abstract {

	public static function factory($config) {
		return new DnaRaygunLogWriter_Zend();
	}

	public function _write($event) {
		$raygunAPIKey = Config::inst()->get('RaygunLogWriter', 'api_key');
		if(empty($raygunAPIKey) && defined('SS_RAYGUN_APP_KEY')) {
			$raygunAPIKey = SS_RAYGUN_APP_KEY;
		}

		$raygun = new DnaRaygunLogWriter($raygunAPIKey);

		$raygun->error_handler(
			$errno = $event['message']['errno'],
			$errstr = $event['message']['errstr'],
			$errfile = $event['message']['errfile'],
			$errline = $event['message']['errline'],
			$errcontext = $event['message']['errcontext']
		);
	}

}
