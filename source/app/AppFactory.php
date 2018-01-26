<?php

namespace lola\app;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;



class AppFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'entityParserName' => \lola\module\EntityParser::class,
			'providers' => [
				'environment' => \lola\provide\EnvironmentProvider::class,
				'service' => \lola\service\ServiceProvider::class,
				'controller' => \lola\ctrl\ControllerProvider::class
			],
			'global' => [],
			'errorSourceName' => \lola\error\NativeErrorSource::class
		];
	}


	protected function _produceInstance(ICoreFactory $core, array $config) {
		$driverFactory = $core->newInstance(InjectorDriverFactory::class, [ $core ]);
		$driver = $driverFactory->produce($config);
		$injector = $driver->getInjector();

		$app = $injector->produce(App::class, [
			'driver' => $driver,
			'component' => $core->newInstance(AppConfig::class, [ $config['global'] ])
		]);

		$driverFactory->useReferenceSource()['app'] = $app;

		$injector->produce($config['errorSourceName']);

		return $app;
	}
}
