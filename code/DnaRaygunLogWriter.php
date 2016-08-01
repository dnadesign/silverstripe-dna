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

		if(in_array($exception->getMessage(), $this->ignored_exceptions)) {
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

		if(in_array($errstr, $this->ignored_errors)) {
			return false;
		}

		$appName = Config::inst()->get('RaygunLogWriter', 'app_name');
		$errstr = sprintf("[%s] %s",
			$appName,
			$errstr
		);
		parent::error_handler($errno, $errstr, $errfile, $errline, $tags);
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
