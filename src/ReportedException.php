<?php

namespace DNA\DNARaygun;

/**
 * Deal with PHP's limited support for custom exception handlers.
 * We can't use a real exception as we can't override final methods and there's
 * no other way to set the stack trace.
 */
class ReportedException {
	protected $data;

	function __construct($data) {
		$this->data = $data;
	}

	function getMessage() {
		return $this->data['errstr'];
	}
	function getCode() {
		return $this->data['errno'];
	}
	function getTrace() {
		return $this->data['errcontext'];
	}
	function getFile() {
		return $this->data['errfile'];
	}
	function getLine() {
		return $this->data['errline'];
	}
}
