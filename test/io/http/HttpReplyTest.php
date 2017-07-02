<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpConfig;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpRequest;
use lola\io\http\IHttpCookies;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpMessage;
use lola\io\http\HttpReply;
use lola\io\http\HttpConfig;
use lola\io\mime\IMimePayload;



class HttpReplyTest
extends TestCase
{

	private function _produceConfig() : IHttpConfig {
		return new HttpConfig();
	}

	private function _produceMessage(string $line = null, array $headers = null, string $body = null) : IHttpMessage {
		if (is_null($line)) $line = 'HTTP/1.1 200 OK';
		if (is_null($headers)) $headers = [
			IHttpMessage::HEADER_CONTENT_TYPE => ['text/plain;charset=iso-8859-1']
		];
		if (is_null($body)) $body = '200 - OK';

		return new HttpMessage($line, $headers, $body);
	}

	private function _produceMock(string $qname) {
		return $this
			->getMockBuilder($qname)
			->getMock();
	}

	private function _mockDriver(IHttpMessage $message = null) {
		if (is_null($message)) $message = $this->_produceMessage();

		$config = $this->_produceConfig();
		$request = $this->_produceMock(IHttpRequest::class);
		$cookies = $this->_produceMock(IHttpCookies::class);
		$mime = $this->_produceMock(IMimePayload::class);

		$driver = $this
			->getMockBuilder(IHttpDriver::class)
			->getMock();

		$driver
			->expects($this->any())
			->method('useConfig')
			->with()
			->willReturnReference($config);

		$driver
			->expects($this->any())
			->method('useReplyMessage')
			->with()
			->willReturnReference($message);

		$driver
			->expects($this->any())
			->method('useRequest')
			->with()
			->willReturnReference($request);

		$driver
			->expects($this->any())
			->method('useCookies')
			->with()
			->willReturnReference($cookies);

		$driver
			->expects($this->any())
			->method('useReplyPayload')
			->with()
			->willReturnReference($mime);

		$driver
			->expects($this->any())
			->method('sendReply')
			->with()
			->willReturn(true);

		return $driver;
	}

	private function _produceReply(IHttpDriver $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new HttpReply($driver);
	}


	public function testUsePayload() {
		$driver = $this->_mockDriver();
		$reply = $this->_produceReply($driver);

		$this->assertSame($driver->useReplyPayload(), $reply->usePayload());
	}

	public function testUseRequest() {
		$driver = $this->_mockDriver();
		$reply = $this->_produceReply($driver);

		$this->assertSame($driver->useRequest(), $reply->useRequest());
	}

	public function testUseCookies() {
		$driver = $this->_mockDriver();
		$reply = $this->_produceReply($driver);

		$this->assertSame($driver->useCookies(), $reply->useCookies());
	}


	public function testGetCode() {
		$reply = $this->_produceReply();

		$this->assertEquals('200', $reply->getCode());
	}

	public function testSetCode() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setCode('404'));
		$this->assertEquals('404', $reply->getCode());
	}

	public function testGetCodeHeader() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setCode(IHttpConfig::CODE_NOT_FOUND));
		$this->assertEquals('HTTP/1.1 404 Not Found', $reply->getCodeHeader());
	}

	public function testGetCodeMessage() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setCode(IHttpConfig::CODE_NOT_FOUND));
		$this->assertEquals('404 - Page not found', $reply->getCodeMessage());
	}


	public function testGetMime() {
		$reply = $this->_produceReply();

		$this->assertEquals('text/plain', $reply->getMime());
	}

	public function testSetMime() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setMime('text/html'));
		$this->assertEquals('text/html', $reply->getMime());
	}

	public function testGetEncoding() {
		$reply = $this->_produceReply();

		$this->assertEquals('iso-8859-1', $reply->getEncoding());
	}

	public function testSetEncoding() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setEncoding('utf-8'));
		$this->assertEquals('utf-8', $reply->getEncoding());
	}

	public function testIsRedirect() {
		$reply = $this->_produceReply();

		$this->assertFalse($reply->isRedirect());

		$this->assertSame($reply, $reply->setCode('301'));
		$this->assertTrue($reply->isRedirect());

		$this->assertSame($reply, $reply->setCode('404'));
		$this->assertFalse($reply->isRedirect());
	}

	public function testGetRedirectTarget() {
		$reply = $this->_produceReply();

		$this->assertEquals('', $reply->getRedirectTarget());
	}

	public function testSetRedirectTarget() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setRedirectTarget('/path/to/resource'));
		$this->assertEquals('/path/to/resource', $reply->getRedirectTarget());
	}

	public function testHasHeader() {
		$reply = $this->_produceReply();

		$this->assertTrue($reply->hasHeader('Content-Type'));
		$this->assertFalse($reply->hasHeader('Location'));
		$this->assertFalse($reply->hasHeader('Header-1'));
	}

	public function testGetHeader() {
		$reply = $this->_produceReply();

		$this->assertEquals('text/plain;charset=iso-8859-1', $reply->getHeader('Content-Type'));
	}

	public function testSetHeader() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setHeader(IHttpMessage::HEADER_CONTENT_TYPE, 'text/html;charset=utf-8'));
		$this->assertEquals('text/html;charset=utf-8', $reply->getHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals('text/html', $reply->getMime());
		$this->assertEquals('utf-8', $reply->getEncoding());

		$this->assertSame($reply, $reply->setHeader(IHttpMessage::HEADER_LOCATION, '/path/to/resource'));
		$this->assertEquals('/path/to/resource', $reply->getHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertEquals('/path/to/resource', $reply->getRedirectTarget());

		$this->assertSame($reply, $reply->setHeader('Header-1', 'foo'));
		$this->assertTrue($reply->hasHeader('Header-1'));
		$this->assertEquals('foo', $reply->getHeader('Header-1'));
	}

	public function testResetHeader() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setHeader(IHttpMessage::HEADER_CONTENT_TYPE, 'text/html;charset=iso-8859-1'));
		$this->assertSame($reply, $reply->resetHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertFalse($reply->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals('', $reply->getMime());
		$this->assertEquals('', $reply->getEncoding());

		$this->assertSame($reply, $reply->setHeader(IHttpMessage::HEADER_LOCATION, '/path/to/resource'));
		$this->assertSame($reply, $reply->resetHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertFalse($reply->hasHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertEquals('', $reply->getRedirectTarget());

		$this->assertSame($reply, $reply->setHeader('Header-1', 'foo'));
		$this->assertSame($reply, $reply->resetHeader('Header-1'));
		$this->assertFalse($reply->hasHeader('Header-1'));
	}


	public function testGetBody() {
		$reply = $this->_produceReply();

		$this->assertEquals('200 - OK', $reply->getBody());
	}

	public function testSetBody() {
		$reply = $this->_produceReply();

		$this->assertSame($reply, $reply->setBody('foo'));
		$this->assertEquals('foo', $reply->getBody());
	}


	public function testSend() {
		$reply = $this->_produceReply();

		$this->assertTrue($reply->send());
	}
}
