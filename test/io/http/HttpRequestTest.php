<?php

require_once('MockDriver.php');

use PHPUnit\Framework\TestCase;

use lola\io\http\HttpRequest;
use test\io\http\MockDriver;



class HttpRequestTest
extends TestCase
{

	private $_driver;


	public function __construct() {
		parent::__construct();

		$this->_driver = new MockDriver();
	}


	public function testUsePayload() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($this->_driver->usePayload(), $request->usePayload());
	}

	public function testUseReply() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($this->_driver->useReply(), $request->useReply());
	}

	public function testUseCookies() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($this->_driver->useCookies(), $request->useCookies());
	}

	public function testUseClient() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($this->_driver->useClient(), $request->useClient());
	}


	public function testGetTime() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getTime(), 3);
	}

	public function testSetTime() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setTime(4), $request);
		$this->assertEquals($request->getTime(), 4);
	}

	public function testGetProtocol() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getProtocol(), 'http');
	}

	public function testSetProtocol() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setProtocol('https'), $request);
		$this->assertEquals($request->getProtocol(), 'https');
	}

	public function testGetHostName() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getHostName(), 'sub.domain.tld');
	}

	public function testSetHostName() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setHostName('foo'), $request);
		$this->assertEquals($request->getHostName(), 'foo');
	}

	public function testGetPath() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getPath(), '/path/to/resource');
	}

	public function testSetPath() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setPath('foo/bar/baz'), $request);
		$this->assertEquals($request->getPath(), 'foo/bar/baz');
	}

	public function testUseQuery() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->useQuery(), [
			'foo' => 'bar',
			'baz' => 'quux'
		]);
	}

	public function testSetQuery() {
		$request = new HttpRequest($this->_driver);

		$query = [
			'a' => 'b',
			'c' => 'd'
		];

		$this->assertEquals($request->setQuery($query), $request);
		$this->assertEquals($request->useQuery(), $query);
	}

	public function testGetMethod() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getMethod(), 'GET');
	}

	public function testSetMethod() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setMethod('POST'), $request);
		$this->assertEquals($request->getMethod(), 'POST');
	}

	public function testGetMime() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getMime(), 'text/plain');
	}

	public function testSetMime() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setMime('text/html'), $request);
		$this->assertEquals($request->getMime(), 'text/html');
	}

	public function testGetEncoding() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getEncoding(), 'iso-8859-1');
	}

	public function testSetEncoding() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setEncoding('utf-8'), $request);
		$this->assertEquals($request->getEncoding(), 'utf-8');
	}

	public function testUseAcceptMimes() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->useAcceptMimes(), [
			'text/plain' => 1.0,
			'text/html' => 0.5
		]);
	}

	public function testGetPreferedAcceptMime() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getPreferedAcceptMime([
			'application/json',
			'application/xml',
			'text/html'
		]), 'text/html');

		$this->assertEquals($request->getPreferedAcceptMime([
			'application/json',
			'application/xml'
		]), '');
	}

	public function testSetAcceptMimes() {
		$request = new HttpRequest($this->_driver);

		$map = [
			'application/json' => 1.0,
			'application/xml' => 0.5
		];

		$this->assertEquals($request->setAcceptMimes($map), $request);
		$this->assertEquals($request->useAcceptMimes(), $map);
	}

	public function testUseAcceptLanguages() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->useAcceptLanguages(), [
			'en' => 1.0,
			'en-us' => 0.9
		]);
	}

	public function testGetPreferedAcceptLanguage() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getPreferedAcceptLanguage([
			'es',
			'de',
			'fr',
			'en-us'
		]), 'en-us');

		$this->assertEquals($request->getPreferedAcceptLanguage([
			'es',
			'de',
			'fr'
		]), '');
	}

	public function testSetAcceptLanguages() {
		$request = new HttpRequest($this->_driver);

		$map = [
			'es' => 1.0,
			'es-es' => 0.9
		];

		$this->assertEquals($request->setAcceptLanguages($map), $request);
		$this->assertEquals($request->useAcceptLanguages(), $map);
	}


	public function testHasHeader() {
		$request = new HttpRequest($this->_driver);

		$this->assertTrue($request->hasHeader('Header-1'));
		$this->assertTrue($request->hasHeader('Header-2'));
		$this->assertFalse($request->hasHeader('Header-3'));
	}

	public function testGetHeader() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getHeader('Content-Type'), 'text/plain;charset=iso-8859-1');
		$this->assertEquals($request->getHeader('Accept'), 'text/plain,text/html;q=0.5');
		$this->assertEquals($request->getHeader('Accept-Language'), 'en,en-us;q=0.9');
		$this->assertEquals($request->getHeader('Header-1'), 'foo');
		$this->assertEquals($request->getHeader('Header-2'), 'bar');
	}

	public function testSetHeader() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setHeader('Content-Type', 'application/json;charset=utf-8'), $request);
		$this->assertEquals($request->getHeader('Content-Type'), 'application/json;charset=utf-8');
		$this->assertEquals($request->getMime(), 'application/json');
		$this->assertEquals($request->getEncoding(), 'utf-8');

		$this->assertEquals($request->setHeader('Accept', 'application/json,application/xml;q=0.5'), $request);
		$this->assertEquals($request->getHeader('Accept'), 'application/json,application/xml;q=0.5');
		$this->assertEquals($request->useAcceptMimes(), [
			'application/json' => 1.0,
			'application/xml' => 0.5
		]);

		$this->assertEquals($request->setHeader('Accept-Language', 'es,es-es;q=0.9'), $request);
		$this->assertEquals($request->getHeader('Accept-Language'), 'es,es-es;q=0.9');
		$this->assertEquals($request->useAcceptLanguages(), [
			'es' => 1.0,
			'es-es' => 0.9
		]);

		$this->assertEquals($request->setHeader('Header-3', 'baz'), $request);
		$this->assertTrue($request->hasHeader('Header-3'));
		$this->assertEquals($request->getHeader('Header-3'), 'baz');
	}


	public function testGetBody() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->getBody(), '{"items":[]}');
	}

	public function testSetBody() {
		$request = new HttpRequest($this->_driver);

		$this->assertEquals($request->setBody('foo-bar-baz'), $request);
		$this->assertEquals($request->getBody(), 'foo-bar-baz');
	}
}
