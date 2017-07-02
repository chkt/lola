<?php

namespace lola\type;

use lola\type\AIterateable;
use lola\type\ISizedIterateable;



abstract class ASizedIterateable
extends AIterateable
implements ISizedIterateable
{

	protected function _hasItem(int $index) : bool {
		return $index > -1 && $index < $this->getLength();
	}


	public function& useLast() {
		return $this->useIndex($this->getLength() - 1);
	}
}
