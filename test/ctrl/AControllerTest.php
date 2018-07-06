<?php

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectableIdentity;
use lola\ctrl\IControllerState;
use lola\ctrl\AController;



final class AControllerTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$res = $this
			->getMockBuilder($qname)
			->getMock();

		return $res;
	}

	private function _mockControllerState(array& $vars = []) {
		$state = $this->_mockInterface(IControllerState::class);

		$state
			->method('setVars')
			->with($this->isType('array'))
			->willReturnCallback(function(array $items) use (& $vars, $state) {
				$vars = array_merge($vars, $items);

				return $state;
			});

		return $state;
	}

	private function _mockController(array $methods = []) {
		$ctrl = $this
			->getMockBuilder(AController::class)
			->setMethods($methods)
			->getMockForAbstractClass();

		return $ctrl;
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$ctrl = $this->_mockController();

		$this->assertInstanceOf(\lola\ctrl\IController::class, $ctrl);
		$this->assertInstanceOf(\eve\inject\IInjectableIdentity::class, $ctrl);
		$this->assertInstanceOf(\eve\inject\IInjectable::class, $ctrl);
	}

	public function testDependencyConfig() {
		$this->assertEquals([], AController::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, AController::getInstanceIdentity($this->_produceAccessor()));

		$config = [ 'id' => 'foo' ];

		$this->assertEquals('foo', AController::getInstanceIdentity($this->_produceAccessor($config)));
	}


	public function testHasAction() {
		$ctrl = $this->_mockController(['fooAction']);

		$this->assertTrue($ctrl->hasAction('foo'));
		$this->assertTrue($ctrl->hasAction('Foo'));
		$this->assertFalse($ctrl->hasAction('bar'));
	}


	public function testEnter() {
		$route = $this->_mockControllerState();
		$ctrl = $this->_mockController(['fooAction']);

		$ctrl
			->method('fooAction')
			->with($this->isInstanceOf(IControllerState::class))
			->willReturn(null);

		$this->assertSame($ctrl, $ctrl->enter('foo', $route));
	}

	public function testEnter_noAction() {
		$route = $this->_mockControllerState();
		$ctrl = $this->_mockController([]);

		$this->expectException(\lola\ctrl\NoActionException::class);
		$this->expectExceptionMessage('CTR no action "foo"');

		$ctrl->enter('foo', $route);
	}

	public function testEnter_result() {
		$result = [];
		$route = $this->_mockControllerState($result);
		$ctrl = $this->_mockController([ 'fooAction' ]);

		$ctrl
			->method('fooAction')
			->with($this->isInstanceOf(IControllerState::class))
			->willReturn('bar');

		$this->assertSame($ctrl, $ctrl->enter('foo', $route));
		$this->assertEquals([ 'foo' => 'bar'], $result);
	}

	public function testEnter_resultArray() {
		$result = [];
		$route = $this->_mockControllerState($result);
		$ctrl = $this->_mockController([ 'fooAction' ]);

		$props = [ 'foo' => 1, 'bar' => 2 ];
		$ctrl
			->method('fooAction')
			->with($this->isInstanceOf(IControllerState::class))
			->willReturn($props);

		$this->assertSame($ctrl, $ctrl->enter('foo', $route));
		$this->assertEquals($props, $result);
	}
}
