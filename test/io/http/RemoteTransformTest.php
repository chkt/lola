<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\inject\IInjector;
use lola\io\ReplySentException;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpMessage;
use lola\io\http\RemoteTransform;
use lola\io\http\HttpDriver;



class RemoteTransformTest
extends TestCase
{

	use PHPMock;


	private function _produceRequestMessage(string $line = null, array $headers = null, string $body = null) : IHttpMessage {
		if (is_null($line)) $line = 'GET /path/to/resource HTTP/1.1';
		if (is_null($headers)) $headers = [
			IHttpMessage::HEADER_COOKIE => ['foo=1;bar=2;baz=3']
		];
		if(is_null($body)) $body = '';

		return new HttpMessage($line, $headers, $body);
	}

	private function _produceReplyMessage(string $line = null, array $headers = null, string $body = null) : IHttpMessage {
		if (is_null($line)) $line = 'HTTP/1.1 200 OK';
		if(is_null($headers)) $headers = [
			IHttpMessage::HEADER_CONTENT_TYPE => ['text/plain;charset=utf-8']
		];
		if (is_null($body)) $body = '';

		return new HttpMessage($line, $headers, $body);
	}


	public function _mockInjector() : IInjector {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		return $injector;
	}

	public function _mockDriver(IHttpMessage $request = null, IHttpMessage $reply = null) : IHttpDriver {
		if (is_null($request)) $request = $this->_produceRequestMessage();
		if (is_null($reply)) $reply = $this->_produceReplyMessage();

		$injector = $this->_mockInjector();

		$driver = new HttpDriver($injector);
		$driver
			->setRequestMessage($request)
			->setReplyMessage($reply);

		return $driver;
	}

	private function _produceTransform() : RemoteTransform {
		return new RemoteTransform();
	}


	public function testSetCookiesStep() {
		$driver = $this->_mockDriver();
		$cookies =& $driver->useCookies();
		$message =& $driver->useReplyMessage();
		$transform = $this->_produceTransform();

		$time = time() + 3600;

		$cookies->reset('foo');
		$cookies->set('bar', '1', $time);
		$cookies->set('baz', 'quux');

		$transform->setCookiesStep($driver);

		$this->assertEquals(3, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('foo=;Expires=Thu, 01 Jan 1970 00:00:00 GMT', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 0));
		$this->assertEquals('bar=1;Expires=' . gmdate('D, d M Y H:i:s T', $time), $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 1));
		$this->assertEquals('baz=quux', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 2));
	}

	public function testRedirectBodyStep() {
		$driver = $this->_mockDriver();
		$reply =& $driver->useReply();
		$message =& $driver->useReplyMessage();
		$transform = $this->_produceTransform();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource')
			->setMime('text/html')
			->setBody('');

		$transform->redirectBodyStep($driver);

		$this->assertEquals('', $message->getBody());
	}

	public function testSetContentLengthStep() {
		$driver = $this->_mockDriver();
		$message =& $driver->useReplyMessage();
		$transform = $this->_produceTransform();

		$message->setBody('Page Not Found');

		$transform->setContentLengthStep($driver);

		$this->assertEquals('14', $message->getHeader(IHttpMessage::HEADER_CONTENT_LENGTH));
	}

	public function testSendHeadersStep() {
		$driver = $this->_mockDriver();
		$transform = $this->_produceTransform();

		$result = [];

		$header = $this->getFunctionMock('\lola\io\http', 'header');
		$header
			->expects($this->any())
			->with($this->isType('string'))
			->willReturnCallback(function($header) use (& $result) {
				$result[] = $header;
			});

		$transform->sendHeadersStep($driver);

		$this->assertEquals([
			'HTTP/1.1 200 OK',
			'Content-Type: text/plain;charset=utf-8'
		], $result);
	}

	public function testSendBodyStep() {
		$driver = $this->_mockDriver();
		$transform = $this->_produceTransform();

		$body = '';

		$transform->sendBodyStep($driver);

		$this->assertEquals('', $body);
	}

	public function testExitStep() {
		$driver = $this->_mockDriver();
		$transform = $this->_produceTransform();

		$this->expectException(ReplySentException::class);

		$transform->exitStep($driver);
	}
}
