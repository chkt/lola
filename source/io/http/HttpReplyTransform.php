<?php

namespace lola\io\http;

use lola\type\AStateTransform;

use lola\io\http\IHttpDriver;
use lola\io\ReplySentException;



class HttpReplyTransform
extends AStateTransform
{

	const VERSION = '0.5.0';

	const STEP_FILTER_HEADERS = 'filterHeaders';
	const STEP_SEND_HEADERS = 'sendHeaders';
	const STEP_SEND_COOKIES = 'sendCookies';
	const STEP_SEND_REDIRECT = 'sendRedirect';
	const STEP_REDIRECT_BODY = 'redirectBody';
	const STEP_SEND_BODY = 'sendBody';
	const STEP_EXIT = 'exit';



	public function __construct() {
		parent::__construct([
			self::STEP_FIRST => [
				'next' => [
					self::STEP_SUCCESS => self::STEP_FILTER_HEADERS
				]
			],
			self::STEP_FILTER_HEADERS => [
				'transform' => 'filterHeaders',
				'next' => [
					self::STEP_SUCCESS => self::STEP_SEND_HEADERS
				]
			],
			self::STEP_SEND_HEADERS => [
				'transform' => 'sendHeaders',
				'next' => [
					'cookies' => self::STEP_SEND_COOKIES,
					'redirect' => self::STEP_SEND_REDIRECT,
					self::STEP_SUCCESS => self::STEP_SEND_BODY
				]
			],
			self::STEP_SEND_COOKIES => [
				'transform' => 'sendCookies',
				'next' => [
					'redirect' => self::STEP_SEND_REDIRECT,
					self::STEP_SUCCESS => self::STEP_SEND_BODY,
				]
			],
			self::STEP_SEND_REDIRECT => [
				'transform' => 'sendRedirect',
				'next' => [
					'body' => self::STEP_REDIRECT_BODY,
					self::STEP_SUCCESS => self::STEP_SEND_BODY
				]
			],
			self::STEP_REDIRECT_BODY => [
				'transform' => 'redirectBody',
				'next' => [
					self::STEP_SUCCESS => self::STEP_SEND_BODY
				]
			],
			self::STEP_SEND_BODY => [
				'transform' => 'sendBody',
				'next' => [
					self::STEP_SUCCESS => self::STEP_EXIT
				]
			],
			self::STEP_EXIT => [
				'transform' => 'exit',
				'next' => [
					self::STEP_SUCCESS => ''
				]
			]
		]);
	}


	public function filterHeadersStep(IHttpDriver& $driver) {
		$reply = $driver->getReply();

		if ($reply->hasHeader('Content-Length')) $reply->resetHeader('Content-Length');
		if ($reply->hasHeader('Set-Cookie')) $reply->resetHeader('Set-Cookie');
	}

	public function sendHeadersStep(IHttpDriver& $driver) {
		while (ob_get_level() !== 0) ob_end_clean();

		$reply = $driver->getReply();
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

		if ($driver->getReply()->isRedirect()) return 'redirect';
		else return self::STEP_SUCCESS;
	}

	public function sendRedirectStep(IHttpDriver& $driver) {
		$reply = $driver->getReply();

		$driver->useReplyResource()->sendHeader('Location: ' . $reply->getRedirectTarget());

		if (empty($reply->getBody())) return 'body';
		else return self::STEP_SUCCESS;
	}

	public function redirectBodyStep(IHttpDriver& $driver) {
		$reply = $driver->getReply();

		$body = $driver
			->useConfig()
			->getMimeBody($reply->getCode(), $reply->getMime(), $reply->getRedirectTarget());

		$reply->setBody($body);
	}

	public function sendBodyStep(IHttpDriver& $driver) {
		$reply = $driver->getReply();
		$body = $reply->getBody();

		$driver
			->useReplyResource()
			->sendHeader('Content-Length: ' . strlen($body))
			->sendBody($body);
	}

	public function exitStep(IHttpDriver& $driver) {
		throw new ReplySentException();
	}
}
