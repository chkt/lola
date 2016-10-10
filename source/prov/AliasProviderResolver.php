<?php

namespace lola\prov;

use lola\prov\SimpleProviderResolver;



class AliasProviderResolver
extends SimpleProviderResolver
{
	
	const VERSION = '0.3.2';
	
	
	
	/**
	 * Creates an alias between $id and $target
	 * @param string $id The new id
	 * @param string $target The existing id
	 * @return SimpleProviderResolver
	 * @throws \ErrorException if $id is not a nonempty string
	 * @throws \ErrorException if $target id not a nonempty string
	 * @throws \ErrorException if $target is not an existing reference
	 */
	public function setAlias($id, $target) {
		if (
			!is_string($id) || empty($id) ||
			!is_string($target) || empty($target) ||
			!array_key_exists($target, $this->_map)
		) throw new \ErrorException();
		
		$this->_map[$id] = $this->_map[$target];
		
		return $this;
	}
}
