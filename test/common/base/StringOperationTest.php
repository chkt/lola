<?php

namespace lola\common\base;

use PHPUnit\Framework\TestCase;



final class StringOperationTest
extends TestCase
{


	public function testPathToCamel() {
		$this->assertEquals('', StringOperation::pathToCamel(''));
		$this->assertEquals('path', StringOperation::pathToCamel('path'));
		$this->assertEquals('path', StringOperation::pathToCamel('path'));
		$this->assertEquals('pathToResource', StringOperation::pathToCamel('path/to/resource'));
		$this->assertEquals('pathToResource', StringOperation::pathToCamel('PATH/TO/RESOURCE'));
		$this->assertEquals('pathToResource', StringOperation::pathToCamel('/path/to/resource/'));
	}

	public function testPathToSnake() {
		$this->assertEquals('', StringOperation::pathToSnake(''));
		$this->assertEquals('path', StringOperation::pathToSnake('path'));
		$this->assertEquals('path', StringOperation::pathToSnake('PATH'));
		$this->assertEquals('path_to_resource', StringOperation::pathToSnake('path/to/resource'));
		$this->assertEquals('path_to_resource', StringOperation::pathToSnake('PATH/TO/RESOURCE'));
		$this->assertEquals('path_to_resource', StringOperation::pathToSnake('/path/to/resource'));
	}
}
