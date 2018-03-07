<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use lola\type\AProjector;



final class ProjectorTest
extends TestCase
{

	private function _mockProjector(array $transforms) : AProjector {
		return $this
			->getMockBuilder(AProjector::class)
			->setConstructorArgs([ & $transforms ])
			->getMockForAbstractClass();
	}

	private function _mockAccessor() {
		$access = $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();

		return $access;
	}


	public function testInheritance() {
		$projector = $this->_mockProjector([]);

		$this->assertInstanceOf(\lola\type\IProjector::class, $projector);
		$this->assertInstanceOf(\lola\common\projection\IFilterProjectable::class, $projector);
		$this->assertInstanceOf(\eve\common\projection\IProjectable::class, $projector);
	}

	public function testSetSource() {
		$data = $this->_mockAccessor();
		$ins = $this->_mockProjector([]);

		$this->assertEquals($ins, $ins->setSource($data));
	}

	public function testGetProjection() {
		$data = [
			'foo' => 0,
			'bar' => [ 1, 2, 3 ],
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			]
		];

		$access = $this->_mockAccessor();

		$access
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($data) {
				$segs = explode('.', $key);
				$branch = $data;

				foreach ($segs as $seg) $branch = $branch[$seg];

				return $branch;
			});

		$ins = $this
			->_mockProjector([
				'fooProp' => function(ITraversableAccessor $data) {
					return [ 'foo' => $data->getItem('foo') ];
				},
				'barProp' => function(ITraversableAccessor $data) {
					return [ 'bar' => $data->getItem('bar') ];
				},
				'bazProp' => function(ITraversableAccessor $data) {
					return [ 'baz' => $data->getItem('baz') ];
				},
				'quuxProp' => function(ITraversableAccessor $data) {
					return [ 'quux' => $data->getItem('baz.baz') ];
				}
			])
			->setSource($access);

		$this->assertEquals([
			'foo' => 0,
			'bar' => [ 1, 2, 3 ],
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			],
			'quux' => 6
		], $ins->getProjection());

		$this->assertEquals([
			'foo' => 0
		], $ins->getProjection([ 'fooProp' ]));

		$this->assertEquals([
			'bar' => [ 1, 2, 3 ]
		], $ins->getProjection([ 'barProp' ]));

		$this->assertEquals([
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			]
		], $ins->getProjection([ 'bazProp' ]));

		$this->assertEquals([
			'quux' => 6
		], $ins->getProjection([ 'quuxProp' ]));
	}
}
