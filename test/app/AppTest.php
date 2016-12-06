<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use lola\app\App;



final class AppTest
extends TestCase
{

	public function testHasProperty() {
		$app = new App([
			'foo' => 1,
			'bar' => 2
		]);

		$this->assertTrue($app->hasProperty('foo'));
		$this->assertTrue($app->hasProperty('bar'));
		$this->assertFalse($app->hasProperty('baz'));
	}

	public function testGetProperty() {
		$app = new App([
			'foo' => 1,
			'bar' => 2
		]);

		$this->assertEquals(1, $app->getProperty('foo'));
		$this->assertEquals(2, $app->getProperty('bar'));

		$this->expectException(\ErrorException::class);

		$app->getProperty('baz');
	}
}
