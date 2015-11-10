<?php

namespace chkt\prov;

use \chkt\prov\AProvider;

use \chkt\app\App;

use \MongoDB\Client;



class MongoDBProvider extends AProvider {
	
	public function __construct(App $app) {
		parent::__construct(function($id) use ($app) {
			$props = $app->getProperty('mongodb');
			
			if (!array_key_exists($id, $props)) throw new \ErrorException();
			
			$config = $props[$id];
			
			$client = new Client(
				'mongodb://' .
				$config['host'] . ':' .
				$config['port']
			);
			
			$db = $client->selectDatabase($config['dbname']);
			
			return $db;
		});
	}
}
