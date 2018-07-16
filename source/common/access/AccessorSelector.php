<?php

namespace lola\common\access;



class AccessorSelector
implements IAccessorSelector
{

	private $_separator;

	private $_path;
	private $_items;

	private $_pathLength;
	private $_itemLength;
	private $_resolvedLength;


	public function __construct(string $separator = '.') {
		if (empty($separator)) throw new \ErrorException();

		$this->_separator = $separator;

		$this->_path = [];
		$this->_items = [];

		$this->_pathLength = 0;
		$this->_itemLength = 0;
		$this->_resolvedLength = 0;
	}


	public function isResolved() : bool {
		return
			$this->_pathLength !== 0 &&
			$this->_pathLength === $this->_resolvedLength;
	}

	public function hasAccessFailure() : bool {
		return $this->_itemLength - $this->_resolvedLength === 1;
	}

	public function hasBranchFailure() : bool {
		return
			$this->_resolvedLength !== $this->_pathLength &&
			$this->_resolvedLength === $this->_itemLength;
	}


	public function getPath(int $index0 = 0, int $indexN = null) : string {
		if (is_null($indexN)) $indexN = $this->_pathLength;

		if ($index0 < 0 || $indexN < $index0 || $indexN > $this->_pathLength) throw new \ErrorException();

		$segments = array_slice($this->_path, $index0, $indexN - $index0);

		return implode($this->_separator, $segments);
	}

	public function getPathLength() : int {
		return $this->_pathLength;
	}

	public function getResolvedLength() : int {
		return $this->_resolvedLength;
	}


	public function getResolvedItem() {
		if ($this->_pathLength === 0) throw new \ErrorException('ACC no selection');

		$index = $this->_resolvedLength - 1;

		return $this->_items[$index][$this->_path[$index]];
	}

	public function setResolvedItem($item) : IAccessorSelector {
		if ($this->_pathLength === 0) throw new \ErrorException('ACC no selection');

		$index = $this->_resolvedLength - 1;

		$ref =& $this->_items[$index][$this->_path[$index]];
		$ref = $item;

		return $this;
	}


	public function select(array& $source, string $key) : IAccessorSelector {
		$limit = $this->_separator;

		if (strpos(
			$limit . $key . $limit,
			$limit . $limit
			) !== false) throw new \ErrorException(sprintf('ACC degenerate key "%s"', $key));

		$path = explode($limit, $key);
		$items = [];

		$data =& $source;
		$resolved = 0;

		foreach ($path as $segment) {
			if (!is_array($data)) break;

			$items[] =& $data;

			if (!array_key_exists($segment, $data)) break;

			$data =& $data[$segment];
			$resolved += 1;
		}

		$this->_path = $path;
		$this->_items = $items;

		$this->_pathLength = count($path);
		$this->_itemLength = count($items);
		$this->_resolvedLength = $resolved;

		return $this;
	}


	public function linkTo(int $index) : IAccessorSelector {
		$pathLength = $this->_pathLength;

		if ($index < 1 || $index > $pathLength) throw new \ErrorException();

		$path = $this->_path;
		$items =& $this->_items;
		$last = min($index, $pathLength - 1);

		for ($i = $this->_itemLength - 1; $i < $last; $i += 1) {
			$next = [];
			$items[$i][$path[$i]] =& $next;
			$items[$i + 1] =& $next;

			unset($next);
		}

		if ($index === $pathLength) $items[$last][$path[$last]] = null;

		$this->_itemLength = $last + 1;
		$this->_resolvedLength = $index;

		return $this;
	}


	public function linkAll() : IAccessorSelector {
		return $this->linkTo($this->_pathLength);
	}


	public function unlinkAt(int $index) : IAccessorSelector {
		if ($index < 0 || $index >= $this->_pathLength) throw new \ErrorException();

		$path = $this->_path;
		$items =& $this->_items;

		for ($i = $this->_itemLength - 1; $i >= $index; $i -= 1) {
			unset($items[$i][$path[$i]]);
			unset($items[$i + 1]);
		}

		$this->_itemLength = $index + 1;
		$this->_resolvedLength = $index;

		return $this;
	}

	public function unlinkRecursive() : IAccessorSelector {
		for ($i = $this->_itemLength - 1; $i > -1; $i -= 1) {
			if (count($this->_items[$i]) === 1) continue;

			$this->unlinkAt($i);

			break;
		}

		return $this;
	}
}
