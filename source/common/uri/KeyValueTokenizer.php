<?php

namespace lola\common\uri;

use eve\common\ITokenizer;



class KeyValueTokenizer
implements ITokenizer
{

	const DEFAULT_DELIMITER_KEY = '&';
	const DEFAULT_DELIMITER_VALUE = '=';
	const DEFAULT_DELIMITER_LIST = ',';



	private $_keyDelimiter;
	private $_valueDelimiter;
	private $_listDelimiter;


	public function __construct(
		$keyDelimiter = self::DEFAULT_DELIMITER_KEY,
		$valueDelimiter = self::DEFAULT_DELIMITER_VALUE,
		$listDelimiter = self::DEFAULT_DELIMITER_LIST
	) {
		$this->_keyDelimiter = $keyDelimiter;
		$this->_valueDelimiter = $valueDelimiter;
		$this->_listDelimiter = $listDelimiter;
	}


	public function parse(string $keyValue) : array {
		$kv = [];
		$v = [];
		$pieces = explode($this->_keyDelimiter, $keyValue);

		foreach ($pieces as $piece) {
			$parts = explode($this->_valueDelimiter, $piece, 2);
			$pieceHasKey = count($parts) === 2;
			$list = $parts[$pieceHasKey ? 1 : 0];
			$values = explode($this->_listDelimiter, $list);

			if (!$pieceHasKey) {
				if (array_search('', $values) !== false) throw new \ErrorException(sprintf('PRS empty keyless value "%s" in "%s"', $piece, $keyValue));

				array_push($v, ...$values);
			}
			else {
				$key = $parts[0];

				if (empty($key)) throw new \ErrorException(sprintf('PRS empty key "%s" in "%s"', $piece, $keyValue));

				if (!array_key_exists($key, $kv)) $kv[$key] = count($values) > 1 ? $values : $values[0];
				else {
					$item = is_array($kv[$key]) ? $kv[$key] : [ $kv[$key] ];

					array_push($item, ...$values);

					$kv[$key] = $item;
				}
			}
		}

		if (count($v) !== 0) array_push($kv, ...array_unique($v));

		return $kv;
	}
}
