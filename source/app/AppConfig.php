<?php

namespace lola\app;

use lola\common\IComponentConfig;
use lola\common\access\TreeAccessor;
use lola\common\access\exception\ITreeAccessorException;



class AppConfig
extends TreeAccessor
implements IComponentConfig
{

	const CONFIG_ROOT_PATH = 'rootPath';
	const CONFIG_VERBOSITY = 'verbosity';



	public function __construct(array $data = []) {
		parent::__construct($data);
	}


	private function _getRootPath() {
		$segs = explode(DIRECTORY_SEPARATOR, filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));

		return implode(DIRECTORY_SEPARATOR, array_slice($segs, 0, count($segs) - 2));
	}


	protected function _handlePropertyException(ITreeAccessorException $ex) {
		$data =& $ex->useResolvedItem();
		$key = $ex->getKey();

		if ($key === 'verbosity') $data['verbosity'] = 0;
		else if ($key === 'rootPath') $data['rootPath'] = $this->_getRootPath();
		else return false;

		return true;
	}
}
