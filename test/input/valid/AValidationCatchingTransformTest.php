<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationStep;
use lola\input\valid\AValidationCatchingTransform;
use lola\input\valid\ValidationException;



final class AValidationCatchingTransformTest
extends TestCase
{

	private function _mockTest() : IValidationStep {
		$ins = $this
			->getMockBuilder(IValidationStep::class)
			->getMock();

		return $ins;
	}

	private function _mockTransform(IValidationStep $step, callable $test, callable $recover) : AValidationCatchingTransform {
		$ins = $this
			->getMockBuilder(AValidationCatchingTransform::class)
			->setConstructorArgs([ $step ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$ins
			->expects($this->any())
			->method('_recover')
			->with($this->isInstanceOf(ValidationException::class))
			->willReturnCallback($recover);

		return $ins;
	}

	private function _produceException($message = '', $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testWasRecovered() {
		$step1 = $this->_mockTest();
		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function(ValidationException $ex) {
			return 'bang';
		});

		$this->assertFalse($step0->wasRecovered());

		$step0->validate('foo');

		$this->assertFalse($step0->wasTransformed());

		$step0->recover($this->_produceException());

		$this->assertTrue($step0->wasRecovered());
	}

	public function testGetRecoveredResult() {
		$step1 = $this->_mockTest();
		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function(ValidationException $ex) {
			return 'bang';
		});

		$step0->validate('foo');

		$step0->recover($this->_produceException());

		$this->assertEquals('bang', $step0->getRecoveredResult());
	}

	public function testGetRecoveredResult_error() {
		$step1 = $this->_mockTest();
		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function(ValidationException $ex) {
			return 'bang';
		});

		$this->expectException(\ErrorException::class);

		$step0->getRecoveredResult();
	}

	public function testRecover() {
		$step1 = $this->_mockTest();
		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function(ValidationException $ex) {
			return 'bang';
		});

		$this->assertEquals($step0, $step0->validate('foo'));
		$this->assertEquals($step0, $step0->recover($this->_produceException()));
	}
}
