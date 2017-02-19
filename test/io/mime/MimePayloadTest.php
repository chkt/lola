<?php

require_once('test/io/http/MockDriver.php');

use PHPUnit\Framework\TestCase;

use lola\io\mime\MimePayload;

use lola\io\mime\IMimeContainer;
use lola\io\mime\IMimeConfig;



final class MimePayloadTest
extends TestCase
{

	private function _mockConfig() {
		$config = $this
			->getMockBuilder(IMimeConfig::class)
			->getMock();

		$config
			->expects($this->any())
			->method('produceMimeParser')
			->with($this->isType('string'))
			->willReturnCallback(function (string $mime) {
				switch ($mime) {
					case IMimeConfig::MIME_JSON : return new \lola\io\mime\parser\JSONMimeParser();
					case IMimeConfig::MIME_FORM : return new \lola\io\mime\parser\FormMimeParser();
					default : throw new \ErrorException();
				}
			});

		return $config;
	}


	public function testIsValid() {
		$content = '';
		$mime = IMimeConfig::MIME_JSON;

		$container = $this
			->getMockBuilder(IMimeContainer::class)
			->getMock();

		$container
			->expects($this->any())
			->method('getBody')
			->with()
			->willReturnReference($content);

		$container
			->expects($this->any())
			->method('getMime')
			->with()
			->willReturnReference($mime);

		$config = $this->_mockConfig();

		$payload = new MimePayload($container, $config);

		$this->assertFalse($payload->isValid());

		$content = '{"foo":"bar"}';

		$this->assertTrue($payload->isValid());

		$content = "foo=bar";

		$this->assertFalse($payload->isValid());

		$mime = IMimeConfig::MIME_FORM;

		$this->assertTrue($payload->isValid());
	}

	public function testGet() {
		$content = 'foo=bar&baz=quux';
		$mime = IMimeConfig::MIME_FORM;

		$container = $this
			->getMockBuilder(IMimeContainer::class)
			->getMock();

		$container
			->expects($this->any())
			->method('getBody')
			->with()
			->willReturnReference($content);

		$container
			->expects($this->any())
			->method('getMime')
			->with()
			->willReturnReference($mime);

		$config = $this->_mockConfig();

		$payload = new MimePayload($container, $config);

		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $payload->get());

		$content = '{"foo":"bar"}';
		$mime = IMimeConfig::MIME_JSON;

		$this->assertEquals([
			'foo' => 'bar'
		], $payload->get());
	}

	public function testSet() {
		$content = '';
		$mime = IMimeConfig::MIME_FORM;

		$container = $this
			->getMockBuilder(IMimeContainer::class)
			->getMock();

		$container
			->expects($this->any())
			->method('setBody')
			->with($this->isType('string'))
			->willReturnCallback(function(string $body) use (& $content, $container) {
				$content = $body;

				return $container;
			});

		$container
			->expects($this->any())
			->method('getMime')
			->with()
			->willReturnReference($mime);

		$config = $this->_mockConfig();

		$payload = new MimePayload($container, $config);

		$payload->set([
			'foo' => 'bar',
			'baz' => 'quux'
		]);

		$this->assertEquals('foo=bar&baz=quux', $content);

		$mime = IMimeConfig::MIME_JSON;

		$payload->set([
			'foo' => 'bar'
		]);

		$this->assertEquals('{"foo":"bar"}', $content);
	}
}
