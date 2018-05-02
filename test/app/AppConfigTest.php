<?php

namespace test\app;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;
use lola\common\access\IAccessorSelector;
use lola\common\access\exception\AccessorException;
use lola\app\AppConfig;



final class AppConfigTest
extends TestCase
{

	use PHPMock;


	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockFilterInput() {
		$this
			->getFunctionMock('\\lola\\app', 'filter_input')
			->expects($this->any())
			->with(
				$this->equalTo(INPUT_SERVER),
				$this->equalTo('SCRIPT_FILENAME')
			)
			->willReturn('/foo/bar/file/something.php');
	}


	private function _produceConfig(IAccessorSelector $selector = null, array & $data = []) {
		if (is_null($selector)) $selector = $this->_mockInterface(IAccessorSelector::class);

		return new AppConfig($selector, $data);
	}


	public function testInheritance() {
		$config = $this->_produceConfig();

		$this->assertInstanceOf(\lola\common\access\ItemAccessor::class, $config);
		$this->assertInstanceOf(\lola\common\IComponentConfig::class, $config);
	}

	public function testRootPath() {
		$this->_mockFilterInput();

		$data = [];
		$selector = $this->_mockInterface(IAccessorSelector::class, $data);

		$selector
			->method('select')
			->with(
				$this->equalTo($data),
				$this->equalTo('rootPath')
			)
			->willReturn($selector);

		$selector
			->expects($this->at(1))
			->method('isResolved')
			->willReturn(false);

		$selector
			->expects($this->at(5))
			->method('isResolved')
			->willReturn(true);

		$selector
			->expects($this->at(8))
			->method('isResolved')
			->willReturn(true);

		$selector
			->method('getPath')
			->willReturn('rootPath');

		$selector
			->method('linkAll')
			->willReturn($selector);

		$selector
			->method('setResolvedItem')
			->with($this->equalTo('/foo/bar'))
			->willReturn($selector);

		$selector
			->expects($this->at(6))
			->method('getResolvedItem')
			->willReturn('/foo/bar');

		$selector
			->expects($this->at(9))
			->method('getResolvedItem')
			->willReturn('/foo/baz');

		$config = $this->_produceConfig($selector);

		$this->assertEquals('/foo/bar', $config->getItem('rootPath'));
		$this->assertEquals('/foo/baz', $config->getItem('rootPath'));
	}

	public function testVerbosity() {
		$selector = $this->_mockInterface(IAccessorSelector::class);

		$selector
			->method('select')
			->willReturn($selector);

		$selector
			->expects($this->at(1))
			->method('isResolved')
			->willReturn(false);

		$selector
			->expects($this->at(5))
			->method('isResolved')
			->willReturn(true);

		$selector
			->expects($this->at(8))
			->method('isResolved')
			->willReturn(true);

		$selector
			->method('getPath')
			->willReturn('verbosity');

		$selector
			->method('linkAll')
			->willReturn($selector);

		$selector
			->method('setResolvedItem')
			->with($this->equalTo(0))
			->willReturn($selector);

		$selector
			->expects($this->at(6))
			->method('getResolvedItem')
			->willReturn(0);

		$selector
			->expects($this->at(9))
			->method('getResolvedItem')
			->willReturn(1);

		$config = $this->_produceConfig($selector);

		$this->assertEquals(0, $config->getItem('verbosity'));
		$this->assertEquals(1, $config->getItem('verbosity'));
	}

	public function testgetItem_noProperty() {

		$selector = $this->_mockInterface(IAccessorSelector::class);

		$selector
			->method('select')
			->willReturn($selector);

		$selector
			->method('isResolved')
			->willReturn(false);

		$config = $this->_produceConfig($selector);

		$this->expectException(AccessorException::class);

		$config->getItem('foo');
	}
}
