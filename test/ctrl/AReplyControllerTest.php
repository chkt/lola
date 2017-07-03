<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpRequest;
use lola\io\http\IHttpReply;
use lola\io\http\IHttpDriver;
use lola\ctrl\AReplyController;



class AReplyControllerTest
extends TestCase
{

	private function _mockDriver() {
		$request = $this
			->getMockBuilder(IHttpRequest::class)
			->getMock();

		$reply = $this
			->getMockBuilder(IHttpReply::class)
			->getMock();

		$driver = $this
			->getMockBuilder(IHttpDriver::class)
			->getMock();

		$driver
			->expects($this->any())
			->method('useRequest')
			->with()
			->willReturnReference($request);

		$driver
			->expects($this->any())
			->method('useReply')
			->with()
			->willReturnReference($reply);

		return $driver;
	}

	private function _mockController(IHttpDriver $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();


		return $this
			->getMockBuilder(AReplyController::class)
			->setConstructorArgs([ & $driver ])
			->getMockForAbstractClass();
	}


	public function testUseDriver() {
		$controller = $this->_mockController();

		$this->assertInstanceOf(IHttpDriver::class, $controller->useDriver());
	}

	public function testSetDriver() {
		$driver = $this->_mockDriver();
		$controller = $this->_mockController();

		$this->assertNotSame($driver, $controller->useDriver());
		$this->assertSame($controller, $controller->setDriver($driver));
		$this->assertSame($driver, $controller->useDriver());
	}

	public function testUseRequest() {
		$driver = $this->_mockDriver();
		$controller = $this->_mockController();

		$this->assertInstanceOf(IHttpRequest::class, $controller->useRequest());
		$this->assertSame($controller, $controller->setDriver($driver));
		$this->assertSame($driver->useRequest(), $controller->useRequest());
	}

	public function testUseReply() {
		$driver = $this->_mockDriver();
		$controller = $this->_mockController();

		$this->assertInstanceOf(IHttpReply::class, $controller->useReply());
		$this->assertSame($controller, $controller->setDriver($driver));
		$this->assertSame($driver->useReply(), $controller->useReply());
	}

	public function testUseRequestTransform() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\ctrl\ControllerTransform', $controller->useRequestTransform());
	}

	public function testSetRequestTransform() {
		$controller = $this->_mockController();
		$transform = new \lola\ctrl\ControllerTransform();

		$this->assertEquals($controller, $controller->setRequestTransform($transform));
		$this->assertEquals($transform, $controller->useRequestTransform());
	}

	public function testUseReplyTransform() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\ctrl\ControllerTransform', $controller->useReplyTransform());
	}

	public function testSetReplyTransform() {
		$controller = $this->_mockController();
		$transform = new \lola\ctrl\ControllerTransform();

		$this->assertEquals($controller, $controller->setReplyTransform($transform));
		$this->assertEquals($transform, $controller->useReplyTransform());
	}
}
