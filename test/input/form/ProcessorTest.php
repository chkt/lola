<?php

namespace test\input\form;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationInterceptor;
use lola\input\valid\AValidator;
use lola\input\valid\step\ArrayPropSelect;
use lola\input\valid\step\IsStringNonEmpty;
use lola\input\valid\step\Constraint;
use lola\input\form\Field;
use lola\input\form\ValidationInterceptor;
use lola\input\form\Processor;



final class ProcessorTest
extends TestCase
{

	private function _mockValidator(array $steps, IValidationInterceptor $interceptor = null) : AValidator {
		return $this
			->getMockBuilder(AValidator::class)
			->setConstructorArgs([ $steps, $interceptor ])
			->getMockForAbstractClass(AValidator::class);
	}

	private function _produceInterceptor(array $map) : ValidationInterceptor {
		return new ValidationInterceptor($map);
	}

	private function _produceProcessor() : Processor {
		$field0 = new Field('foo', [ 'bang' ]);
		$field1 = new Field('bar', [ '1' ]);
		$field2 = new Field('baz', [], Field::FLAG_SUBMIT);

		$step0 = new ArrayPropSelect('foo', new Constraint(['bang', 'quux', 'barf']));
		$step1 = new ArrayPropSelect('bar', new IsStringNonEmpty());

		$interceptor = $this->_produceInterceptor([
			'arrayProp.foo' => & $field0,
			'constraint' => & $field0,
			'arrayProp.bar' => & $field1,
			'stringNonEmpty' => & $field1
		]);

		$validator = $this->_mockValidator([ $step0, $step1 ], $interceptor);

		return new Processor([ & $field0, & $field1, & $field2], $validator);
	}


	public function testInheritance() {
		$processor = $this->_produceProcessor();

		$this->assertInstanceOf(\lola\input\form\IProcessor::class, $processor);
		$this->assertInstanceOf(\eve\common\projection\IProjectable::class, $processor);
	}

	public function testGetState() {
		$processor = $this->_produceProcessor();

		$this->assertEquals(Processor::STATE_UNVALIDATED, $processor->getState());
	}

	public function testSetState() {
		$processor = $this->_produceProcessor();

		$this->assertEquals(Processor::STATE_UNVALIDATED, $processor->getState());
		$this->assertEquals($processor, $processor->setState(Processor::STATE_INVALID));
		$this->assertEquals(Processor::STATE_INVALID, $processor->getState());
	}


	public function testUseField() {
		$processor = $this->_produceProcessor();

		$field0 =& $processor->useField('foo');

		$this->assertInstanceOf(Field::class, $field0);
		$this->assertEquals('foo', $field0->getName());
		$this->assertEquals('bang', $field0->getValue());

		$field1 =& $processor->useField('bar');

		$this->assertInstanceOf(Field::class, $field1);
		$this->assertEquals('bar', $field1->getName());
		$this->assertEquals('1', $field1->getValue());

		$field2 =& $processor->useField('baz');

		$this->assertInstanceOf(Field::class, $field2);
		$this->assertEquals('baz', $field2->getName());
		$this->assertTrue($field2->isSubmit());
	}

	public function testGetValue() {
		$processor = $this->_produceProcessor();

		$this->assertEquals('bang', $processor->getValue('foo'));
		$this->assertEquals('1', $processor->getValue('bar'));
		$this->assertEquals('', $processor->getValue('baz'));
	}

	public function testGetValues() {
		$processor = $this->_produceProcessor();

		$this->assertEquals([ 'bang' ], $processor->getValues('foo'));
		$this->assertEquals([ '1' ], $processor->getValues('bar'));
		$this->assertEquals([], $processor->getValues('baz'));
	}

	public function testGetValidatedData() {
		$processor = $this->_produceProcessor();

		$this->assertNull($processor->getValidatedData('foo'));
		$this->assertNull($processor->getValidatedData('bar'));
		$this->assertNull($processor->getValidatedData('baz'));

		$processor->validate([
			'foo' => 'quux',
			'bar' => '2',
			'baz' => 'submit'
		]);

		$this->assertEquals('quux', $processor->getValidatedData('foo'));
		$this->assertEquals(2, $processor->getValidatedData('bar'));
		$this->assertEquals('submit', $processor->getValidatedData('baz'));
	}


	public function testValidate() {
		$processor0 = $this->_produceProcessor();

		$this->assertEquals($processor0, $processor0->validate([
			'foo' => 'bang',
			'bar' => '1',
			'baz' => ''
		]));

		$this->assertEquals(Processor::STATE_UNCOMMITED, $processor0->getState());

		$processor1 = $this->_produceProcessor();

		$this->assertEquals($processor1, $processor1->validate([
			'foo' => 'bang',
			'bar' => '1',
			'baz' => 'submit'
		]));

		$this->assertEquals(Processor::STATE_UNMODIFIED, $processor1->getState());

		$processor2 = $this->_produceProcessor();

		$this->assertEquals($processor2, $processor2->validate([
			'foo' => '',
			'bar' => '1',
			'baz' => 'submit'
		]));

		$this->assertEquals(Processor::STATE_INVALID, $processor2->getState());

		$processor3 = $this->_produceProcessor();

		$this->assertEquals($processor3, $processor3->validate([
			'foo' => 'barf',
			'bar' => '2',
			'baz' => 'submit'
		]));

		$this->assertEquals(Processor::STATE_VALID, $processor3->getState());
	}


	public function testGetProjection() {
		$processor = $this->_produceProcessor();
		$field0 =& $processor->useField('foo');
		$field1 =& $processor->useField('bar');
		$field2 =& $processor->useField('baz');

		$this->assertEquals([
			'state' => Processor::STATE_UNVALIDATED,
			'fields' => [
				$field0->getName() => $field0->getProjection(),
				$field1->getName() => $field1->getProjection(),
				$field2->getName() => $field2->getProjection()
			]
		], $processor->getProjection());

		$processor->validate([
			'foo' => 'barf',
			'bar' => '2',
			'baz' => 'submit'
		]);

		$this->assertEquals([
			'state' => Processor::STATE_VALID,
			'fields' => [
				$field0->getName() => $field0->getProjection(),
				$field1->getName() => $field1->getProjection(),
				$field2->getName() => $field2->getProjection()
			]
		], $processor->getProjection());
	}
}
