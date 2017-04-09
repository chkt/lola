<?php

namespace lola\type\query;



interface IDataQuery
{

	const OP_EQ = 1;
	const OP_NEQ = 2;
	const OP_GT = 3;
	const OP_GTE = 4;
	const OP_LT = 5;
	const OP_LTE = 6;
	const OP_EXISTS = 7;



	public function getRequirements() : array;

	public function setRequirements(array $requirements) : IDataQuery;


	public function match(array $data) : bool;
}
