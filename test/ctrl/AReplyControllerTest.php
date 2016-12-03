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

	public function testUseRequestTransform() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');

		$this->assertInstanceOf('\lola\ctrl\ControllerTransform', $controller->useRequestTransform());
	}

	public function testSetRequestTransform() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');
		$transform = new \lola\ctrl\ControllerTransform();

		$this->assertEquals($controller, $controller->setRequestTransform($transform));
		$this->assertEquals($transform, $controller->useRequestTransform());
	}

	public function testUseReplyTransform() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');

		$this->assertInstanceOf('\lola\ctrl\ControllerTransform', $controller->useReplyTransform());
	}

	public function testSetReplyTransform() {
		$controller = $this->getMockForAbstractClass('\lola\ctrl\AReplyController');
		$transform = new \lola\ctrl\ControllerTransform();

		$this->assertEquals($controller, $controller->setReplyTransform($transform));
		$this->assertEquals($transform, $controller->useReplyTransform());
	}
}
