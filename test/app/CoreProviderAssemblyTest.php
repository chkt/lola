<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\ITokenizer;
use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\driver\InjectorDriverAssembly;
use lola\common\uri\KeyValueTokenizer;
use lola\app\CoreProviderAssembly;
use lola\module\IEntityParser;
use lola\module\EntityParser;




final class CoreProviderAssemblyTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		return $this
			->getMockBuilder($qname)
			->getMock();
	}

	private function _mockCoreFactory(array $map) {
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('newInstance')
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) use ($map) {
				$this->assertArrayHasKey($qname, $map);

				return $map[$qname];
			});

		return $base;
	}

	private function _mockAccessorFactory() {
		$access = $this->_mockInterface(ISimpleFactory::class);

		return $access;
	}

	private function _mockAccessor() {
		$access = $this->_mockInterface(ITraversableAccessor::class);

		return $access;
	}

	private function _produceAssembly(array $map = []) {
		$base = $this->_mockCoreFactory($map);
		$access = $this->_mockAccessorFactory();
		$config = $this->_mockAccessor();

		return new CoreProviderAssembly($base, $access, $config);
	}


	public function testInheritance() {
		$assembly = $this->_produceAssembly();

		$this->assertInstanceOf(InjectorDriverAssembly::class, $assembly);
	}

	public function testGetItem_entityParser() {
		$config = $this->_mockInterface(ITokenizer::class);
		$parser = $this->_mockInterface(IEntityParser::class);
		$assembly = $this->_produceAssembly([
			KeyValueTokenizer::class => $config,
			EntityParser::class => $parser
		]);

		$this->assertSame($parser, $assembly->getItem('entityParser'));
	}
}
