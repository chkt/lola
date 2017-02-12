<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationStep;
use lola\input\valid\AValidationStep;
use lola\input\valid\AValidationTransform;



final class AValidationTransformTest
extends TestCase
{

	private function _mockTest(callable $test) {
		$ins = $this
			->getMockBuilder(AValidationStep::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		return $ins;
	}

	private function _mockTransform(IValidationStep $next, callable $test, callable $transform) {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->setConstructorArgs([ $next ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$ins
			->expects($this->any())
			->method('_transform')
			->with($this->anything())
			->willReturnCallback($transform);

		return $ins;
	}


	public function testWasTransformed() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value['foo'];
		}, function($source, $result) {
			$source['foo'] = $result;

			return $source;
		});

		$this->assertFalse($step0->wasTransformed());

		$step0->validate([ 'foo' => '1' ]);

		$this->assertFalse($step0->wasTransformed());

		$step0->transform(1);

		$this->assertTrue($step0->wasTransformed());
	}


	public function testGetNextStep() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function($source, $result) {
			return $result;
		});

		$this->assertEquals($step1, $step0->getNextStep());
	}


	public function testGetTransformedResult() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value['foo'];
		}, function($source, $result) {
			$source['foo'] = $result;

			return $source;
		});

		$step0
			->validate(['foo' => '1'])
			->transform(1);

		$this->assertEquals(['foo' => 1], $step0->getTransformedResult());
	}

	public function testTransform() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value['foo'];
		}, function($source, $result) {
			return $source['foo'] = $result;
		});

		$this->assertEquals($step0, $step0->validate([ 'foo' => '1' ]));
		$this->assertEquals($step0, $step0->transform(1));
	}
}
