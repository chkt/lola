<?php

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\io\http\HttpReplyResource;



class HttpReplyResourceTest
extends TestCase
{
	use PHPMock;



	public function testSendHeader() {
		$header = $this->getFunctionMock('\lola\io\http', 'header');
		$header
			->expects($this->any())
			->willReturnCallback(function(...$args) {
				$this->assertEquals(count($args), 1);
				$this->assertEquals(is_string($args[0]), true);
			});

		$reply = new HttpReplyResource();

		$reply->sendHeader('X-Some-Header: foo');
		$reply->sendHeader('X-Random-Header: bar');

		$this->assertEquals($reply->sendHeader('X-Men: baz'), $reply);
	}

	public function testSendCookie() {
		$cookie = $this->getFunctionMock('\lola\io\http', 'setCookie');
		$cookie
			->expects($this->any())
			->willReturnCallback(function(...$args) {
				$this->assertEquals(count($args), 3);
				$this->assertEquals(is_string($args[0]), true);
				$this->assertEquals(is_string($args[1]), true);
				$this->assertEquals(is_int($args[2]), true);
			});

		$reply = new HttpReplyResource();

		$reply->sendCookie('a', 'foo', time() + 1000);
		$reply->sendCookie('b', '', 0);

		$this->assertEquals($reply->sendCookie('c', 'bar', time() + 1000), $reply);
	}

	public function testSendBody() {
		$out = '';

		$this
			->getFunctionMock('\lola\io\http', 'fopen')
			->expects($this->any())
			->with(
				$this->equalTo('php://output'),
				$this->equalTo('r+')
			)
			->willReturn(1);

		$this
			->getFunctionMock('\lola\io\http', 'fwrite')
			->expects($this->any())
			->with(
				$this->equalTo(1),
				$this->isType('string')
			)
			->willReturnCallback(function($handle, $content) use (& $out) {
				$out .= $content;
			});

		$this
			->getFunctionMock('\lola\io\http', 'fclose')
			->expects($this->any())
			->with($this->equalTo(1))
			->willReturn(true);

		$reply = new HttpReplyResource();

		$reply
			->sendBody('foo')
			->sendBody('bar');

		$this->assertEquals('foobar', $out);
	}
}
