<?php

namespace lola\type;

use lola\type\IGenerateable;



interface IIterateable
extends IGenerateable
{

	public function getIndex() : int;


	public function& useIndex(int $index);

	public function& useOffset(int $offset);


	public function& useFirst();

	public function& usePrev();

	public function& useNext();
}
