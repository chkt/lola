<?php

namespace test\route;

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\provide\ILocator;
use lola\route\Route;
use lola\ctrl\AController;



final class RouteTest
extends TestCase
{

	private function _mockController() : AController {
		$ins = $this
			->getMockBuilder(AController::class)
			->setMethods([ 'enter' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('enter')
			->with(
				$this->isType('string'),
				$this->isInstanceOf(Route::class)
			)
			->willReturnCallback(function(string $name, Route $route) use ($ins) {
				$route->setVar('entered', $name);

				return $ins;
			});

		return $ins;
	}

	private function _mockLocator() : ILocator {
		$ins = $this
			->getMockBuilder(ILocator::class)
			->disableOriginalConstructor()
			->getMock();

		$ins
			->expects($this->any())
			->method('locate')
			->with($this->isType('string'))
			->willReturnCallback(function(string $entity) {
				$this->assertEquals('controller:foo', $entity);

				return $this->_mockController();
			});

		return $ins;
	}

	private function _produceRoute(array $config) : Route {
		$locator = $this->_mockLocator();

		return new Route($locator, $config);
	}

	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$route = $this->_produceRoute([]);

		$this->assertInstanceOf(\lola\ctrl\IControllerState::class, $route);
		$this->assertInstanceOf(IInjectable::class, $route);
	}

	public function testDependencyConfig() {
		$config = [
			'foo' => 1,
			'bar' => 2
		];

		$this->assertEquals([
			'locator:',
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $config
			]
		], Route::getDependencyConfig($this->_produceAccessor($config)));
	}


	public function testSetCtrl() {
		$route = $this->_produceRoute([ 'ctrl' => 'foo' ]);

		$this->assertEquals('foo', $route->getCtrl());
		$this->assertEquals($route, $route->setCtrl('bar'));
		$this->assertEquals('bar', $route->getCtrl());
	}

	public function testEnter() {
		$route = $this->_produceRoute([
			'ctrl' => 'foo',
			'action' => 'bar'
		]);

		$this->assertNull($route->enter());
		$this->assertEquals('bar', $route->getVar('entered'));
	}

	public function testEnter_callable() {
		$route = $this->_produceRoute([
			'ctrl' => 'foo',
			'action' => 'bar'
		]);

		$intercept = false;

		$this->assertNull($route->enter(function(AController& $ctrl) use (& $intercept) {
			$intercept = true;
		}));
		$this->assertEquals('bar', $route->getVar('entered'));
		$this->assertTrue($intercept);
	}
}
