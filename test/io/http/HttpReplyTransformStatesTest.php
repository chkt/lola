<?php

require_once('MockDriver.php');

use PHPUnit\Framework\TestCase;
use test\io\http\MockDriver;



class HttpReplyTransformStatesTest
extends TestCase
{

	public function testSimpleReply() {
		$driver = new MockDriver();
		$reply =& $driver->useReply();
		$trn =& $driver->useReplyTransform();

		$reply
			->setCode('404')
			->setMime('text/plain')
			->setHeader('X-Header-1', 'foo')
			->setBody('Does not exist - sorry...');

		try {
			$trn
				->setTarget($driver)
				->process();

			throw new \Exception();
		}
		catch (\Exception $ex) {
			$this->assertInstanceOf('\lola\io\IReplySentException', $ex);
		}

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'HTTP/1.1 404 Not Found'
		], [
			'type' => 'header',
			'content' => 'Content-Type: text/plain;charset=utf-8'
		], [
			'type' => 'header',
			'content' => 'X-Header-1: foo'
		], [
			'type' => 'header',
			'content' => 'Content-Length: 25'
		], [
			'type' => 'body',
			'content' => 'Does not exist - sorry...'
		]]);
	}

	public function testRedirectReply() {
		$driver = new MockDriver();
		$reply =& $driver->useReply();
		$trn =& $driver->useReplyTransform();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource')
			->setMime('text/plain')
			->setHeader('X-Header-1', 'foo');

		try {
			$trn
				->setTarget($driver)
				->process();

			throw new \Exception();
		}
		catch (\Exception $ex) {
			$this->assertInstanceOf('\lola\io\IReplySentException', $ex);
		}

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'HTTP/1.1 302 Found'
		], [
			'type' => 'header',
			'content' => 'Content-Type: text/plain;charset=utf-8'
		], [
			'type' => 'header',
			'content' => 'X-Header-1: foo'
		], [
			'type' => 'header',
			'content' => 'Location: /path/to/resource'
		], [
			'type' => 'header',
			'content' => 'Content-Length: 30'
		], [
			'type' => 'body',
			'content' => '302 - Found: /path/to/resource'
		]]);
	}

	public function testCookieReply() {
		$driver = new MockDriver();
		$reply =& $driver->useReply();
		$trn =& $driver->useReplyTransform();

		$reply
			->setCode('404')
			->setMime('text/plain')
			->setHeader('X-Header-1', 'foo')
			->setBody('Does not exist - sorry...')
			->useCookies()
			->set('a', 'bar', 1234)
			->set('b', 'baz', 4321);

		try {
			$trn
				->setTarget($driver)
				->process();

			throw new \Exception();
		}
		catch (Exception $ex) {
			$this->assertInstanceOf('\lola\io\IReplySentException', $ex);
		}

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'HTTP/1.1 404 Not Found'
		], [
			'type' => 'header',
			'content' => 'Content-Type: text/plain;charset=utf-8'
		], [
			'type' => 'header',
			'content' => 'X-Header-1: foo'
		], [
			'type' => 'cookie',
			'name' => 'a',
			'value' => 'bar',
			'expires' => 1234
		], [
			'type' => 'cookie',
			'name' => 'b',
			'value' => 'baz',
			'expires' => 4321
		], [
			'type' => 'header',
			'content' => 'Content-Length: 25'
		], [
			'type' => 'body',
			'content' => 'Does not exist - sorry...'
		]]);
	}

	public function testCookieRedirectReply() {
		$driver = new MockDriver();
		$reply =& $driver->useReply();
		$trn =& $driver->useReplyTransform();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource')
			->setMime('text/plain')
			->setHeader('X-Header-1', 'foo')
			->useCookies()
			->set('a', 'bar', 1234)
			->set('b', 'baz', 4321);

		try {
			$trn
				->setTarget($driver)
				->process();
		}
		catch (Exception $ex) {
			$this->assertInstanceOf('\lola\io\IReplySentException', $ex);
		}

		$queue = $driver
			->useReplyResource()
			->popQueue();

		$this->assertEquals($queue, [[
			'type' => 'header',
			'content' => 'HTTP/1.1 302 Found'
		], [
			'type' => 'header',
			'content' => 'Content-Type: text/plain;charset=utf-8',
		], [
			'type' => 'header',
			'content' => 'X-Header-1: foo'
		], [
			'type' => 'cookie',
			'name' => 'a',
			'value' => 'bar',
			'expires' => 1234
		], [
			'type' => 'cookie',
			'name' => 'b',
			'value' => 'baz',
			'expires' => 4321
		], [
			'type' => 'header',
			'content' => 'Location: /path/to/resource'
		], [
			'type' => 'header',
			'content' => 'Content-Length: 30'
		], [
			'type' => 'body',
			'content' => '302 - Found: /path/to/resource'
		]]);
	}
}
