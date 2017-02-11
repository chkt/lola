<?php

namespace test\input\form;

use PHPUnit\Framework\TestCase;

use lola\input\form\Field;
use lola\input\valid\AValidationStep;
use lola\input\valid\step\NoopValidationStep;



final class FieldTest
extends TestCase
{

	private function _mockValidationStep() {
		return $this
			->getMockBuilder(AValidationStep::class)
			->getMockForAbstractClass();
	}


	private function _produceField($name = 'foo', $values = [], $flags = Field::FLAG_NONE) {
		return new Field($name, $values, $flags);
	}

	public function testIsChanged() {
		$field0 = $this->_produceField();

		$this->assertFalse($field0->isChanged());

		$field0->setValue('foo');

		$this->assertTrue($field0->isChanged());

		$field0->setValue('');

		$this->assertFalse($field0->isChanged());

		$field1 = $this->_produceField('bar', [ 'baz' ]);

		$this->assertFalse($field1->isChanged());

		$field1->setValue('');

		$this->assertTrue($field1->isChanged());

		$field1->setValue('baz');

		$this->assertFalse($field1->isChanged());
	}

	public function testIsEmpty() {
		$field = $this->_produceField();

		$this->assertTrue($field->isEmpty());

		$field->setValue('foo');

		$this->assertFalse($field->isEmpty());

		$field->setValue('');

		$this->assertTrue($field->isEmpty());
	}

	public function testIsMultiValue() {
		$field0 = $this->_produceField();

		$this->assertFalse($field0->isMultiValue());

		$field0->setValue('foo');

		$this->assertFalse($field0->isMultiValue());

		$field0->setValues([ 'bar' ]);

		$this->assertFalse($field0->isMultiValue());

		$field0->setValues([ 'baz', 'quux' ]);

		$this->assertTrue($field0->isMultiValue());

		$field1 = $this->_produceField('foo', [ 'bar', 'baz' ]);

		$this->assertTrue($field1->isMultiValue());
	}

	public function testIsImmutable() {
		$field0 = $this->_produceField('foo');

		$this->assertFalse($field0->isImmutable());

		$field1 = $this->_produceField('bar', [], Field::FLAG_IMMUTABLE);

		$this->assertTrue($field1->isImmutable());
	}

	public function testIsSubmit() {
		$field0 = $this->_produceField('foo');

		$this->assertFalse($field0->isSubmit());

		$field1 = $this->_produceField('bar', [], Field::FLAG_SUBMIT);

		$this->assertTrue($field1->isSubmit());
	}


	public function testGetName() {
		$field0 = $this->_produceField('foo');

		$this->assertEquals('foo', $field0->getName());

		$field1 = $this->_produceField('bar');

		$this->assertEquals('bar', $field1->getName());
	}


	public function testGetValue() {
		$field0 = $this->_produceField();

		$this->assertEmpty($field0->getValue());

		$field0->setValue('foo');

		$this->assertEquals('foo', $field0->getValue());

		$field0->setValues([]);

		$this->assertEquals('', $field0->getValue());

		$field0->setValues([ '' ]);

		$this->assertEquals('', $field0->getValue());

		$field0->setValues([ '', 'bar' ]);

		$this->assertEquals('bar', $field0->getValue());

		$field0->setValues([ 'baz', 'quux' ]);

		$this->assertEquals('baz', $field0->getValue());

		$field1 = $this->_produceField('bar', [ '' ]);

		$this->assertEquals('', $field1->getValue());

		$field2 = $this->_produceField('baz', [ '', 'foo' ]);

		$this->assertEquals('foo', $field2->getValue());

		$field3 = $this->_produceField('bar', [ 'baz', 'quux' ]);

		$this->assertEquals('baz', $field3->getValue());
	}

	public function testSetValue() {
		$field = $this->_produceField();

		$this->assertEquals($field, $field->setValue('foo'));
	}


	public function testGetValues() {
		$field0 = $this->_produceField();

		$this->assertEmpty($field0->getValues());

		$field0->setValue('');

		$this->assertEmpty($field0->getValues());

		$field0->setValue('foo');

		$this->assertEquals([ 'foo' ], $field0->getValues());

		$field0->setValues([]);

		$this->assertEmpty($field0->getValues());

		$field0->setValues(['']);

		$this->assertEmpty($field0->getValues());

		$field0->setValues(['', '']);

		$this->assertEmpty($field0->getValues());

		$field0->setValues(['', 'foo']);

		$this->assertEquals([ 'foo' ], $field0->getValues());

		$field0->setValues([ 'foo', 'bar', 'foo' ]);

		$this->assertEquals([ 'foo', 'bar'], $field0->getValues());

		$field1 = $this->_produceField('bar', ['']);

		$this->assertEmpty($field1->getValues());

		$field2 = $this->_produceField('baz', ['', '']);

		$this->assertEmpty($field2->getValues());

		$field3 = $this->_produceField('quux', ['', 'foo']);

		$this->assertEquals([ 'foo' ], $field3->getValues());

		$field4 = $this->_produceField('bang', [ 'foo', 'bar', 'foo']);

		$this->assertEquals([ 'foo', 'bar' ], $field4->getValues());
	}

	public function testSetValues() {
		$field = $this->_produceField();

		$this->assertEquals($field, $field->setValues([]));
	}


	public function testUseValidation() {
		$field = $this->_produceField();

		$step0 =& $field->useValidation();

		$this->assertInstanceOf(NoopValidationStep::class, $step0);

		$step1 = $this->_mockValidationStep();

		$field->setValidation($step1);

		$this->assertEquals($step1, $field->useValidation());
	}

	public function testSetValidation() {
		$field = $this->_produceField();
		$step = $this->_mockValidationStep();

		$this->assertEquals($field, $field->setValidation($step));
	}


	public function testGetProjection() {
		$field = $this->_produceField();

		$this->assertEquals([
			'changed' => false,
			'empty' => true,
			'multiValue' => false,
			'immutable' => false,
			'submit' => false,
			'name' => 'foo',
			'value' => '',
			'values' => [],
			'validated' => true,
			'valid' => true,
			'error' => null
		], $field->getProjection());
	}
}
