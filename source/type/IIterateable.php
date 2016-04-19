<?php

namespace lola\type;



interface IIterateable {
	
	public function getIndex();
	
	public function getLength();
	
	
	public function& useIndex($index);
	
	public function& useOffset($offset);
	
	
	public function& useFirst();
	
	public function& useLast();
	
	public function& usePrev();
	
	public function& useNext();
	
	
	public function& useItems();
}
