<?php

namespace test\route;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\route\Router;
use lola\route\CSVRouter;



final class CSVRouterTest
extends TestCase
{

	use PHPMock;


	private function _mockInjector() {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		return $injector;
	}

	private function _mockRouter(IInjector $injector = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$this
			->getFunctionMock('\\lola\\route', 'fopen')
			->expects($this->once())
			->with(
				$this->equalTo('table.csv'),
				$this->equalTo('r')
			)
			->willReturn(1);

		$fgetcsv = $this->getFunctionMock('\\lola\\route', 'fgetcsv');

		$fgetcsv
			->expects($this->at(0))
			->with($this->equalTo(1))
			->willReturn(['path', 'ctrl', 'action', 'view', 'tree', 'data']);

		$fgetcsv
			->expects($this->at(1))
			->with($this->equalTo(1))
			->willReturn(false);

		$this
			->getFunctionMock('\\lola\route', 'fclose')
			->expects($this->once())
			->with($this->equalTo(1))
			->willReturn(true);

		return new CSVRouter($injector, [
			'path' => 'table.csv'
		]);
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$router = $this->_mockRouter();

		$this->assertInstanceOf(Router::class, $router);
	}

	public function testDependencyConfig() {
		$data = [
			'path' => 'path/to/resource.csv'
		];

		$this->assertEquals([
			'injector:',
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $data
			]
		], CSVRouter::getDependencyConfig($this->_produceAccessor($data)));
	}
}
