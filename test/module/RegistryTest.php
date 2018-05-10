<?php

namespace test\module;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjector;
use eve\provide\ILocator;
use lola\provide\IConfigurableProvider;
use lola\module\IEntityParser;
use lola\module\IModule;
use lola\module\Registry;



final class RegistryTest
extends TestCase
{

	use PHPMock;


	private function  _mock_class_exists(bool $exists = true) {
		$this
			->getFunctionMock('\\lola\\module', 'class_exists')
			->expects($this->any())
			->with($this->isType('string'))
			->willReturn($exists);
	}


	private function _mockModule(array $config) : IModule {
		$ins = $this
			->getMockBuilder(IModule::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getModuleConfig')
			->with()
			->willReturn($config);

		return $ins;
	}

	private function _mockInjector(callable $fn = null) {
		if (is_null($fn)) $fn = function(string $qname, array $config = null) {
			return [
				'qname' => $qname,
				'config' => !is_null($config) ? $config : []
			];
		};

		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('string'), $this->anything())
			->willReturnCallback($fn);

		return $ins;
	}

	private function _mockProvider() {
		$ins = $this
			->getMockBuilder(IConfigurableProvider::class)
			->getMock();

		return $ins;
	}

	private function _mockLocator() {
		$ins = $this
			->getMockBuilder(ILocator::class)
			->getMock();

		return $ins;
	}

	private function _mockParser() {
		$ins = $this
			->getMockBuilder(IEntityParser::class)
			->getMock();

		return $ins;
	}


	private function _produceAccessor() : TraversableAccessor {
		$data = [];

		return new TraversableAccessor($data);
	}


	private function _produceRegistry(IInjector $injector = null, ILocator $locator = null, IEntityParser $parser = null) : Registry {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($locator)) $locator = $this->_mockLocator();
		if (is_null($parser)) $parser = $this->_mockParser();

		return new Registry($injector, $locator, $parser);
	}


	public function testInheritance() {
		$ins = $this->_produceRegistry();

		$this->assertInstanceOf(IInjectableIdentity::class, $ins);
		$this->assertInstanceOf(IInjectable::class, $ins);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'injector:',
			'locator:',
			'core:entityParser'
		], Registry::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, Registry::getInstanceIdentity($this->_produceAccessor()));
	}


	public function testInjectModule() {
		$ins = $this->_produceRegistry();

		$this->assertEquals($ins, $ins->injectModule('foo', []));
	}


	public function testInjectModule_dependencies() {
		$count = 0;
		$injector = $this->_mockInjector(function(string $qname) use (& $count) {
			$count += 1;

			switch ($qname) {
				case '\\foo\\Module' : return $this->_mockModule([]);
				case '\\bar\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				case '\\baz\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				case '\\quux\\Module' : return $this->_mockModule([ 'depend' => [ 'bar', 'baz' ]]);
				default : throw new \ErrorException();
			}
		});
		$registry = $this->_produceRegistry($injector);

		$this->assertEquals($registry, $registry->injectModule('bang', [ 'depend' => [ 'quux' ]]));
		$this->assertEquals(4, $count);
	}

	public function testInjectModule_errorDependencyLoop() {
		$injector = $this->_mockInjector(function(string $qname) {
			switch ($qname) {
				case '\\foo\\Module' : return $this->_mockModule([ 'depend' => [ 'baz' ]]);
				case '\\bar\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				default : throw new \ErrorException();
			}
		});
		$registry = $this->_produceRegistry($injector);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('MOD: circular dependency');

		$registry->injectModule('baz', [ 'depend' => [ 'bar' ]]);
	}


	public function testInjectModule_config() {
		$provider = $this->_mockProvider();

		$provider
			->expects($this->once())
			->method('addConfiguration')
			->with(
				$this->equalTo('bar?baz'),
				$this->isType('callable')
			)
			->willReturnSelf();

		$locator = $this->_mockLocator();

		$locator
			->expects($this->once())
			->method('getItem')
			->with($this->equalTo('fooType'))
			->willReturn($provider);

		$parser = $this->_mockParser();

		$parser
			->expects($this->once())
			->method('parse')
			->with($this->equalTo('fooType://foo/bar?baz'))
			->willReturn([
				'type' => 'fooType',
				'module' => 'foo',
				'descriptor' => 'bar?baz'
			]);

		$registry = $this->_produceRegistry(null, $locator, $parser);

		$this->assertSame($registry, $registry->injectModule('foo', [
			'config' => [
				'fooType://foo/bar?baz' => function() {}
			]
		]));
	}

	public function testInjectModule_ownReference() {
		$this
			->getFunctionMock('\\lola\\module', 'class_exists')
			->expects($this->any())
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) {
				return $qname === '\\foo\\bar\\BazBar';
			});

		$provider = $this->_mockProvider();
		$locator = $this->_mockLocator();

		$locator
			->method('getItem')
			->with($this->equalTo('bar'))
			->willReturn($provider);

		$parser = $this->_mockParser();

		$parser
			->method('parse')
			->with($this->equalTo('bar:baz'))
			->willReturn([
				'type' => 'bar',
				'module' => '',
				'descriptor' => 'baz'
			]);

		$registry = $this->_produceRegistry(null, $locator, $parser);

		$provider
			->method('addConfiguration')
			->with(
				$this->equalTo('baz'),
				$this->isType('callable')
			)
			->willReturnCallback(function(string $name, callable $fn) use ($registry, $provider) {
				$this->assertEquals(
					'\\foo\\bar\\BazBar',
					$registry->getQualifiedName('bar', 'baz')
				);

				return $provider;
			});

		$this->assertSame($registry, $registry->injectModule('foo', [
			'config' => [
				'bar:baz' => function() {}
			]
		]));
	}


	public function testGetQualifiedName_module() {
		$this->_mock_class_exists();

		$registry = $this
			->_produceRegistry()
			->injectModule('foo', [
				'locator' => [
					'barType' => [
						'path' => '/path/to/',
						'prefix' => 'Prefix',
						'postfix' => 'Postfix'
					]
				]
			]);

		$this->assertEquals('\\foo\\fooType\\ClassFooType', $registry->getQualifiedName('fooType', 'class', 'foo'));
		$this->assertEquals('\\foo\\fooType\\ClassFooType', $registry->getQualifiedName('fooType', '/class', 'foo'));
		$this->assertEquals('\\foo\\fooType\\path\\to\\ClassFooType', $registry->getQualifiedName('fooType', 'path/to/class', 'foo'));
		$this->assertEquals('\\foo\\fooType\\path\\to\\ClassFooType', $registry->getQualifiedName('fooType', '/path/to/class', 'foo'));
		$this->assertEquals('\\foo\\path\\to\\PrefixClassPostfix', $registry->getQualifiedName('barType', 'class', 'foo'));
	}

	public function testGetQualifiedName_descriptorInvalid() {
		$registry = $this
			->_produceRegistry()
			->injectModule('foo', []);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('MOD bad descriptor "type://foo/"-"path/to/class/"');

		$registry->getQualifiedName('type', 'path/to/class/');
	}

	public function testGetQualifiedName_moduleInvalid() {
		$this->_mock_class_exists(false);

		$registry = $this
			->_produceRegistry()
			->injectModule('foo', []);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('MOD unresolvable "foo://fooType/class" - missing "\\foo\\fooType\\ClassFooType"');

		$registry->getQualifiedName('fooType', 'class', 'foo');
	}


	public function testGetQualifiedName_locate() {
		$this->_mock_class_exists();

		$registry = $this
			->_produceRegistry()
			->injectModule('foo', []);

		$this->assertEquals('\\foo\\fooType\\ClassFooType', $registry->getQualifiedName('fooType', 'class'));
	}

	public function testGetQualifiedName_locateInvalid() {
		$this->_mock_class_exists(false);

		$registry = $this
			->_produceRegistry()
			->injectModule('foo', []);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('MOD unresolvable "fooType:class"');

		$registry->getQualifiedName('fooType', 'class');
	}


	public function testLoadModule() {
		$registry = $this->_produceRegistry();

		$this->assertSame($registry, $registry->loadModule('foo'));
	}

	public function testLoadModule_locate() {
		$this->_mock_class_exists();
		$injector = $this->_mockInjector(function(string $qname) {
			if ($qname === '\\foo\\Module') return $this->_mockModule([
				'locator' => [
					'fooType' => [
						'path' => 'path\\to',
						'prefix' => 'Prefix',
						'postfix' => 'Postfix'
					]
				]
			]);
			throw new \ErrorException();
		});
		$registry = $this
			->_produceRegistry($injector)
			->loadModule('foo');

		$this->assertEquals('\\foo\\path\\to\\PrefixClassPostfix', $registry->getQualifiedName('fooType', 'class'));
	}
}
