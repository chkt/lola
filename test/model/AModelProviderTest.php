<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use eve\common\access\TraversableAccessor;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\ILocator;
use lola\model\IModel;
use lola\model\AModelProvider;
use lola\service\IService;
use lola\service\IGetModelService;



final class AModelProviderTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockProvider(ILocator $locator = null, ITraversableAccessor $config = null) {
		if (is_null($locator)) $locator = $this->_mockInterface(ILocator::class);
		if (is_null($config)) $config = $this->_mockInterface(ITraversableAccessor::class);

		$ins = $this
			->getMockBuilder(AModelProvider::class)
			->setConstructorArgs([$locator, $config])
			->getMockForAbstractClass();

		return $ins;
	}


	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$provider = $this->_mockProvider();

		$this->assertInstanceOf(IProvider::class, $provider);
	}

	public function testDependencyConfig() {
		$config = $this->_produceAccessor([]);

		$this->assertEquals([
			'locator:',
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $config
			]
		], AModelProvider::getDependencyConfig($config));
	}


	public function testHasItem() {
		$provider = $this->_mockProvider(null, $this->_produceAccessor([
			'foo' => []
		]));

		$this->assertTrue($provider->hasKey('foo'));
		$this->assertFalse($provider->hasKey('bar'));
	}

	public function testGetItem() {
		$query = [ 'baz' => 'quux '];
		$model = $this->_mockInterface(IModel::class);

		$service = $this->_mockInterface(IGetModelService::class);

		$service
			->method('getModel')
			->with($this->equalTo($query))
			->willReturn($model);

		$serviceProvider = $this->_mockInterface(IProvider::class);

		$serviceProvider
			->method('getItem')
			->with($this->equalTo('bar'))
			->willReturn($service);


		$locator = $this->_mockInterface(ILocator::class);

		$locator
			->method('getItem')
			->with($this->equalTo('service'))
			->willReturn($serviceProvider);

		$modelProvider = $this->_mockProvider($locator, $this->_produceAccessor([
			'foo' => [
				'service' => 'bar',
				'query' => $query
			]
		]));

		$this->assertSame($model, $modelProvider->getItem('foo'));
	}

	public function testGetItem_noKey() {
		$provider = $this->_mockProvider();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV not providable "foo"');

		$provider->getItem('foo');
	}

	public function testGetItem_noService() {
		$provider = $this->_mockProvider(null, $this->_produceAccessor([
			'foo' => []
		]));

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed config "foo"');

		$provider->getItem('foo');
	}

	public function testGetItem_invalidService() {
		$provider = $this->_mockProvider(null, $this->_produceAccessor([
			'foo' => [ 'service' => 1 ]
		]));

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed config "foo"');

		$provider->getItem('foo');
	}

	public function testGetItem_noQuery() {
		$provider = $this->_mockProvider(null, $this->_produceAccessor([
			'foo' => [ 'service' => 'foo' ]
		]));

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed config "foo"');

		$provider->getItem('foo');
	}

	public function testGetItem_invalidQuery() {
		$provider = $this->_mockProvider(null, $this->_produceAccessor([
			'foo' => [ 'service' => 'foo',  'query' => 1 ]
		]));

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed config "foo"');

		$provider->getItem('foo');
	}

	public function testGetItem_badService() {
		$service = $this->_mockInterface(IService::class);

		$serviceProvider = $this->_mockInterface(IProvider::class);

		$serviceProvider
			->method('getItem')
			->with($this->equalTo('bar'))
			->willReturn($service);

		$locator = $this->_mockInterface(ILocator::class);

		$locator
			->method('getItem')
			->with($this->equalTo('service'))
			->willReturn($serviceProvider);

		$modelProvider = $this->_mockProvider($locator, $this->_produceAccessor([
			'foo' => [ 'service' => 'bar', 'query' => [] ]
		]));

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV no model service "foo"');

		$modelProvider->getItem('foo');
	}
}
