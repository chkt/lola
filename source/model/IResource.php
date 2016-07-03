<?php

namespace lola\model;

use lola\model\IResourceQuery;

use lola\type\StructuredData;



interface IResource {
	
	const INTERFACE_VERSION = '0.2.4';
	
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	const OP_NONE = 0x0;
	const OP_CREATE = 0x4;
	const OP_READ = 0x8;
	const OP_UPDATE = 0x10;
	const OP_DELETE = 0x20;
	
	
	public function isLive();
	
	public function isDirty();
	
	
	public function wasCreated();
	
	public function wasRead();
	
	public function wasUpdated();
	
	public function wasDeleted();
	
	
	public function getData();
	
	public function setData(StructuredData $data);
	
	
	public function create(StructuredData $data);
	
	public function read(IResourceQuery $query);
	
	public function update();
	
	public function delete();
}
