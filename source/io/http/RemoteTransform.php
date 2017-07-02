<?php

namespace lola\io\http;

use lola\type\AStateTransform;

use lola\io\ReplySentException;




final class RemoteTransform
extends AStateTransform
{

	const STEP_COOKIES = 'setCookies';
	const STEP_REDIRECT = 'redirect';
	const STEP_CONTENT = 'content';
	const STEP_HEADERS = 'headers';
	const STEP_BODY = 'body';
	const STEP_EXIT = 'exit';



	public function __construct() {
		parent::__construct([
			self::STEP_FIRST => [
				'next' => [
					self::STEP_SUCCESS => self::STEP_COOKIES
				]
			],
			self::STEP_COOKIES => [
				'transform' => 'setCookies',
				'next' => [
					'redirect' => self::STEP_REDIRECT,
					self::STEP_SUCCESS => self::STEP_CONTENT
				]
			],
			self::STEP_REDIRECT => [
				'transform' => 'redirectBody',
				'next' => [
					self::STEP_SUCCESS => self::STEP_CONTENT
				]
			],
			self::STEP_CONTENT => [
				'transform' => 'setContentLength',
				'next' => [
					self::STEP_SUCCESS => self::STEP_HEADERS
				]
			],
			self::STEP_HEADERS => [
				'transform' => 'sendHeaders',
				'next' => [
					self::STEP_SUCCESS => self::STEP_BODY
				]
			],
			self::STEP_BODY => [
				'transform' => 'sendBody',
				'next' => [
					self::STEP_SUCCESS => self::STEP_EXIT
				]
			],
			self::STEP_EXIT => [
				'transform' => 'exit',
				'next' => [
					self::STEP_SUCCESS => self::STEP_END
				]
			]
		]);
	}


	public function setCookiesStep(IHttpDriver& $driver) {
		$message =& $driver->useReplyMessage();
		$cookies =& $driver->useCookies();

		foreach ($cookies->getChangedNames() as $name) {
			$opts = [];

			if ($cookies->isRemoved($name)) $opts['Expires'] = gmdate('D, d M Y H:i:s T', 0);
			else if ($cookies->getExpiry($name) !== 0) $opts['Expires'] = gmdate('D, d M Y H:i:s T', $cookies->getExpiry($name));

			$message->appendHeader(IHttpMessage::HEADER_SET_COOKIE, HttpConfig::buildHeader($name . '=' . $cookies->getValue($name), $opts));
		}

		if ($driver->useReply()->isRedirect()) return 'redirect';
		else return self::STEP_SUCCESS;
	}

	public function redirectBodyStep(IHttpDriver& $driver) {
		$message =& $driver->useReplyMessage();
		$reply =& $driver->useReply();
		$config =& $driver->useConfig();

		if (empty($message->getBody())) $message->setBody($config->getMimeBody(
			$reply->getCode(),
			$reply->getMime(),
			$reply->getRedirectTarget()
		));
	}

	public function setContentLengthStep(IHttpDriver& $driver) {
		$message =& $driver->useReplyMessage();
		$len = strlen($message->getBody());

		$message->clearHeader(IHttpMessage::HEADER_CONTENT_LENGTH);

		if ($len !== 0) $message->setHeader(IHttpMessage::HEADER_CONTENT_LENGTH, $len);
	}


	public function sendHeadersStep(IHttpDriver& $driver) {
		while (ob_get_level() !== 0) ob_end_clean();

		$message =& $driver->useReplyMessage();

		header($message->getStartLine());

		foreach ($message->iterateHeaders([
			IHttpMessage::HEADER_LOCATION,
			IHttpMessage::HEADER_CONTENT_TYPE,
			IHttpMessage::HEADER_CONTENT_LENGTH,
			IHttpMessage::HEADER_SET_COOKIE
		]) as $name => $content) header($name . ': ' . $content);
	}

	public function sendBodyStep(IHttpDriver& $driver) {
		$handle = fopen('php://output', 'r+');

		fwrite($handle, $driver->useReplyMessage()->getBody());
		fclose($handle);
	}

	public function exitStep(IHttpDriver& $driver) {
		throw new ReplySentException();
	}
}
