<?php

namespace lola\module;



final class EntityParser
{
	
	const VERSION = '0.3.2';
	
	
	
	/**
	 * Returns the entity definition referenced by hash
	 * @param string $hash
	 * @return array
	 * @throws \ErrorException if $hash is not a nonempty string
	 */
	static public function parse($hash) {
		if (!is_string($hash) || empty($hash)) throw new \ErrorException();
		
		$segs = parse_url($hash);
		
		if ($segs === false) throw new \ErrorException('MOD: hash malformed - ' . $hash);

		return [
			'type' => array_key_exists('scheme', $segs) ? $segs['scheme'] : '',
			'module' => array_key_exists('host', $segs) ? $segs['host'] : '',
			'name' => array_key_exists('path', $segs) ? str_replace('/', '\\', trim($segs['path'], '/')) : '',
			'id' => array_key_exists('query', $segs) ? $segs['query'] : ''
		];
	}
}
