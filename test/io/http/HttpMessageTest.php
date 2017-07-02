<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpMessage;
use lola\io\http\HttpMessage;



final class HttpMessageTest
extends TestCase
{

	private function _produceMessage(string $startLine = 'GET / HTTP/1.1', $headers = [], $body = '') : IHttpMessage {
		return new HttpMessage($startLine, $headers, $body);
	}

	private function _produceMockHeaders() : array {
		return [
			IHttpMessage::HEADER_CONTENT_TYPE => ['application/json; charset=utf-8'],
			IHttpMessage::HEADER_SET_COOKIE => [
				'sid=abcdef; Path=/; Domain=sub.domain.tld',
				'debug=0'
			]
		];
	}


	public function testSetStartLine() {
		$start = 'HTTP/1.1 200 OK';
		$message = $this->_produceMessage($start);

		$this->assertEquals($start, $message->getStartLine());
	}

	public function testGetStartLine() {
		$request = 'GET / HTTP/1.1';
		$reply = 'HTTP/1.1 200 OK';
		$message = $this->_produceMessage($request);

		$this->assertEquals($request, $message->getStartLine());
		$this->assertSame($message, $message->setStartLine($reply));
		$this->assertEquals($reply, $message->getStartLine());
	}


	public function testHasHeader() {
		$message = $this->_produceMessage('', [
			IHttpMessage::HEADER_CONTENT_TYPE => ['application/json; encoding=utf-8']
		]);

		$this->assertTrue($message->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertFalse($message->hasHeader(IHttpMessage::HEADER_LOCATION));
	}

	public function testNumHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertEquals(1, $message->numHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals(2, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals(0, $message->numHeader(IHttpMessage::HEADER_LOCATION));
	}

	public function testGetHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertEquals('application/json; charset=utf-8', $message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals('sid=abcdef; Path=/; Domain=sub.domain.tld', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('sid=abcdef; Path=/; Domain=sub.domain.tld', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 0));
		$this->assertEquals('debug=0', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 1));
	}

	public function testSetHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertSame($message, $message->setHeader(IHttpMessage::HEADER_SET_COOKIE, 'foo=bar'));
		$this->assertEquals(2, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('foo=bar', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('debug=0', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 1));
		$this->assertSame($message, $message->setHeader(IHttpMessage::HEADER_CONTENT_TYPE, 'text/plain; encoding=utf-8'));
		$this->assertSame($message, $message->setHeader(IHttpMessage::HEADER_CONTENT_TYPE, 'application/xml; encoding=utf-8', 1));
		$this->assertEquals(2, $message->numHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals('text/plain; encoding=utf-8', $message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertEquals('application/xml; encoding=utf-8', $message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE, 1));
		$this->assertSame($message, $message->setHeader(IHttpMessage::HEADER_HOST, 'foo.bar.tld'));
		$this->assertEquals(1, $message->numHeader(IHttpMessage::HEADER_HOST));
		$this->assertEquals('foo.bar.tld', $message->getHeader(IHttpMessage::HEADER_HOST));
		$this->assertSame($message, $message->setHeader(IHttpMessage::HEADER_LOCATION, '/404'), 10);
		$this->assertEquals(1, $message->numHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertEquals('/404', $message->getHeader(IHttpMessage::HEADER_LOCATION));
	}

	public function testClearHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertSame($message, $message->clearHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertFalse($message->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertSame($message, $message->clearHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertFalse($message->hasHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertSame($message, $message->clearHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertFalse($message->hasHeader(IHttpMessage::HEADER_LOCATION));
	}

	public function testInsertHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertSame($message, $message->insertHeader(IHttpMessage::HEADER_SET_COOKIE, 'foo=bar', 1));
		$this->assertSame($message, $message->insertHeader(IHttpMessage::HEADER_SET_COOKIE, 'lang=en', 10));
		$this->assertEquals(4, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('sid=abcdef; Path=/; Domain=sub.domain.tld', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 0));
		$this->assertEquals('foo=bar', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 1));
		$this->assertEquals('debug=0', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 2));
		$this->assertEquals('lang=en', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 3));
		$this->assertSame($message, $message->insertHeader(IHttpMessage::HEADER_LOCATION, '/foo', 10));
		$this->assertEquals(1, $message->numHeader(IHttpMessage::HEADER_LOCATION));
		$this->assertEquals('/foo', $message->getHeader(IHttpMessage::HEADER_LOCATION, 0));
	}

	public function testAppendHeader() {
		$message = $this->_produceMessage('', $this->_produceMockHeaders());

		$this->assertSame($message, $message->appendHeader(IHttpMessage::HEADER_SET_COOKIE, 'foo=bar'));
		$this->assertEquals(3, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('sid=abcdef; Path=/; Domain=sub.domain.tld', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 0));
		$this->assertEquals('debug=0', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 1));
		$this->assertEquals('foo=bar', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE, 2));
	}

	public function testRemoveHeader() {
		$headers = $this->_produceMockHeaders();
		$headers[IHttpMessage::HEADER_SET_COOKIE][] = 'lang=en';

		$message = $this->_produceMessage('', $headers);

		$this->assertSame($message, $message->removeHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertSame($message, $message->removeHeader(IHttpMessage::HEADER_SET_COOKIE, 10));
		$this->assertEquals(1, $message->numHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertEquals('debug=0', $message->getHeader(IHttpMessage::HEADER_SET_COOKIE));
		$this->assertSame($message, $message->removeHeader(IHttpMessage::HEADER_CONTENT_TYPE));
		$this->assertFalse($message->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE));
	}


	public function testIterateHeaders() {
		$headers = $this->_produceMockHeaders();
		$message = $this->_produceMessage('', $headers);

		$iterator = $message->iterateHeaders();

		$this->assertInstanceOf(\Generator::class, $iterator);

		$cmp = [];

		foreach ($iterator as $name => $value) {
			if (!array_key_exists($name, $cmp)) $cmp[$name] = [];

			$cmp[$name][] = $value;
		}

		$this->assertEquals($cmp, $headers);
	}


	public function testGetBody() {
		$body = 'foo';
		$message = $this->_produceMessage('', [], $body);

		$this->assertEquals($body, $message->getBody());
	}

	public function testSetBody() {
		$first = 'foo';
		$second = 'bar';
		$message = $this->_produceMessage('', [], $first);

		$this->assertEquals($first, $message->getBody());
		$this->assertSame($message, $message->setBody($second));
		$this->assertEquals($second, $message->getBody());
	}


	public function test__toString() {
		$message = $this->_produceMessage('HTTP/1.1 200 OK', $this->_produceMockHeaders(), '{"foo":{"bar":"baz"}}');

		$str = "HTTP/1.1 200 OK\r\n" .
			"Content-Type: application/json; charset=utf-8\r\n" .
			"Set-Cookie: sid=abcdef; Path=/; Domain=sub.domain.tld\r\n" .
			"Set-Cookie: debug=0\r\n" .
			"\r\n" .
			"{\"foo\":{\"bar\":\"baz\"}}";

		$this->assertEquals($str, (string) $message);
	}
}
