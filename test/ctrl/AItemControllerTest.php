<?php

use PHPUnit\Framework\TestCase;



final class AItemControllerTest
extends TestCase
{
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
			->getMockBuilder('\lola\ctrl\AItemController')
			->setMethods([ 'useReply' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useReply')
			->willReturn($reply);

		return $ctrl;
	}

	private function _getRoute() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->getMock();

		return $route;
	}


	public function test__construct() {
		$ctrl = $this->getMockForAbstractClass('\lola\ctrl\AItemController');

		$this->assertInstanceOf('\lola\ctrl\RESTItemRequestTransform', $ctrl->useRequestTransform());
		$this->assertInstanceOf('\lola\ctrl\RESTReplyTransform', $ctrl->useReplyTransform());
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
