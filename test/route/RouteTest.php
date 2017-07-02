<?php

namespace test\route;

use PHPUnit\Framework\TestCase;

use lola\prov\ProviderProvider;
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
			->with($this->isInstanceOf(Route::class))
			->willReturnCallback(function(Route& $route) {
				$route->setVar('entered', true);
			});

		return $ins;
	}

	private function _mockLocator() : ProviderProvider {
		$ins = $this
			->getMockBuilder(ProviderProvider::class)
			->disableOriginalConstructor()
			->getMock();

		$ins
			->expects($this->any())
			->method('locate')
			->with()
			->willReturnCallback(function() {
				return $this->_mockController();
			});

		return $ins;
	}

	private function _produceRoute(array $config) : Route {
		$locator = $this->_mockLocator();

		return new Route($locator, $config);
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
		$this->assertTrue($route->getVar('entered'));
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
		$this->assertTrue($route->getVar('entered'));
		$this->assertTrue($intercept);
	}
}
