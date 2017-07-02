<?php

namespace test\io\http;

use lola\io\ReplySentException;
use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\inject\IInjector;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpMessage;
use lola\io\http\HttpDriver;
use lola\io\http\RemoteTransform;



final class RemoteTransformStatesTest
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


	private function _mockInjector() : IInjector {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		return $injector;
	}

	private function& _mockOutput() {
		$res = [];

		$header = $this->getFunctionMock('lola\io\http', 'header');
		$header
			->expects($this->any())
			->with($this->isType('string'))
			->willReturnCallback(function($header) use (& $res) {
				$res[] = $header;
			});

		$fopen = $this->getFunctionMock('\lola\io\http', 'fopen');
		$fopen
			->expects($this->any())
			->with($this->equalTo('php://output'), $this->equalTo('r+'))
			->willReturn(1);

		$fwrite = $this->getFunctionMock('\lola\io\http', 'fwrite');
		$fwrite
			->expects($this->any())
			->with($this->equalTo(1), $this->isType('string'))
			->willReturnCallback(function(int $handle, string $content) use (& $res) {
				$res[] = 'Body: ' . $content;
			});

		$fclose = $this->getFunctionMock('\lola\io\http', 'fclose');
		$fclose
			->expects($this->any())
			->with($this->equalTo(1))
			->willReturn(true);

		return $res;
	}


	private function _processTransform(IHttpDriver& $driver) {
		$trn = new RemoteTransform();

		try {
			$trn->setTarget($driver)->process();
		}
		catch (ReplySentException $ex) {}
	}


	private function _produceDriver(IHttpMessage $request = null, IHttpMessage $reply = null) : IHttpDriver {
		if (is_null($request)) $request = $this->_produceRequestMessage();
		if (is_null($reply)) $reply = $this->_produceReplyMessage();

		$injector = $this->_mockInjector();

		$driver = new HttpDriver($injector);
		$driver
			->setRequestMessage($request)
			->setReplyMessage($reply);

		return $driver;
	}


	public function testSimpleReply() {
		$driver = $this->_produceDriver();
		$reply =& $driver->useReply();
		$result =& $this->_mockOutput();

		$reply
			->setCode('404')
			->setMime('text/plain')
			->setHeader('X-Header', 'foo')
			->setBody('Sorry no have');

		$this->_processTransform($driver);

		$this->assertEquals([
			'HTTP/1.1 404 Not Found',
			'Content-Type: text/plain;charset=utf-8',
			'Content-Length: 13',
			'X-Header: foo',
			'Body: Sorry no have'
		], $result);
	}

	public function testCookieReply() {
		$driver = $this->_produceDriver();
		$reply =& $driver->useReply();
		$result =& $this->_mockOutput();

		$reply
			->setCode('404')
			->setMime('text/plain')
			->setHeader('X-Header', 'foo')
			->setBody('Sorry is finish')
			->useCookies()
			->set('a', 'bar', 3600)
			->set('b', 'baz', 60);

		$this->_processTransform($driver);

		$this->assertEquals([
			'HTTP/1.1 404 Not Found',
			'Content-Type: text/plain;charset=utf-8',
			'Content-Length: 15',
			'Set-Cookie: a=bar;Expires=Thu, 01 Jan 1970 01:00:00 GMT',
			'Set-Cookie: b=baz;Expires=Thu, 01 Jan 1970 00:01:00 GMT',
			'X-Header: foo',
			'Body: Sorry is finish'
		], $result);
	}

	public function testCookieRedirectReply() {
		$driver = $this->_produceDriver();
		$reply =& $driver->useReply();
		$result =& $this->_mockOutput();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource')
			->setMime('text/plain')
			->setHeader('X-Header', 'foo')
			->useCookies()
			->set('a', 'bar', 3600)
			->set('b', 'baz', 60);

		$this->_processTransform($driver);

		$this->assertEquals([
			'HTTP/1.1 302 Found',
			'Location: /path/to/resource',
			'Content-Type: text/plain;charset=utf-8',
			'Content-Length: 30',
			'Set-Cookie: a=bar;Expires=Thu, 01 Jan 1970 01:00:00 GMT',
			'Set-Cookie: b=baz;Expires=Thu, 01 Jan 1970 00:01:00 GMT',
			'X-Header: foo',
			'Body: 302 - Found: /path/to/resource'
		], $result);
	}
}
