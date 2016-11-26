<?php

require_once('MockDriver.php');

use PHPUnit\Framework\TestCase;
use test\io\http\MockDriver;
use lola\io\http\HttpReplyTransform;



class HttpReplyTransformTest
extends TestCase
{

	public function testFilterHeadersStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply
			->setHeader('Content-Length', '200')
			->setHeader('Set-Cookie', 'a=foo');

		$trn->filterHeadersStep($driver);

		$this->assertFalse($reply->hasHeader('Content-Length'));
		$this->assertFalse($reply->hasHeader('Set-Cookie'));
	}

	public function testSendHeadersStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply
			->setCode('302')
			->setRedirectTarget('/to/redirect')
			->setMime('text/html')
			->setHeader('Header-1', 'foo')
			->setHeader('Header-2', 'bar')
			->setHeader('Header-3', 'baz');

		$trn->sendHeadersStep($driver);

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'HTTP/1.1 302 Found'
		], [
			'type' => 'header',
			'content' => 'Content-Type: text/html;charset=utf-8'
		], [
			'type' => 'header',
			'content' => 'Header-1: foo'
		], [
			'type' => 'header',
			'content' => 'Header-2: bar'
		], [
			'type' => 'header',
			'content' => 'Header-3: baz'
		]]);
	}

	public function testSendCookiesStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$cookies = $driver->useCookies();

		$cookies
			->set('a', 'abc', 1234)
			->set('b', 'cba', 4321);

		$trn->sendCookiesStep($driver);

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'cookie',
			'name' => 'a',
			'value' => 'abc',
			'expires' => 1234
		], [
			'type' => 'cookie',
			'name' => 'b',
			'value' => 'cba',
			'expires' => 4321
		]]);
	}

	public function testSendRedirectStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply
			->setCode('302')
			->setRedirectTarget('/to/resource');

		$trn->sendRedirectStep($driver);

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'Location: /to/resource'
		]]);
	}

	public function testRedirectBodyStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply
			->setMime('text/plain')
			->setCode('302')
			->setRedirectTarget('/to/resource');

		$trn->redirectBodyStep($driver);

		$this->assertEquals($reply->getBody(), '302 - Found: /to/resource');
	}

	public function testSendBodyStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply->setBody('foo-bar-baz');

		$trn->sendBodyStep($driver);

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'Content-Length: 11'
		],[
			'type' => 'body',
			'content' => 'foo-bar-baz'
		]]);
	}

	public function testExitStep() {
		$trn = new HttpReplyTransform();
		$driver = new MockDriver();

		$this->expectException('\\lola\\io\\ReplySentException');

		$trn->exitStep($driver);
	}
}
