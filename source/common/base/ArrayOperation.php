<?php

namespace lola\common\base;



class ArrayOperation
extends \eve\common\base\ArrayOperation
{

	static public function& iterate(array& $source) : \Generator {
		$keys = [ array_keys($source) ];
		$values = [ & $source ];

		$stackIndex = [0];
		$stackLength = [ count($source) ];
		$stackPath = [];

		for (
			$depth = 0;
			$depth >= 0;
		) {
			$index = $stackIndex[$depth]++;

			if ($index === $stackLength[$depth]) {
				array_pop($values);
				array_pop($keys);

				array_pop($stackIndex);
				array_pop($stackLength);
				array_pop($stackPath);

				$depth -= 1;

				continue;
			}

			$key = $keys[$depth][$index];
			$value =& $values[$depth][$key];

			$stackPath[] = $key;

			if (is_array($value)) {
				$keys[] = array_keys($value);
				$values[] =& $value;

				$stackIndex[] = 0;
				$stackLength[] = count($value);

				$depth += 1;

				continue;
			}

			yield implode('.', $stackPath) => $value;

			array_pop($stackPath);
		}
	}
}
