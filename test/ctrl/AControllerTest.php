<?php

use PHPUnit\Framework\TestCase;



final class AControllerTest
extends TestCase
{

	public function testHasAction() {
		$ctrl = $this
			->getMockBuilder('\lola\ctrl\AController')
			->setMethods([ 'fooAction' ])
			->getMockForAbstractClass();

		$this->assertTrue($ctrl->hasAction('foo'));
		$this->assertTrue($ctrl->hasAction('Foo'));
		$this->assertFalse($ctrl->hasAction('bar'));
	}

	public function testIsEnterable() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
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

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\AController')
			->setMethods([ 'fooAction' ])
			->getMockForAbstractClass();

		$this->assertTrue($ctrl->isEnterable($route));
		$this->assertTrue($ctrl->isEnterable($route));
		$this->assertFalse($ctrl->isEnterable($route));
	}

	public function testEnter() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
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

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\AController')
			->setMethods([ 'fooAction', 'defaultAction' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('fooAction')
			->with($this->isInstanceOf('\lola\route\Route'))
			->willReturn('fooResult');

		$ctrl
			->expects($this->once())
			->method('defaultAction')
			->with($this->isInstanceOf('\lola\route\Route'))
			->willReturn('defaultResult');

		$this->assertEquals('fooResult', $ctrl->enter($route));
		$this->assertEquals('defaultResult', $ctrl->enter($route));
	}

	public function test_reenter() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->getMock();

		$route
			->expects($this->at(0))
			->method('setAction')
			->with($this->equalTo('bar'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\AController')
			->setMethods([ 'barAction' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('barAction')
			->with($this->isInstanceOf('\lola\route\Route'))
			->willReturn('barResult');

		$reenter = new \ReflectionMethod($ctrl, '_reenter');
		$reenter->setAccessible(true);

		$this->assertEquals('barResult', $reenter->invokeArgs($ctrl, [ 'bar', & $route ]));

		$this->expectException('\ErrorException');
		$reenter->invokeArgs($ctrl,  [ 'baz', & $route ]);
	}
}
