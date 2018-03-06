<?php

namespace lola\module;

use eve\common\ITokenizer;



final class EntityParser
implements IEntityParser
{


	private  $_configParser;


	public function __construct(ITokenizer $configParser) {
		$this->_configParser = $configParser;
	}


	public function parse(string $entity, string $end = self::COMPONENT_TYPE) : array {
		if (empty($entity)) throw new \ErrorException();

		$res = [];
		$offset = 0;
		$segs = parse_url($entity);

		if ($segs === false) throw new \ErrorException(sprintf('ENT malformed entity "%s"', $entity));

		$map = [
			self::COMPONENT_TYPE => ['key' => 'scheme', 'offset' => 1, 'end' => self::COMPONENT_LOCATION],
			self::COMPONENT_MODULE => ['key' => 'host', 'offset' => 2, 'end' => self::COMPONENT_DESCRIPTOR],
			self::COMPONENT_NAME => ['key' => 'path', 'offset' => 0],
			self::COMPONENT_CONFIG => ['key' => 'query', 'offset' => 1]
		];

		foreach ($map as $name => $item) {
			$key = $item['key'];

			if (array_key_exists($key, $segs)) {
				$res[$name] = $segs[$key];
				$offset += strlen($segs[$key]) + $item['offset'];
			}
			else $res[$name] = '';

			if ($end !== $name || !array_key_exists('end', $item)) continue;

			$res[$item['end']] = substr($entity, $offset);

			break;
		}

		if (array_key_exists(self::COMPONENT_CONFIG, $res)) {
			$res[self::COMPONENT_CONFIG] = empty($res[self::COMPONENT_CONFIG]) ?
			[] :
			$this->_configParser->parse($res[self::COMPONENT_CONFIG]);
		}

		return $res;
	}
}
