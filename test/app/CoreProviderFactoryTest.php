<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;
use eve\provide\ILocator;
use lola\app\CoreProviderFactory;



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

	private function _mockAccessorFactory() {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();

		$ins
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		return $ins;
	}


	private function _produceProviderFactory(ICoreFactory $core = null) : CoreProviderFactory {
		if (is_null($core)) $core = $this->_mockInterface(ICoreFactory::class);

		return new CoreProviderFactory($core);
	}


	public function testInheritance() {
		$factory = $this->_produceProviderFactory();

		$this->assertInstanceOf(\eve\driver\InjectorDriverFactory::class, $factory);
	}


	public function testProduce() {
		$core = $this->_mockInterface(ICoreFactory::class);
		$access = $this->_mockAccessorFactory();

		$core
			->expects($this->once())
			->method('newInstance')
			->with(
				$this->equalTo(\lola\app\CoreProvider::class),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) use ($access) {
				return $this->_mockInterface(IInjectorDriver::class, $args);
			});

		$factory = $this->_produceProviderFactory($core);
		$config = [
			'accessorFactory' => $access,
			'instanceCache' => $this->_mockInterface(IItemMutator::class),
			'entityParser' => $this->_mockInterface(IEntityParser::class),
			'injector' => $this->_mockInterface(IInjector::class),
			'locator' => $this->_mockInterface(ILocator::class)
		];

		$factory->produce($config);
	}
}
