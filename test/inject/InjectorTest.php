<?php

namespace test\inject;

use PHPUnit\Framework\TestCase;

use test\inject\MockInjectable;
use test\inject\MockFactory;
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

	public function testProduceCore() {
		$locator = $this->_mockLocator();
		$injector = new Injector($locator);

		$items = $injector
			->produce(MockInjectable::class, [
				'injector:',
				'locator:'
			])
			->getArgs();

		$this->assertCount(2, $items);
		$this->assertEquals($injector, $items[0]);
		$this->assertEquals($locator, $items[1]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_INJECTOR
			], [
				'type' => Injector::TYPE_LOCATOR
			]])
			->getArgs();

		$this->assertCount(2, $items);
		$this->assertEquals($injector, $items[0]);
		$this->assertEquals($locator, $items[1]);
	}

	public function testProduceLocateable() {
		$locator = $this->_mockLocator();
		$injector = new Injector($locator);

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

		$injector = new Injector($locator);

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

	public function testProduceResolvable() {
		$locator = $this->_mockLocator();
		$injector = new Injector($locator, [
			'foo' => 'bar',
			'baz' => 'quux'
		]);

		$items = $injector
			->produce(MockInjectable::class, [
				'resolve:foo',
				'resolve:baz'
			])
			->getArgs();

		$this->assertCount(2, $items);
		$this->assertEquals('bar', $items[0]);
		$this->assertEquals('quux', $items[1]);

		$items = $injector
			->produce(MockInjectable::class, [[
				'type' => Injector::TYPE_RESOLVE,
				'location' => 'foo'
			], [
				'type' => Injector::TYPE_RESOLVE,
				'location' => 'baz'
			]])
			->getArgs();

		$this->assertCount(2, $items);
		$this->assertEquals('bar', $items[0]);
		$this->assertEquals('quux', $items[1]);
	}

	public function testProduceArgument() {
		$locator = $this->_mockLocator();
		$injector = new Injector($locator);

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
		$injector = new Injector($locator);

		$items = $injector->process(function(...$args) {
			return $args;
		}, [ 'injector:' ]);

		$this->assertCount(1, $items);
		$this->assertEquals($injector, $items[0]);
	}
}
