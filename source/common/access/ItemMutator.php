<?php

namespace lola\common\access;

use eve\common\projection\IProjectable;
use eve\common\access\IKeyMutator;
use eve\common\access\IItemAccessor;
use eve\common\access\IItemMutator as IParentMutator;
use lola\common\base\ArrayOperation;
use lola\common\projection\IFilterProjectable;



class ItemMutator
extends TraversableAccessor
implements IItemMutator
{

	public function removeKey(string $key) : IKeyMutator {
		$this
			->_select($key)
			->unlinkRecursive();

		return $this;
	}

	public function setItem(string $key, $item) : IParentMutator {
		$this
			->_select($key)
			->linkAll()
			->setResolvedItem($item);

		return $this;
	}


	public function copy(IProjectable $source) : IItemAccessor {
		$data =& $this->_useData();
		$data = $source->getProjection();

		return $this;
	}


	public function merge(IProjectable $a, IProjectable $b) : IItemAccessor {
		$data =& $this->_useData();
		$data = $this
			->_getMethodProxy()
			->callMethod(ArrayOperation::class, 'merge', [
				$a->getProjection(),
				$b->getProjection()
			]);

		return $this;
	}

	public function mergeAssign(IProjectable $b) : IItemMutator {
		return $this->merge($this, $b);
	}


	public function filter(IFilterProjectable $source, array $keys) : IItemAccessor {
		$data =& $this->_useData();
		$data = $source->getProjection($keys);

		return $this;
	}

	public function filterSelf(array $keys) : IItemMutator {
		return $this->filter($this, $keys);
	}


	public function insert(IProjectable $target, IProjectable $source, string $key) : IItemAccessor {
		return $this
			->copy($target)
			->setItem($key, $source->getProjection());
	}

	public function insertAssign(IProjectable $source, string $key) : IItemMutator {
		return $this->setItem($key, $source->getProjection());
	}


	public function select(IItemAccessor $source, string $key) : IItemAccessor {
		$value = $source->getItem($key);

		if (!is_array($value)) throw new \ErrorException(sprintf('ACC invalid accessor root "%s"', $key));

		$data =& $this->_useData();
		$data = $value;

		return $this;
	}

	public function selectSelf(string $key) : IItemMutator {
		return $this->select($this, $key);
	}
}
