<?php

namespace lola\common\access;

use eve\common\access\exception\IAccessorException;



interface ITreeAccessorException
extends IAccessorException
{

	public function& useResolvedItem();


	public function getResolvedKeySegment() : string;

	public function getMissingKeySegment() : string;


	public function getResolvedPath() : array;

	public function getMissingPath() : array;
}
