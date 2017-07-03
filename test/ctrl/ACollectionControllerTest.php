<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpDriver;
use lola\ctrl\ACollectionController;



final class ACollectionControllerTest
extends TestCase
{

	private function _mockController() {
		$driver = $this
			->getMockBuilder(IHttpDriver::class)
			->getMock();

		return $this
			->getMockBuilder(ACollectionController::class)
			->setConstructorArgs([& $driver ]);
		}

	private function _getController($code, $mime) {
		$reply = $this
			->getMockBuilder('\lola\io\http\HttpReply')
			->disableOriginalConstructor()
			->setMethods([ 'setCode', 'setMime', 'send' ])
			->getMock();

		$reply
			->expects($this->at(0))
			->method('setCode')
			->with($this->equalTo($code))
			->willReturn($reply);

		$reply
			->expects($this->at(1))
			->method('setMime')
			->with($this->equalTo($mime))
			->willReturn($reply);

		$reply
			->expects($this->at(2))
			->method('send')
			->willReturn($reply);

		$ctrl = $this
			->_mockController()
			->setMethods([ 'useReply' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useReply')
			->willReturn($reply);

		return $ctrl;
	}

	private function _getRoute() {
		return $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->getMock();
	}


	public function test__construct() {
		$ctrl = $this
			->_mockController()
			->getMockForAbstractClass();

		$this->assertInstanceOf('\lola\ctrl\RESTCollectionRequestTransform', $ctrl->useRequestTransform());
		$this->assertInstanceOf('\lola\ctrl\RESTReplyTransform', $ctrl->useReplyTransform());
	}

	public function testCreateAction() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setCtrl', 'setAction', 'enter' ])
			->getMock();

		$route
			->expects($this->at(0))
			->method('setCtrl')
			->with($this->equalTo('foo'))
			->willReturn($route);

		$route
			->expects($this->at(1))
			->method('setAction')
			->with($this->equalTo('resolve'))
			->willReturn($route);

		$route
			->expects($this->at(2))
			->method('enter')
			->willReturn($route);

		$ctrl = $this
			->_mockController()
			->setMethods([ 'unavailableAction' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->at(0))
			->method('unavailableAction')
			->willReturn($ctrl);

		$method = new \ReflectionMethod($ctrl, 'createAction');
		$method->setAccessible(true);

		$method->invoke($ctrl, $route);

		$prop = new \ReflectionProperty($ctrl, '_itemController');
		$prop->setAccessible(true);
		$prop->setValue($ctrl, 'foo');

		$method->invoke($ctrl, $route);
	}

	public function testUnavailableAction() {
		$this
			->_getController('400', 'text/plain')
			->unavailableAction($this->_getRoute());
	}

	public function testUnauthenticatedAction() {
		$this
			->_getController('403', 'text/plain')
			->unauthenticatedAction($this->_getRoute());
	}
}
