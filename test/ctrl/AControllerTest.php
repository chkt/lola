<?php

use PHPUnit\Framework\TestCase;

use eve\access\TraversableAccessor;
use eve\inject\IInjectable;
use lola\route\Route;
use lola\ctrl\AController;



final class AControllerTest
extends TestCase
{

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

		$this->assertInstanceOf(IInjectable::class, $ctrl);
	}

	public function testDependencyConfig() {
		$this->assertEquals([], AController::getDependencyConfig($this->_produceAccessor()));
	}


	public function testHasAction() {
		$ctrl = $this->_mockController(['fooAction']);

		$this->assertTrue($ctrl->hasAction('foo'));
		$this->assertTrue($ctrl->hasAction('Foo'));
		$this->assertFalse($ctrl->hasAction('bar'));
	}

	public function testIsEnterable() {
		$route = $this
			->getMockBuilder(Route::class)
			->disableOriginalConstructor()
			->getMock();

		$route
			->expects($this->at(0))
			->method('getAction')
			->willReturn('foo');

		$route
			->expects($this->at(1))
			->method('getAction')
			->willReturn('Foo');

		$route
			->expects($this->at(2))
			->method('getAction')
			->willReturn('bar');

		$ctrl = $this->_mockController(['fooAction']);

		$this->assertTrue($ctrl->isEnterable($route));
		$this->assertTrue($ctrl->isEnterable($route));
		$this->assertFalse($ctrl->isEnterable($route));
	}

	public function testEnter() {
		$route = $this
			->getMockBuilder(Route::class)
			->disableOriginalConstructor()
			->getMock();

		$route
			->expects($this->at(0))
			->method('getAction')
			->willReturn('foo');

		$route
			->expects($this->at(1))
			->method('getAction')
			->willReturn('bar');

		$ctrl = $this->_mockController(['fooAction', 'defaultAction']);

		$ctrl
			->expects($this->once())
			->method('fooAction')
			->with($this->isInstanceOf(Route::class))
			->willReturn('fooResult');

		$ctrl
			->expects($this->once())
			->method('defaultAction')
			->with($this->isInstanceOf(Route::class))
			->willReturn('defaultResult');

		$this->assertEquals('fooResult', $ctrl->enter($route));
		$this->assertEquals('defaultResult', $ctrl->enter($route));
	}

	public function test_reenter() {
		$route = $this
			->getMockBuilder(Route::class)
			->disableOriginalConstructor()
			->getMock();

		$route
			->expects($this->at(0))
			->method('setAction')
			->with($this->equalTo('bar'))
			->willReturn($route);

		$ctrl = $this->_mockController(['barAction']);

		$ctrl
			->expects($this->once())
			->method('barAction')
			->with($this->isInstanceOf(Route::class))
			->willReturn('barResult');

		$reenter = new \ReflectionMethod($ctrl, '_reenter');
		$reenter->setAccessible(true);

		$this->assertEquals('barResult', $reenter->invokeArgs($ctrl, [ 'bar', & $route ]));

		$this->expectException(\ErrorException::class);
		$reenter->invokeArgs($ctrl,  [ 'baz', & $route ]);
	}
}
