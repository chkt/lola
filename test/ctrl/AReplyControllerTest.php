<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;
use eve\common\access\TraversableAccessor;
use lola\ctrl\IControllerState;
use lola\ctrl\ControllerTransform;
use lola\ctrl\AReplyController;
use test\io\http\MockDriver;



class AReplyControllerTest
extends TestCase
{

	private function _mockControllerState() {
		$res = $this
			->getMockBuilder(IControllerState::class)
			->getMock();

		return $res;
	}

	private function _mockController(array $actions = []) {
		$driver = new MockDriver();

		return $this
			->getMockBuilder(AReplyController::class)
			->setConstructorArgs([& $driver])
			->setMethods($actions)
			->getMockForAbstractClass();
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$ctrl = $this->_mockController();

		$this->assertInstanceOf(\lola\ctrl\AController::class, $ctrl);
	}


	public function testDependencyConfig() {
		$this->assertEquals([ 'environment:io' ], AReplyController::getDependencyConfig($this->_produceAccessor()));
	}


	public function testUseRoute() {
		$controller = $this->_mockController();

		$this->assertNull($controller->useRoute());
	}

	public function testSetRoute() {
		$route = $this
			->getMockBuilder(IControllerState::class)
			->getMock();

		$controller = $this->_mockController();

		$this->assertSame($controller, $controller->setRoute($route));
		$this->assertSame($route, $controller->useRoute());
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

		$this->assertEquals($driver->getRequest(), $controller->useRequest());
	}

	public function testUseReply() {
		$controller = $this->_mockController();

		$this->assertInstanceOf('\lola\io\http\IHttpReply', $controller->useReply());

		$driver = new \lola\io\http\HttpDriver();
		$controller->setDriver($driver);

		$this->assertEquals($driver->getReply(), $controller->useReply());
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


	public function testEnter() {
		$state = $this->_mockControllerState();
		$controller = $this->_mockController([ 'fooAction', 'useReply' ]);
		$transform = $this
			->getMockBuilder(ControllerTransform::class)
			->setConstructorArgs([[
				ControllerTransform::STEP_FIRST => [
					'next' => [ ControllerTransform::STEP_SUCCESS => 'foo' ]
				],
				'foo' => [
					'transform' => 'foo',
					'next' => [ ControllerTransform::STEP_SUCCESS => ControllerTransform::STEP_END ]
				]
			]])
			->setMethods([ 'fooStep' ])
			->getMock();

		$transform
			->expects($this->exactly(2))
			->method('fooStep')
			->with($controller);

		$reply = $this
			->getMockBuilder(\lola\io\http\IHttpReply::class)
			->getMock();

		$reply
			->expects($this->exactly(1))
			->method('send');

		$controller
			->setRequestTransform($transform)
			->setReplyTransform($transform);

		$controller
			->method('fooAction')
			->with($this->equalTo($state))
			->willReturn(null);

		$controller
			->method('useReply')
			->willReturn($reply);

		$controller->enter('foo', $state);
	}
}
