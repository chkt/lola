<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use lola\ctrl\AController;
use lola\ctrl\AReplyController;
use test\io\http\MockDriver;



class AReplyControllerTest
extends TestCase
{

	private function _mockController() {
		$driver = new MockDriver();

		return $this
			->getMockBuilder(AReplyController::class)
			->setConstructorArgs([ & $driver ])
			->getMockForAbstractClass();
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$ctrl = $this->_mockController();

		$this->assertInstanceOf(AController::class, $ctrl);
	}

	public function testDependencyConfig() {
		$this->assertEquals([ 'environment:http'], AReplyController::getDependencyConfig($this->_produceAccessor()));
	}


	public function testUseDriver() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\io\http\IHttpDriver', $controller->useDriver());
	}

	public function testSetDriver() {
		$controller = $this->_mockController();
		$driver = new \lola\io\http\HttpDriver();

		$this->assertEquals($controller, $controller->setDriver($driver));
		$this->assertEquals($driver, $controller->useDriver());
	}

	public function testUseRequest() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\io\http\IHttpRequest', $controller->useRequest());

		$driver = new \lola\io\http\HttpDriver();
		$controller->setDriver($driver);

		$this->assertEquals($driver->useRequest(), $controller->useRequest());
	}

	public function testUseReply() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\io\http\IHttpReply', $controller->useReply());

		$driver = new \lola\io\http\HttpDriver();
		$controller->setDriver($driver);

		$this->assertEquals($driver->useReply(), $controller->useReply());
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
