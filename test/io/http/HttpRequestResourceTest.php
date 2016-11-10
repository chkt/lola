<?php

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\io\http\HttpRequestResource;



class HttpRequestResourceTest
extends TestCase
{
	use PHPMock;



	public function testGetTime() {
		$_SERVER['REQUEST_TIME'] = 3;

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getTime(), 3);
	}

	public function testGetProtocol() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTPS')
			)
			->willReturn('');

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTPS')
			)
			->willReturn('off');

		$filter
			->expects($this->at(2))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTPS')
			)
			->willReturn('https');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getProtocol(), 'http');
		$this->assertEquals($resource->getProtocol(), 'http');
		$this->assertEquals($resource->getProtocol(), 'https');
	}

	public function testGetHostName() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->once())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('SERVER_NAME')
			)
			->willReturn('sub.domain.tld');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getHostName(), 'sub.domain.tld');
	}

	public function testGetPath() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('REQUEST_URI')
			)
			->willReturn('path/to/resource');

		$resource = new lola\io\http\HttpRequestResource();

		$this->assertEquals($resource->getPath(), 'path/to/resource');
	}

	public function testGetQuery() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('QUERY_STRING')
			)
			->willReturn('a=b,c,d&d=e&f&g,h,i');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getQuery(), [
			'a' => 'b,c,d',
			'd' => 'e',
			'f' => '',
			'g,h,i' => ''
		]);
	}

	public function testGetMethod() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('REQUEST_METHOD')
			)
			->willReturn('GET');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getMethod(), 'GET');
	}

	public function testGetMime() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_CONTENT_TYPE')
			)
			->willReturn('plain/text;charset=utf8');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getMime(), 'plain/text');
	}

	public function testGetEncoding() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_CONTENT_TYPE')
			)
			->willReturn('plain/text;charset=utf8');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getEncoding(), 'utf8');
	}

	public function testGetAcceptMimes() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_ACCEPT')
			)
			->willReturn('text/plain,text/html;q=0.1,application/xml;q=0.9');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getAcceptMimes(), [
			'text/plain' => 1.0,
			'application/xml' => 0.9,
			'text/html' => 0.1
		]);
	}

	public function testGetAcceptLanguages() {
		$filter= $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_ACCEPT_LANGUAGE')
			)
			->willReturn('en,es,en-us;q=0.9,en-gb;q=0.1');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getAcceptLanguages(), [
			'en' => 1.0,
			'es' => 1.0,
			'en-us' => 0.9,
			'en-gb' => 0.1
		]);
	}

	public function testGetClientIP() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('REMOTE_ADDR')
			)
			->willReturn('127.0.0.1');

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('REMOTE_ADDR')
			)
			->willReturn('::1');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getClientIP(), '127.0.0.1');
		$this->assertEquals($resource->getClientIP(), '::1');
	}

	public function testGetClientUA() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_USER_AGENT')
			)
			->willReturn('Arbitrary Mozilla/5.0');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getClientUA(), 'Arbitrary Mozilla/5.0');
	}

	public function testGetClientTime() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_DATE')
			)
			->willReturn('Thu, 10 Nov 2016 17:06:31 GMT');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getClientTime(), 1478797591);
	}

	public function testHasHeader() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_X_RANDOM_HEADER')
			)
			->willReturn(false);

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_X_SOME_HEADER')
			)
			->willReturn('foo');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->hasHeader('X-Random-Header'), false);
		$this->assertEquals($resource->hasHeader('X-Some-Header'), true);
	}

	public function testGetHeader() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_X_RANDOM_HEADER')
			)
			->willReturn(false);

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('HTTP_X_SOME_HEADER')
			)
			->willReturn('foo');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getHeader('X-Random-Header'), false);
		$this->assertEquals($resource->getHeader('X-Some-Header'), 'foo');
	}

	public function testHasCookie() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_COOKIE),
				$this->equalTo('a')
			)
			->willReturn(false);

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_COOKIE),
				$this->equalTo('b')
			)
			->willReturn('foo');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->hasCookie('a'), false);
		$this->assertEquals($resource->hasCookie('b'), true);
	}

	public function testGetCookie() {
		$filter = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$filter
			->expects($this->at(0))
			->with(
				$this->equalTo(INPUT_COOKIE),
				$this->equalTo('a')
			)
			->willReturn(false);

		$filter
			->expects($this->at(1))
			->with(
				$this->equalTo(INPUT_COOKIE),
				$this->equalTo('b')
			)
			->willReturn('foo');

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getCookie('a'), false);
		$this->assertEquals($resource->getCookie('b'), 'foo');
	}

	public function testGetBody() {
		$fopen = $this->getFunctionMock('\lola\io\http', 'fopen');
		$fopen
			->expects($this->any())
			->with(
				$this->equalTo('php://input'),
				$this->equalTo('r')
			)
			->willReturn('x');

		$stream = $this->getFunctionMock('\lola\io\http', 'stream_get_contents');
		$stream
			->expects($this->any())
			->with(
				$this->equalTo('x')
			)
			->willReturn('{"items":[]}');

		$fclose = $this->getFunctionMock('\lola\io\http', 'fclose');
		$fclose
			->expects($this->any())
			->with(
				$this->equalTo('x')
			)
			->willReturn(true);

		$resource = new HttpRequestResource();

		$this->assertEquals($resource->getBody(), '{"items":[]}');
	}
}
