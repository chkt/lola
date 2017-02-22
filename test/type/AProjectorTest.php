<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use lola\type\StructuredData;
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


	private function _produceSource(array $data) : StructuredData {
		return new StructuredData($data);
	}


	public function testSetSource() {
		$data = $this->_produceSource([]);
		$ins = $this->_mockProjector([]);

		$this->assertEquals($ins, $ins->setSource($data));
	}


	public function testGetProjection() {
		$data = $this->_produceSource([
			'foo' => 0,
			'bar' => [ 1, 2, 3 ],
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			]
		]);

		$ins = $this
			->_mockProjector([
				'fooProp' => function(StructuredData $data) {
					return [ 'foo' => $data->useItem('foo') ];
				},
				'barProp' => function(StructuredData $data) {
					return [ 'bar' => $data->useItem('bar') ];
				},
				'bazProp' => function(StructuredData $data) {
					return [ 'baz' => $data->useItem('baz') ];
				},
				'quuxProp' => function(StructuredData $data) {
					return [ 'quux' => $data->useItem('baz.baz') ];
				}
			])
			->setSource($data);

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
