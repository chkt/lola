<?php

namespace lola\app;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\inject\IInjectableIdentity;


class AppFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'entityParserName' => \lola\module\EntityParser::class,
			'providers' => [
				'core' => \lola\app\CoreProvider::class,
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

		$driver
			->getInstanceCache()
			->setItem(CoreProvider::class . ':' . IInjectableIdentity::IDENTITY_SINGLE, $driver);

		$injector = $driver->getInjector();

		$app = $injector->produce(App::class, [
			'driver' => $driver,
			'component' => $core->newInstance(AppConfig::class, [ $config['global'] ])
		]);

		$injector->produce($config['errorSourceName']);

		return $app;
	}
}
