<?php

declare(strict_types=1);
namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\TypedTreeData;



final class TypedTreeDataTest
extends TestCase
{

	private function _produceData(array& $data = null) {
		if (is_null($data)) $data = [
			'bool' => true,
			'int' => 1,
			'float' => M_PI,
			'string' => 'foo',
			'object' => new \stdClass(),
			'array' => [
				'bool' => false,
				'int' => 2,
				'float' => M_EULER,
				'string' => 'bar',
				'object' => new \stdClass(),
				'array' => []
			]
		];

		return new TypedTreeData($data);
	}


	public function testIsBool() {
		$data = $this->_produceData();

		$this->assertTrue($data->isBool('bool'));
		$this->assertFalse($data->isBool('int'));
		$this->assertFalse($data->isBool('foo'));
	}

	public function testGetBool() {
		$data = $this->_produceData();

		$this->assertSame(true, $data->getBool('bool'));
		$this->assertSame(false, $data->getBool('array.bool'));
	}

	public function testGetBool_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->getBool('int');
	}

	public function testSetBool() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->setBool('foo', true));
		$this->assertSame(true, $data->getBool('foo'));
	}

	public function testSetBool_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->setBool('foo', '1');
	}


	public function testIsInt() {
		$data = $this->_produceData();

		$this->assertFalse($data->isInt('bool'));
		$this->assertTrue($data->isInt('int'));
		$this->assertFalse($data->isInt('foo'));
	}

	public function testGetInt() {
		$data = $this->_produceData();

		$this->assertSame(1, $data->getInt('int'));
		$this->assertSame(2, $data->getInt('array.int'));
	}

	public function testGetInt_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->getInt('bool');
	}

	public function testSetInt() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->setInt('foo', 3));
		$this->assertSame(3, $data->getInt('foo'));
	}

	public function testSetInt_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->setInt('foo', '1');
	}


	public function testIsFloat() {
		$data = $this->_produceData();

		$this->assertFalse($data->isFloat('bool'));
		$this->assertFalse($data->isFloat('int'));
		$this->assertTrue($data->isFloat('float'));
		$this->assertFalse($data->isFloat('foo'));
	}

	public function testGetFloat() {
		$data = $this->_produceData();

		$this->assertSame(M_PI, $data->getFloat('float'));
		$this->assertSame(M_EULER, $data->getFloat('array.float'));
	}

	public function testGetFloat_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->getFloat('bool');
	}

	public function testSetFloat() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->setFloat('foo', M_PI));
		$this->assertSame(M_PI, $data->getFloat('foo'));
	}

	public function testSetFloat_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->setFloat('foo', '1');
	}


	public function testIsString() {
		$data = $this->_produceData();

		$this->assertFalse($data->isString('bool'));
		$this->assertTrue($data->isString('string'));
		$this->assertFalse($data->isString('foo'));
	}

	public function testGetString() {
		$data = $this->_produceData();

		$this->assertSame('foo', $data->getString('string'));
		$this->assertSame('bar', $data->getString('array.string'));
	}

	public function testGetString_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->getString('bool');
	}

	public function testSetString() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->setString('foo', 'baz'));
		$this->assertSame('baz', $data->getString('foo'));
	}

	public function testSetString_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->setString('foo', 1);
	}


	public function testIsArray() {
		$data = $this->_produceData();

		$this->assertFalse($data->isArray('bool'));
		$this->assertTrue($data->isArray('array'));
		$this->assertFalse($data->isArray('foo'));
	}

	public function testUseArray() {
		$data = $this->_produceData();

		$a =& $data->useArray('array');
		$b =& $data->useArray('array.array');

		$this->assertInternalType('array', $a);
		$this->assertNotEmpty($a);
		$this->assertArrayHasKey('array', $a);
		$this->assertEquals($b, $a['array']);
		$this->assertInternalType('array', $b);
		$this->assertEmpty($b);

		$c =& $data->useArray('array.array');
		$b['foo'] = 'bar';
		$c['baz'] = 'quux';

		$this->assertSame($b, $c);
	}

	public function testUseArray_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->useArray('bool');
	}

	public function testSetArray() {
		$data = $this->_produceData();

		$a = [
			'foo' => 'bar',
			'baz' => 'quux'
		];

		$this->assertSame($data, $data->setArray('foo', $a));
		$this->assertSame($a, $data->useArray('foo'));
	}

	public function testSetArray_exception() {
		$data = $this->_produceData();

		$this->expectException(\TypeError::class);

		$data->setArray('foo', '1');
	}


	public function testIsInstance() {
		$data = $this->_produceData();

		$this->assertFalse($data->isInstance('bool', \stdClass::class));
		$this->assertTrue($data->isInstance('object', \stdClass::class));
		$this->assertFalse($data->isInstance('foo', \stdClass::class));
	}

	public function testUseInstance() {
		$data = $this->_produceData();

		$this->assertInstanceOf(\stdClass::class, $data->useInstance('object', \stdClass::class));
		$this->assertInstanceOf(\stdClass::class, $data->useInstance('array.object', \stdClass::class));

		$a =& $data->useInstance('object', \stdClass::class);
		$b =& $data->useInstance('object', \stdClass::class);

		$a->foo = 1;
		$b->bar = 2;

		$this->assertSame($a, $b);
	}

	public function testUseInstance_noObject() {
		$data = $this->_produceData();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC_NO_INS:bool');

		$data->useInstance('bool', \stdClass::class);
	}

	public function testUseInstance_noClass() {
		$data = $this->_produceData();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC_NO_INS:array');

		$data->useInstance('array', 'array');
	}

	public function testUseInstance_invalidClass() {
		$data = $this->_produceData();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC_NO_INS:object');

		$data->useInstance('object', 'foo');
	}


	public function testSetInstance() {
		$data = $this->_produceData();

		$ins = new \stdClass();

		$this->assertSame($data, $data->setInstance('foo', $ins));
		$this->assertSame($ins, $data->useInstance('foo', \stdClass::class));
	}

	public function testSetInstance_exception() {
		$data = $this->_produceData();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC_NO_INS:foo');

		$data->setInstance('foo', true);
	}
}
