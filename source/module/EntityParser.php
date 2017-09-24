<?php

namespace lola\module;



final class EntityParser
implements IEntityParser
{

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

		return $res;
	}
}
