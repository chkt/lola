<?php

require('test/io/http/MockDriver.php');

use PHPUnit\Framework\TestCase;
use lola\input\Form;

use lola\io\http\HttpConfig;
use test\io\http\MockDriver;



final class FormTest
extends TestCase
{

	public function testValidate() {
		$form = new Form('form', [[
			'name' => 'foo',
			'value' => 'bar',
			'validate' => function($now, $was) {
				error_log($now . ' ' . $was);

				$this->assertEquals($was, 'bar');
				$this->assertEquals($now, 'baz');

				return 0;
			}
		], [
			'name' => 'submit',
			'type' => 'submit',
			'value' => 'submit'
		]]);

		$driver = new MockDriver();
		$request =& $driver->useRequest();

		$request
			->setMime(HttpConfig::MIME_FORM)
			->setBody('foo=baz&submit=submit');

		$this->assertTrue($form->validate($request));
	}
}
