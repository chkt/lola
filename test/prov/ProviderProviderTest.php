<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use lola\app\IApp;
use lola\inject\Injector;
use lola\prov\AProvider;
use lola\prov\ProviderProvider;



final class ProviderProviderTest
extends TestCase
{

	public function testLocate() {
		$mockProv = $this
			->getMockBuilder(AProvider::class)
			->setConstructorArgs([function($location) {
				return $location;
			}])
			->getMockForAbstractClass();

		$injector = $this
			->getMockBuilder(Injector::class)
			->disableOriginalConstructor()
			->setMethods([ 'produce' ])
			->getMock();

		$injector
			->expects($this->exactly(4))
			->method('produce')
			->with($this->isType('string'))
			->willReturn($mockProv);

		$app = $this
			->getMockBuilder(IApp::class)
			->setMethodsExcept()
			->getMock();

		$app
			->expects($this->any())
			->method('useInjector')
			->willReturn($injector);

		$app
			->expects($this->any())
			->method('hasProperty')
			->willReturn(false);

		$locator = new ProviderProvider($app);

		$this->assertEquals('\\foo\\Bar', $locator->locate('class', '\\foo\\Bar'));
		$this->assertEquals('//module/controller?id', $locator->locate('controller', '//module/controller?id'));
		$this->assertEquals('//module/service?id', $locator->locate('service', '//module/service?id'));
		$this->assertEquals('name', $locator->locate('environment', 'name'));
	}
}
