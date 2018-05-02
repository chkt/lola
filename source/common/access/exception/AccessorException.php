<?php

namespace lola\common\access\exception;

use lola\common\access\IAccessorSelector;



final class AccessorException
extends \Exception
implements IAccessorException
{

	private $_selector;


	public function __construct(IAccessorSelector $selector) {
		$failureIndex = $selector->getResolvedLength();
		$unresolvedIndex = min($failureIndex + 1, $selector->getPathLength());

		$message = 'ACC unidentified error "%1$s"';

		if ($selector->hasAccessFailure()) $message = 'ACC no property "%1$s"["%2$s"]"%3$s"';
		else if ($selector->hasBranchFailure()) $message = 'ACC no branch "%1$s"["%2$s"]"%3$s"';

		parent::__construct(sprintf(
			$message,
			$selector->getPath(0, $failureIndex),
			$selector->getPath($failureIndex, $unresolvedIndex),
			$selector->getPath($unresolvedIndex)
		));

		$this->_selector = $selector;
	}


	public function getKey() : string {
		return $this->_selector->getPath();
	}

	public function getSelector() : IAccessorSelector {
		return $this->_selector;
	}
}
