<?php

namespace test\inject;

use PHPUnit\Framework\TestCase;

use test\inject\MockInjectable;
use test\inject\MockFactory;
use lola\module\Registry;
use lola\prov\ProviderProvider;
use lola\inject\Injector;



final class InjectorTest
extends TestCase
{

	private function _mockLocator() {
		$locator = $this
			->getMockBuilder(ProviderProvider::class)
			->disableOriginalConstructor()
			->setMethods([ 'locate' ])
			->getMock();

		$locator
			->expects($this->any())
			->method('locate')
			->with(
				$this->isType('string'),
				$this->isType('string')
			)
			->willReturnCallback(function($type, $location) {
				return [
					'type' => $type,
					'location' => $location
				];
			});

		return $locator;
	}

	private function _mockRegistry() {
		return $this
			->getMockBuilder(Registry::class)
			->disableOriginalConstructor()
			->getMock();
	}


	public function testProduceCore() {
		$locator = $this->_mockLocator();
		$registry = $this->_mockRegistry();
		$injector = new Injector($locator, $registry);

		$items = $injector
			->produce(MockInjectable::class, [
				'injector:',
				'locator:',
				'registry:'
			])
			->getArgs();

		$this->assertCount(3, $items);
		$this->assertEquals($injector, $items[0]);
		$this->assertEquals($locator, $items[1]);
		$this->assertEquals($registry, $items[2]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_INJECTOR
			], [
				'type' => Injector::TYPE_LOCATOR
			], [
				'type' => Injector::TYPE_REGISTRY
			]])
			->getArgs();

		$this->assertCount(3, $items);
		$this->assertEquals($injector, $items[0]);
		$this->assertEquals($locator, $items[1]);
		$this->assertEquals($registry, $items[2]);
	}

	public function testProduceLocateable() {
		$locator = $this->_mockLocator();
		$registry = $this->_mockRegistry();
		$injector = new Injector($locator, $registry);

		$items = $injector
			->produce(MockInjectable::class, [
				'controller://module/controller?id',
				'service://module/service?id',
				'environment:name'
			])
			->getArgs();

		$this->assertCount(3, $items);
		$this->assertEquals([
			'type' => 'controller',
			'location' => '//module/controller?id'
		], $items[0]);
		$this->assertEquals([
			'type' => 'service',
			'location' => '//module/service?id'
		], $items[1]);
		$this->assertEquals([
			'type' => 'environment',
			'location' => 'name'
		], $items[2]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_CONTROLLER,
				'location' => '//module/controller?id'
			], [
				'type' => Injector::TYPE_SERVICE,
				'location' => '//module/service?id'
			], [
				'type' => Injector::TYPE_ENVIRONMENT,
				'location' => 'name'
			]])
			->getArgs();

		$this->assertCount(3, $items);
		$this->assertEquals([
			'type' => 'controller',
			'location' => '//module/controller?id'
		], $items[0]);
		$this->assertEquals([
			'type' => 'service',
			'location' => '//module/service?id'
		], $items[1]);
		$this->assertEquals([
			'type' => 'environment',
			'location' => 'name'
		], $items[2]);
	}

	public function testProduceFactory() {
		$locator = $this
			->getMockBuilder(ProviderProvider::class)
			->disableOriginalConstructor()
			->setMethods([ 'locate' ])
			->getMock();

		$locator
			->expects($this->any())
			->method('locate')
			->with(
				$this->equalTo('class'),
				$this->equalTo(MockFactory::class)
			)
			->willReturnCallback(function() {
				return new MockFactory();
			});

		$registry = $this->_mockRegistry();
		$injector = new Injector($locator, $registry);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_FACTORY,
				'factory' => MockFactory::class
			]])
			->getArgs();

		$this->assertCount(1, $items);
		$this->assertEquals([], $items[0]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_FACTORY,
				'factory' => MockFactory::class,
				'config' => [ 'foo' => 'bar', 'baz' => 'quux' ]
			]])
			->getArgs();

		$this->assertCount(1, $items);
		$this->assertEquals([
			'foo' => 'bar',
			'baz' => 'quux'
		], $items[0]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_FACTORY,
				'function' => function(...$args) {
					return $args;
				}
			]])
			->getArgs();

		$this->assertCount(1, $items);
		$this->assertEquals([], $items[0]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_FACTORY,
				'function' => function(...$args) {
					return $args;
				},
				'dependencies' => [[
					'type' => Injector::TYPE_ARGUMENT,
					'data' => 'foo'
				]]
			]])
			->getArgs();

		$this->assertCount(1, $items);
		$this->assertEquals([ 'foo' ], $items[0]);
	}

	public function testProduceArgument() {
		$locator = $this->_mockLocator();
		$registry = $this->_mockRegistry();
		$injector = new Injector($locator, $registry);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_ARGUMENT,
				'data' => 'foo'
			]])
			->getArgs();

		$this->assertCount(1, $items);
		$this->assertEquals('foo', $items[0]);
	}

	public function testProcess() {
		$locator = $this->_mockLocator();
		$registry = $this->_mockRegistry();
		$injector = new Injector($locator, $registry);

		$items = $injector->process(function(...$args) {
			return $args;
		}, [ 'injector:' ]);

		$this->assertCount(1, $items);
		$this->assertEquals($injector, $items[0]);
	}
}
