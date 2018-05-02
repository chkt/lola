<?php

namespace lola\app;

use lola\common\IComponentConfig;
use lola\common\access\IAccessorSelector;
use lola\common\access\ItemAccessor;



class AppConfig
extends ItemAccessor
implements IComponentConfig
{

	const CONFIG_ROOT_PATH = 'rootPath';
	const CONFIG_VERBOSITY = 'verbosity';



	private function _getRootPath() {
		$segs = explode(DIRECTORY_SEPARATOR, filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));

		return implode(DIRECTORY_SEPARATOR, array_slice($segs, 0, count($segs) - 2));
	}


	protected function _handleSelectorFailure(IAccessorSelector $selector) {
		$key = $selector->getPath();
		$value = null;

		if ($key === self::CONFIG_VERBOSITY) $value = 0;
		else if ($key === self::CONFIG_ROOT_PATH) $value = $this->_getRootPath();

		if (!is_null($value)) $selector
			->linkAll()
			->setResolvedItem($value);
	}
}
