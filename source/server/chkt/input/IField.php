<?php

namespace lola\input;



interface IField {
	
	public function isInitial();
	
	public function isChanged();
	
	
	public function isEmpty();
	
	public function isNonEmpty();
	
	
	public function isMutable();
	
	public function isValidating();
	
	public function isValid();
	
	public function isSubmit();
	
	public function isMultiple();
	
	
	public function getName();
	
	
	public function getValue();
	
	public function setValue($value);
	
	
	public function getValues();
	
	public function setValues(Array $values);
	
	public function mapValues(Array $values);
	
	
	public function getData();
	
	
	public function invalidate($state);
}
