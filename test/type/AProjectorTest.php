<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use lola\type\StructuredData;
use lola\type\AProjector;



final class ProjectorTest
extends TestCase
{

	private function _getProjection(array $data, array $transforms) {
		$source = new StructuredData($data);

		return $this
			->getMockBuilder(AProjector::class)
			->setConstructorArgs([ & $source, & $transforms ])
			->getMockForAbstractClass();
	}


	public function testGet() {
		$ins = $this->_getProjection([
			'foo' => 0,
			'bar' => [ 1, 2, 3 ],
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			]
		], [
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
		]);

		$this->assertEquals([
			'foo' => 0,
			'bar' => [ 1, 2, 3 ],
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			],
			'quux' => 6
		], $ins->get());

		$this->assertEquals([
			'foo' => 0
		], $ins->get([ 'fooProp' ]));

		$this->assertEquals([
			'bar' => [ 1, 2, 3 ]
		], $ins->get([ 'barProp' ]));

		$this->assertEquals([
			'baz' => [
				'foo' => 4,
				'bar' => 5,
				'baz' => 6
			]
		], $ins->get([ 'bazProp' ]));

		$this->assertEquals([
			'quux' => 6
		], $ins->get([ 'quuxProp' ]));
	}
}
