<?php

namespace test\app;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\common\access\IKeyAccessor;
use eve\common\access\IItemAccessor;
use lola\common\IComponentConfig;
use lola\common\access\TreePropertyException;
use lola\common\access\TreeAccessor;
use lola\app\AppConfig;



final class AppConfigTest
extends TestCase
{

	use PHPMock;


	private function _mockFilterInput() {
		$this
			->getFunctionMock('\\lola\\app', 'filter_input')
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('SCRIPT_FILENAME')
			)
			->willReturnCallback(function(string $type, string $name) : string {
				switch ($name) {
					case 'SCRIPT_FILENAME' : return '/foo/bar/file/something.php';
					default : throw new \ErrorException();
				}
			});
	}


	private function _produceConfig(array $data = []) {
		return new AppConfig($data);
	}


	public function testInheritance() {
		$config = $this->_produceConfig();

		$this->assertInstanceOf(TreeAccessor::class, $config);
		$this->assertInstanceOf(IComponentConfig::class, $config);
		$this->assertInstanceOf(IItemAccessor::class, $config);
		$this->assertInstanceOf(IKeyAccessor::class, $config);
	}

	public function testRootPath() {
		$this->_mockFilterInput();
		$config = $this->_produceConfig();

		$this->assertEquals('/foo/bar', $config->getItem('rootPath'));
	}

	public function testVerbosity() {
		$config = $this->_produceConfig();

		$this->assertEquals(0, $config->getItem('verbosity'));
	}

	public function testgetItem_noProperty() {
		$config = $this->_produceConfig();

		$this->expectException(TreePropertyException::class);
		$this->expectExceptionMessage('foo');

		$config->getItem('ACC no property "!foo"');
	}
}
