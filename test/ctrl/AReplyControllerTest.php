<?php

use PHPUnit\Framework\TestCase;



class AReplyControllerTest
extends TestCase
{

	public function testUseDriver() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');

		$this->assertInstanceOf('\lola\io\http\IHttpDriver', $controller->useDriver());
	}

	public function testSetDriver() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');
		$driver = new \lola\io\http\HttpDriver();

		$this->assertEquals($controller, $controller->setDriver($driver));
		$this->assertEquals($driver, $controller->useDriver());
	}

	public function testUseRequest() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');

		$this->assertInstanceOf('\lola\io\http\IHttpRequest', $controller->useRequest());

		$driver = new \lola\io\http\HttpDriver();
		$controller->setDriver($driver);

		$this->assertEquals($driver->useRequest(), $controller->useRequest());
	}

	public function testUseReply() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');

		$this->assertInstanceOf('\lola\io\http\IHttpReply', $controller->useReply());

		$driver = new \lola\io\http\HttpDriver();
		$controller->setDriver($driver);

		$this->assertEquals($driver->useReply(), $controller->useReply());
	}
}
