<?php

namespace lola\model;

use lola\type\AQueue;

use eve\common\access\ITraversableAccessor;



final class ProxyResourceQueue
extends AQueue
{

	public function process(ITraversableAccessor $data = null) {
		foreach ($this->_items as $cb) call_user_func($cb, !is_null($data) ? $data->getProjection() : null);		//TODO: legacy - should send structured data to consumers

		return $this;
	}
}
