<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\driver\IInjectorDriver;
use lola\app\CoreProvider;
use lola\app\CoreProviderFactory;
use lola\module\EntityParser;



final class CoreProviderFactoryTest
extends TestCase
{

	private function _mockInterface(string $iname, array $args = []) {
		$ins = $this
			->getMockBuilder($iname)
			->getMock();

		foreach ($args as $key => & $value) {
			$key = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$key =& $value;
		}

		return $ins;
	}


	private function _produceProviderFactory(ICoreFactory $core = null) : CoreProviderFactory {
		if (is_null($core)) $core = $this->_mockInterface(ICoreFactory::class);

		return new CoreProviderFactory($core);
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
}


	public function testInheritance() {
		$factory = $this->_produceProviderFactory();

		$this->assertInstanceOf(\eve\driver\InjectorDriverFactory::class, $factory);
	}

	public function test_produceDriver() {
		$base = $this->_mockInterface(ICoreFactory::class);
		$base
			->method('newInstance')
			->with(
				$this->equalTo(CoreProvider::class),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				return $this->_mockInterface(IInjectorDriver::class, $args);
			});

		$factory = $this->_produceProviderFactory($base);
		$method = new \ReflectionMethod($factory, '_produceDriver');
		$method->setAccessible(true);

		$dependencies = [];

		$provider = $method->invokeArgs($factory, [
			$base,
			$this->_produceAccessor([]),
			& $dependencies
		]);

		$dependencies['foo'] = 'bar';

		$this->assertInstanceOf(IInjectorDriver::class, $provider);
		$this->assertSame($dependencies, $provider->p0);
	}

	public function test_produceEntityParser() {
		$base = $this->_mockInterface(ICoreFactory::class);
		$base
			->method('newInstance')
			->with($this->equalTo(EntityParser::class))
			->willReturnCallback(function(string $qname) {
				return $this->_mockInterface(IEntityParser::class);
			});

		$driver = $this->_mockInterface(IInjectorDriver::class);
		$driver
			->method('getCoreFactory')
			->willReturn($base);

		$factory = $this->_produceProviderFactory($base);
		$method = new \ReflectionMethod($factory, '_produceEntityParser');
		$method->setAccessible(true);

		$parser = $method->invokeArgs($factory, [
			$driver,
			$this->_produceAccessor([])
		]);

		$this->assertInstanceOf(IEntityParser::class, $parser);
	}
}
