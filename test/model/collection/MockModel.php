<?php

namespace test\model\collection;

use lola\inject\IInjectable;
use lola\model\IModel;



final class MockModel
implements IModel, IInjectable
{

	static public function getDependencyConfig(array $config) {
		return [];
	}


	public function isLive() {}


	public function wasCreated() {}

	public function wasRead() {}


	public function deferUpdates() {}

	public function update() {}


	public function getProjection(array $selection = null) : array {}
}
