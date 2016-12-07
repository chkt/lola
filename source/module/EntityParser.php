<?php

namespace lola\module;



final class EntityParser
{

	const VERSION = '0.5.2';

	const PROP_TYPE = 'type';
	const PROP_LOCATION = 'location';
	const PROP_MODULE = 'module';
	const PROP_NAME = 'name';
	const PROP_ID = 'id';


	/**
	 * Returns the entity definition referenced by hash
	 * @param string $hash
	 * @return array
	 * @throws \ErrorException if $hash is empty
	 */
	static public function parse(string $hash) {
		if (empty($hash)) throw new \ErrorException();

		$segs = parse_url($hash);

		if ($segs === false) throw new \ErrorException('MOD: hash malformed - ' . $hash);

		return [
			self::PROP_TYPE => array_key_exists('scheme', $segs) ? $segs['scheme'] : '',
			self::PROP_MODULE => array_key_exists('host', $segs) ? $segs['host'] : '',
			self::PROP_NAME => array_key_exists('path', $segs) ? str_replace('/', '\\', trim($segs['path'], '/')) : '',
			self::PROP_ID => array_key_exists('query', $segs) ? $segs['query'] : ''
		];
	}

	/**
	 * Returns the entity type and compound identifier referenced by hash
	 * @param string $hash
	 * @return array
	 * @throws \ErrorException if $hash is empty
	 */
	static public function extractType(string $hash) {
		if (empty($hash)) throw new \ErrorException();

		$type = parse_url($hash, PHP_URL_SCHEME);

		if (!is_null($type)) $location = substr($hash, strlen($type) + 1);
		else {
			$type = '';
			$location = $hash;
		}

		return [
			self::PROP_TYPE => $type,
			self::PROP_LOCATION => $location
		];
	}
}
