<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;
use lola\input\valid\AValidationStep;



final class AValidationStepTest
extends TestCase
{

	private function _mockStep(callable $fn = null) {
		if (is_null($fn)) $fn = function($value) {
			return $value;
		};

		$step = $this
			->getMockBuilder(AValidationStep::class)
			->getMockForAbstractClass();

		$step
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($fn);

		return $step;
	}


	public function testWasValidated() {
		$step = $this->_mockStep();

		$this->assertFalse($step->wasValidated());

		$step->validate('foo');

		$this->assertTrue($step->wasValidated());

		$step->reset();

		$this->assertFalse($step->wasValidated());

		$invalidStep = $this->_mockStep(function($value) {
			throw new ValidationException('bang');
		});

		$this->assertFalse($invalidStep->wasValidated());

		$invalidStep->validate('foo');

		$this->assertTrue($invalidStep->wasValidated());
	}

	public function testIsValid() {
		$step = $this->_mockStep();

		$this->assertFalse($step->isValid());

		$step->validate('foo');

		$this->assertTrue($step->isValid());

		$step->reset();

		$this->assertFalse($step->isValid());

		$invalidStep = $this->_mockStep(function($value) {
			throw new ValidationException('bang');
		});

		$this->assertFalse($invalidStep->isValid());

		$invalidStep->validate('foo');

		$this->assertFalse($invalidStep->isValid());
	}


	public function testGetSource() {
		$step = $this->_mockStep(function($value) {
			if (!is_string($value)) throw new ValidationException('type');

			return $value;
		});

		$this->assertNull($step->getSource());

		$step->validate('foo');

		$this->assertEquals('foo', $step->getSource());

		$step->validate(1);

		$this->assertEquals(1, $step->getSource());

		$step->reset();

		$this->assertNull($step->getSource());
	}

	public function testGetResult() {
		$step = $this->_mockStep(function($value) {
			if (!is_string($value)) throw new ValidationException('type');

			return strrev($value);
		});

		$this->assertNull($step->getResult());

		$step->validate('foo');

		$this->assertEquals('oof', $step->getResult());

		$step->validate(1);

		$this->assertEquals(1, $step->getResult());

		$step->reset();

		$this->assertNull($step->getResult());
	}

	public function testGetError() {
		$step = $this->_mockStep(function($value) {
			throw new ValidationException('bang');
		});

		$step->validate('');

		$ex = $step->getError();

		$this->assertInstanceOf(ValidationException::class, $ex);
	}

	public function testGetErrorInitial() {
		$step = $this->_mockStep();

		$this->expectException(\ErrorException::class);

		$step->getError();
	}

	public function testGetErrorValid() {
		$step = $this->_mockStep();

		$this->expectException(\ErrorException::class);

		$step
			->validate('foo')
			->getError();
	}


	public function testValidate() {
		$step = $this->_mockStep();

		$this->assertEquals($step, $step->validate('foo'));
		$this->assertTrue($step->wasValidated());
		$this->assertEquals('foo', $step->getSource());
		$this->assertEquals('foo', $step->getResult());
	}

	public function testReset() {
		$step = $this
			->_mockStep()
			->validate('foo');

		$this->assertEquals($step, $step->reset());
		$this->assertFalse($step->wasValidated());
		$this->assertNull($step->getSource());
		$this->assertNull($step->getResult());
	}
}
