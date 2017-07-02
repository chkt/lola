<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationTransform;
use lola\input\valid\AValidationTransform;
use lola\input\valid\AValidationRecoveryTransform;
use lola\input\valid\IValidationException;
use lola\input\valid\ValidationException;



final class AValidationRecoveryTransformTest
extends TestCase
{

	private function _mockTest(callable $test) : IValidationTransform {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);


		return $ins;
	}

	private function _mockTransform(IValidationTransform $step, callable $test, callable $transform) : AValidationTransform {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->setConstructorArgs([ $step ])
			->setMethods([ '_transform' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$ins
			->expects($this->any())
			->method('_transform')
			->with($this->anything(), $this->anything())
			->willReturnCallback($transform);

		return $ins;
	}

	private function _mockRecoverable(IValidationTransform $step, callable $test, callable $recover) : AValidationRecoveryTransform {
		$ins = $this
			->getMockBuilder(AValidationRecoveryTransform::class)
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
			->with($this->isInstanceOf(IValidationException::class))
			->willReturnCallback($recover);

		return $ins;
	}

	private function _produceTestChain() {
		$step2 = $this->_mockTest(function($source) {
			if (!is_string($source)) throw new ValidationException('NOSTRING', 4);

			if (!is_numeric($source)) throw new ValidationException('NOTNUMERIC', 5);

			return (int) $source;
		});

		$step1 = $this->_mockTransform($step2, function($source) {
			if (!is_array($source)) throw new ValidationException('NOARRAY', 2);

			if (!array_key_exists('foo', $source)) throw new ValidationException('NOPROP', 3);

			return $source['foo'];
		}, function($source, $result) {
			$source['foo'] = $result;

			return $source;
		});

		$step0 = $this->_mockRecoverable($step1, function($source) {
			return $source;
		}, function(IValidationException $ex) {
			return 'bang';
		});

		return $step0;
	}


	public function testWasRecovered() {
		$step0 = $this->_produceTestChain();

		$this->assertFalse($step0->wasRecovered());

		$step0->validate(['foo' => '1']);
		$this->assertTrue($step0->wasValidated());
		$this->assertTrue($step0->isValid());
		$this->assertFalse($step0->wasRecovered());
		$this->assertEquals(['foo' => 1], $step0->getResult());

		$step0->reset();
		$this->assertFalse($step0->wasValidated());

		$step0->validate('foo');
		$this->assertTrue($step0->wasValidated());
		$this->assertTrue($step0->isValid());
		$this->assertTrue($step0->wasRecovered());
		$this->assertEquals('bang', $step0->getResult());
	}

	public function testUseTerminalStep() {
		$step0 = $this->_produceTestChain();
		$step1 = $step0->useNextStep();
		$step2 = $step1->useNextStep();

		$this->assertEquals($step0, $step0->useTerminalStep());

		$step0->validate(['foo' => '1']);
		$this->assertEquals($step2, $step0->useTerminalStep());

		$step0->validate('foo');
		$this->assertEquals($step0, $step0->useTerminalStep());
	}
}
