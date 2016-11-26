<?php

require_once('test/io/http/MockDriver.php');

use PHPUnit\Framework\TestCase;

use test\io\http\MockDriver;
use lola\io\http\HttpConfig;



final class HttpPayloadTest
extends TestCase
{

	public function testIsValid() {
		$driver = new MockDriver();
		$payload =& $driver->usePayload();

		$this->assertFalse($payload->isValid());

		$driver->useRequest()->setBody('{"foo":"bar"}');

		$this->assertFalse($payload->isValid());

		$driver->useRequest()->setMime(HttpConfig::MIME_JSON);

		$this->assertTrue($payload->isValid());

		$driver
			->useRequest()
			->setMime(HttpConfig::MIME_FORM)
			->setBody('foo=bar');

		$this->assertTrue($payload->isValid());
	}

	public function testGet() {
		$driver = new MockDriver();
		$payload =& $driver->usePayload();

		$driver
			->useRequest()
			->setMime(HttpConfig::MIME_FORM)
			->setBody('foo=bar&baz=quux');

		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $payload->get());

		$driver
			->useRequest()
			->setMime(HttpConfig::MIME_JSON)
			->setBody('{"foo":"bar","baz":"quux"}');

		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $payload->get());
	}

	public function testSet() {
		$driver = new MockDriver();
		$request =& $driver->useRequest();
		$payload =& $driver->usePayload();

		$request->setMime(HttpConfig::MIME_FORM);

		$payload->set([
			'foo' => 'bar',
			'baz' => 'quux'
		]);

		$this->assertEquals('foo=bar&baz=quux', $request->getBody());

		$request->setMime(HttpConfig::MIME_JSON);

		$payload->set([
			'foo' => 'bar',
			'baz' => 'quux'
		]);

		$this->assertEquals('{"foo":"bar","baz":"quux"}', $request->getBody());
	}
}
