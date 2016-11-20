<?php

namespace lola\io\http;

use lola\type\AStateTransform;

use lola\io\http\IHttpDriver;



class HttpReplyTransform
extends AStateTransform
{

	public function __construct() {
		parent::__construct([
			self::STEP_FIRST => [
				'next' => [
					self::STEP_SUCCESS => 'filterHeaders'
				]
			],
			'filterHeaders' => [
				'transform' => 'filterHeaders',
				'next' => [
					self::STEP_SUCCESS => 'sendHeaders'
				]
			],
			'sendHeaders' => [
				'transform' => 'sendHeaders',
				'next' => [
					'cookies' => 'sendCookies',
					'redirect' => 'sendRedirect',
					self::STEP_SUCCESS => 'sendBody'
				]
			],
			'sendCookies' => [
				'transform' => 'sendCookies',
				'next' => [
					'redirect' => 'sendRedirect',
					self::STEP_SUCCESS => 'sendBody',
				]
			],
			'sendRedirect' => [
				'transform' => 'sendRedirect',
				'next' => [
					'body' => 'redirectBody',
					self::STEP_SUCCESS => 'sendBody'
				]
			],
			'redirectBody' => [
				'transform' => 'redirectBody',
				'next' => [
					self::STEP_SUCCESS => 'sendBody'
				]
			],
			'sendBody' => [
				'transform' => 'sendBody',
				'next' => [
					self::STEP_SUCCESS => 'exit'
				]
			],
			'exit' => [
				'transform' => 'exit',
				'next' => [
					self::STEP_SUCCESS => ''
				]
			]
		]);
	}


	public function filterHeadersStep(IHttpDriver& $driver) {
		$reply =& $driver->useReply();

		if ($reply->hasHeader('Content-Length')) $reply->resetHeader('Content-Length');
		if ($reply->hasHeader('Set-Cookie')) $reply->resetHeader('Set-Cookie');
	}

	public function sendHeadersStep(IHttpDriver& $driver) {
		while (ob_get_level() !== 0) ob_end_clean();

		$reply =& $driver->useReply();
		$rules =& $driver->useConfig();
		$resource = $driver->useReplyResource();

		$resource
			->sendHeader($rules->getCodeHeader($reply->getCode()))
			->sendHeader('Content-Type: ' . $rules->buildHeader($reply->getMime(), [ 'charset' => $reply->getEncoding() ]));

		foreach ($reply->getHeaders() as $name => $value) $resource->sendHeader($name . ': ' . $value);

		if ($driver->useCookies()->hasChanges()) return 'cookies';
		else if ($reply->isRedirect()) return 'redirect';
		else return self::STEP_SUCCESS;
	}

	public function sendCookiesStep(IHttpDriver& $driver) {
		$cookies =& $driver->useCookies();
		$resource = $driver->useReplyResource();

		foreach ($cookies->getChangedNames() as $name) {
			if ($cookies->isUpdated($name)) $resource->sendCookie($name, $cookies->getValue($name), $cookies->getExpiry($name));
			else if ($cookies->isRemoved($name)) $resource->sendCookie($name, '', 0);
		}

		if ($driver->useReply()->isRedirect()) return 'redirect';
		else return self::STEP_SUCCESS;
	}

	public function sendRedirectStep(IHttpDriver& $driver) {
		$reply =& $driver->useReply();

		$driver->useReplyResource()->sendHeader('Location: ' . $reply->getRedirectTarget());

		if (empty($reply->getBody())) return 'body';
		else return self::STEP_SUCCESS;
	}

	public function redirectBodyStep(IHttpDriver& $driver) {
		$reply =& $driver->useReply();

		$body = $driver
			->useConfig()
			->getMimeBody($reply->getCode(), $reply->getMime(), $reply->getRedirectTarget());

		$reply->setBody($body);
	}

	public function sendBodyStep(IHttpDriver& $driver) {
		$reply = $driver->useReply();
		$body = $reply->getBody();

		$driver
			->useReplyResource()
			->sendHeader('Content-Length: ' . strlen($body))
			->sendBody($body);
	}

	public function exitStep(IHttpDriver& $driver) {
		exit();
	}
}
