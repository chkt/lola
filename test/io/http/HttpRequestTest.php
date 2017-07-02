<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\connect\IConnection;
use lola\io\connect\Connection;
use lola\io\http\IHttpConfig;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpRequest;
use lola\io\http\IHttpReply;
use lola\io\http\IHttpClient;
use lola\io\http\IHttpCookies;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpConfig;
use lola\io\http\HttpMessage;
use lola\io\http\HttpRequest;
use lola\io\mime\IMimePayload;



class HttpRequestTest
extends TestCase
{

	private function _produceConnection(array $data = null) : IConnection {
		if (is_null($data)) $data = [
			'ts' => 3,
			'tls' => true,
			'host' => [
				'name' => 'sub.domain.tld'
			]
		];

		return new Connection($data);
	}

	private function _produceConfig() : IHttpConfig {
		return new HttpConfig();
	}

	private function _produceMessage(string $line = null, array $headers = null, string $body = null) {
		if (is_null($line)) $line = 'GET /path/to/resource?foo=bar&baz=quux HTTP/1.1';
		if (is_null($headers)) $headers = [
			IHttpMessage::HEADER_CONTENT_TYPE => ['text/plain;charset=iso-8859-1'],
			IHttpMessage::HEADER_ACCEPT_MIME => ['text/plain,text/html;q=0.5'],
			IHttpMessage::HEADER_ACCEPT_LANGUAGE => ['en,en-us;q=0.9'],
			'Header-1' => ['foo'],
			'Header-2' => ['bar']
		];
		if (is_null($body)) $body = '{"items":[]}';

		return new HttpMessage($line, $headers, $body);
	}

	private function _produceMock(string $qname) {
		return $this->getMockBuilder($qname)->getMock();
	}

	private function _mockDriver(IConnection $connection = null, IHttpMessage $message = null) : IHttpDriver {
		if (is_null($connection)) $connection = $this->_produceConnection();
		if (is_null($message)) $message = $this->_produceMessage();

		$config = $this->_produceConfig();
		$payload = $this->_produceMock(IMimePayload::class);
		$reply = $this->_produceMock(IHttpReply::class);
		$client = $this->_produceMock(IHttpClient::class);
		$cookies = $this->_produceMock(IHttpCookies::class);

		$driver = $this
			->getMockBuilder(IHttpDriver::class)
			->getMock();

		$driver
			->expects($this->any())
			->method('useConnection')
			->with()
			->willReturnReference($connection);

		$driver
			->expects($this->any())
			->method('useConfig')
			->with()
			->willReturnReference($config);

		$driver
			->expects($this->any())
			->method('useRequestPayload')
			->with()
			->willReturnReference($payload);

		$driver
			->expects($this->any())
			->method('useRequestMessage')
			->with()
			->willReturnReference($message);

		$driver
			->expects($this->any())
			->method('useReply')
			->with()
			->willReturnReference($reply);

		$driver
			->expects($this->any())
			->method('useClient')
			->with()
			->willReturnReference($client);

		$driver
			->expects($this->any())
			->method('useCookies')
			->with()
			->willReturnReference($cookies);

		return $driver;
	}

	private function _produceRequest(IHttpDriver $driver = null) : IHttpRequest {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new HttpRequest($driver);
	}


	public function testUsePayload() {
		$driver = $this->_mockDriver();
		$request = $this->_produceRequest($driver);

		$this->assertSame($driver->useRequestPayload(), $request->usePayload());
	}

	public function testUseReply() {
		$driver = $this->_mockDriver();
		$request = $this->_produceRequest($driver);

		$this->assertSame($driver->useReply(), $request->useReply());
	}

	public function testUseCookies() {
		$driver = $this->_mockDriver();
		$request = $this->_produceRequest($driver);

		$this->assertSame($driver->useCookies(), $request->useCookies());
	}

	public function testUseClient() {
		$driver = $this->_mockDriver();
		$request = $this->_produceRequest($driver);

		$this->assertSame($driver->useClient(), $request->useClient());
	}


	public function testGetTime() {
		$request = $this->_produceRequest();

		$this->assertEquals(3, $request->getTime());
	}

	public function testSetTime() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setTime(4));
		$this->assertEquals(4, $request->getTime());
	}

	public function testGetTLS() {
		$request = $this->_produceRequest();

		$this->assertTrue($request->getTLS());
	}

	public function testSetTLS() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setTLS(false));
		$this->assertFalse($request->getTLS());
	}

	public function testGetHostName() {
		$request = $this->_produceRequest();

		$this->assertEquals('sub.domain.tld', $request->getHostName());
	}

	public function testSetHostName() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setHostName('foo'));
		$this->assertEquals('foo', $request->getHostName());
	}

	public function testGetPath() {
		$request = $this->_produceRequest();

		$this->assertEquals('/path/to/resource', $request->getPath());
	}

	public function testSetPath() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setPath('foo/bar/baz'));
		$this->assertEquals('foo/bar/baz', $request->getPath());
	}

	public function testGetQuery() {
		$request = $this->_produceRequest();

		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $request->getQuery());
	}

	public function testSetQuery() {
		$request = $this->_produceRequest();

		$query = [
			'a' => 'b',
			'c' => 'd'
		];

		$this->assertSame($request, $request->setQuery($query));
		$this->assertEquals($query, $request->getQuery());
	}

	public function testGetMethod() {
		$request = $this->_produceRequest();

		$this->assertEquals('GET', $request->getMethod());
	}

	public function testSetMethod() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setMethod('POST'));
		$this->assertEquals('POST', $request->getMethod());
	}

	public function testGetMime() {
		$request = $this->_produceRequest();

		$this->assertEquals('text/plain', $request->getMime());
	}

	public function testSetMime() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setMime('text/html'));
		$this->assertEquals('text/html', $request->getMime());
	}

	public function testGetEncoding() {
		$request = $this->_produceRequest();

		$this->assertEquals('iso-8859-1', $request->getEncoding());
	}

	public function testSetEncoding() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setEncoding('utf-8'));
		$this->assertEquals('utf-8', $request->getEncoding());
	}


	public function testGetAcceptMimes() {
		$request = $this->_produceRequest();

		$this->assertEquals([
			'text/plain' => 1.0,
			'text/html' => 0.5
		], $request->getAcceptMimes());
	}

	public function testGetPreferedAcceptMime() {
		$request = $this->_produceRequest();

		$this->assertEquals('text/html', $request->getPreferedAcceptMime([
			'application/json',
			'application/xml',
			'text/html'
		]));

		$this->assertEquals('', $request->getPreferedAcceptMime([
			'application/json',
			'application/xml'
		]));
	}

	public function testSetAcceptMimes() {
		$request = $this->_produceRequest();

		$map = [
			'application/json' => 1.0,
			'application/xml' => 0.5
		];

		$this->assertSame($request, $request->setAcceptMimes($map));
		$this->assertEquals($map, $request->getAcceptMimes());
	}


	public function testGetAcceptLanguages() {
		$request = $this->_produceRequest();

		$this->assertEquals([
			'en' => 1.0,
			'en-us' => 0.9
		], $request->getAcceptLanguages());
	}

	public function testGetPreferedAcceptLanguage() {
		$request = $this->_produceRequest();

		$this->assertEquals('en-us', $request->getPreferedAcceptLanguage([
			'es',
			'de',
			'fr',
			'en-us'
		]));

		$this->assertEquals('', $request->getPreferedAcceptLanguage([
			'es',
			'de',
			'fr'
		]));
	}

	public function testSetAcceptLanguages() {
		$request = $this->_produceRequest();

		$map = [
			'es' => 1.0,
			'es-es' => 0.9
		];

		$this->assertSame($request, $request->setAcceptLanguages($map));
		$this->assertEquals($map, $request->getAcceptLanguages());
	}


	public function testHasHeader() {
		$request = $this->_produceRequest();

		$this->assertTrue($request->hasHeader('Header-1'));
		$this->assertTrue($request->hasHeader('Header-2'));
		$this->assertFalse($request->hasHeader('Header-3'));
	}

	public function testGetHeader() {
		$request = $this->_produceRequest();

		$this->assertEquals('text/plain;charset=iso-8859-1', $request->getHeader('Content-Type'));
		$this->assertEquals('text/plain,text/html;q=0.5', $request->getHeader('Accept'));
		$this->assertEquals('en,en-us;q=0.9', $request->getHeader('Accept-Language'));
		$this->assertEquals('foo', $request->getHeader('Header-1'));
		$this->assertEquals('bar', $request->getHeader('Header-2'));
	}

	public function testSetHeader() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setHeader('Content-Type', 'application/json;charset=utf-8'));
		$this->assertEquals('application/json;charset=utf-8', $request->getHeader('Content-Type'));
		$this->assertEquals('application/json', $request->getMime());
		$this->assertEquals('utf-8', $request->getEncoding());

		$this->assertSame($request, $request->setHeader('Accept', 'application/json,application/xml;q=0.5'));
		$this->assertEquals('application/json,application/xml;q=0.5', $request->getHeader('Accept'));
		$this->assertEquals([
			'application/json' => 1.0,
			'application/xml' => 0.5
		], $request->getAcceptMimes());

		$this->assertSame($request, $request->setHeader('Accept-Language', 'es,es-es;q=0.9'));
		$this->assertEquals('es,es-es;q=0.9', $request->getHeader('Accept-Language'));
		$this->assertEquals([
			'es' => 1.0,
			'es-es' => 0.9
		], $request->getAcceptLanguages());

		$this->assertSame($request, $request->setHeader('Header-3', 'baz'));
		$this->assertTrue($request->hasHeader('Header-3'));
		$this->assertEquals('baz', $request->getHeader('Header-3'));
	}


	public function testGetBody() {
		$request = $this->_produceRequest();

		$this->assertEquals('{"items":[]}', $request->getBody());
	}

	public function testSetBody() {
		$request = $this->_produceRequest();

		$this->assertSame($request, $request->setBody('foo-bar-baz'));
		$this->assertEquals('foo-bar-baz', $request->getBody());
	}
}
