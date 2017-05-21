<?php

namespace lola\type\data;

use lola\type\data\IAccessException;



interface ITreeAccessException
extends IAccessException
{

	public function& useResolvedItem();


	public function getResolvedKey() : string;


	public function getMissingPath() : array;

	public function getResolvedPath() : array;
}
