#Lola
A minimalistic application framework using modularization, providers,
dependency injection, mvc and services.

##Install
```sh
$ php composer.phar install chkt/lola
```

##Use

###Initialize your application

The App object is a singleton representing your essential application configuration,
as well as references to the module registry, the injector and the locator.
You can extend your app object from \lola\app\App or implement \lola\app\IApp.

```php
<?php //App.php

namespace app\app;


final class App
extends \lola\app\App
{

}
```

To do anything useful with the framework, you will have to create an app object,
which will do basic bootstrapping and initialization of its elementary facilities.

```php
<?php //index.php

require_once '../vendor/chkt/lola/source/app/AppLoader.php';
```

If you haven't yet initialized any autoloader, require lola\app\AppLoader.
The AppLoader will do essential bootstrapping and provide a reference to the application object.

```php
$app = \lola\app\AppLoader::Init([
	'rootOffset' => '..',
```

The Apploader will provide your application with elemental configuration data.
You can put any config keys that you need and later retrieve them by invoking $app->getProperty('myProperty').

Additionally there are a few keys that will influence the bootstrap process.
'rootOffset' defines the path of your entry point relative to the application root folder

```php
    'composer' => true,
```

Should we use the composer autoloader. If you already required the composer autoloader yourself, this should be false.

```php
    'exceptionPage' => '/source/server/app/view/error500.html',
```

Application relative path to the 500 exception page.

```php
    'locator' => [
    	'controller' => '\\lola\\ctrl\\ControllerProvider',
    	'service' => '\\lola\\service\\ServiceProvider'
    ]
```

The qualified names of the providers available to the locator.

```php
])->getApp();

$app
	->useLocator()
	->using('service')
	->using('router')
	->useRouter()
	->enterRoutes(filter_input(INPUT_SERVER, 'REQUEST_URI'));
```

Now you can retrieve your reference to the application object
and enter your application (by accessing the router service for example).

###Entity identifiers

Lola uses URI formated identifier strings to identify locateable entities within the application.
The string `entityType://moduleId/path/to/entityName?entityId` will retrieve the entity
of type `entityType` from module `moduleId` at the path `path/to/entityName` and the unique id `entityId`.

`entityType` can be any type of configured locateable resource type,
`moduleId` is the canonical name of the module,
the path represents the path to the class within its local tree -
the forward slashes will be converted to backslashes to conform to php naming conventions.

The framework will do its best to aggressively infer attributes of the requested entity.
In any place where the type is implicit, specifying `entityType` is unneccessary.
When the `moduleId` is not specified, the module registry will search for the class within the registered modules.
`entityId` is only required when needed to configure the entity, and the `entityName` can often be reduced to a simple alphanumeric string.

###Modules

Lola uses Modules, Dependency Injection and Providers.

To create your own module, create a Module class in to root of your module folder structure.

```php
 
namespace myModule;

use lola\module\AModule;

use myOtherModule\MyService;


final class Module
extends AModule
{
```

For convenience, you can just extend from lola\module\AModule. Alternatively you can implement \lola\module\IModule.

```php
	static public function getDependencyConfig(array $config) {
		return [[
			'type' => Injector::TYPE_SERVICE,
			'id' => 'my'
		]];
	}
```

Define your dependencies inside ::getDependencyConfig(). All defined dependencies will be available inside ->__construct()
	
```php
	public function __construct(MyService& $mySerivce) {
		$myService->doModuleTask('myModule');
	}
```

Inside the constructor you can run arbitrary code, for example add a folder of template files to your view service. 

```php
	public function getModuleConfig() {
		return [
			'locator' => [
				'controller' => [
					'path' => 'controller',
					'prefix' => '',
					'postfix' => 'Controller'
				]
			]
```

Define the locations of your controllers, services and other locateable resources inside ->getModuleConfig().
All defined resources will automatically be available to providers and the injector.

The sample contains a custom configuration for the controllers belonging to the module with all settings reflecting the default settings for controllers.

```php
			'config' => [
				'service:my' => function(MyService& $myService) {
					$myService->configure('myModule');
				}
			]
		];
	}
}
```

You can also add additional configuration inside your module config.
All configuration will be added to the module registry and only invoked once an actual instance of the referenced entity is created.

This way you can avoid creating and configuring services that will not be used later 
as well as dependency loops when trying to configure an entity that is defined by the current module.

###Controllers
```php
<?php //MyController.php

namespace myModule\controller;

use lola\ctrl\AController;
use lola\inject\Injector;

use lola\route\Route;


class MyController
extends AController
{

	static public function getDependencyConfig(array $config) {
		return [[
			'type' => Injector::TYPE_INJECTOR
		],[
			'type' => Injector::TYPE_SERVICE,
			'id' => '//myOtherModule/my'
		]];
	}
```

All Controllers extend from /lola/ctrl/AController, which itself is an injectable.

```php


	private $myService = null;


	public function __construct(
		Injector& $injector,
		MyService& $myService
	) {
		$this->myService = $myService;
		
		$this->setRequestTransform($injector->produce('\\myModule\\controller\\MyControllerRequestTransform'));
		$this->setReplyTransform($injector->produce('\\myModule\\controller\\MyControllerReplyTransform'));
	}
```

Controllers use request and reply transforms to setup the state of the controller prior to running the requested action.
All transforms extend from /lola/ctrl/ControllerTransform.
When you first create a controller, its transforms will be empty.

When entering an action on a controller by calling $ctrl->enter(),
first the request transform will be executed on the controller,
then the action will be invoked and last the reply transform will be executed.
Only methods ending in *Action will be enterable. 

If your controller is run in reaction to a http request,
you could for example set up your reply transform to render the view with the return value of your action,
and then use the \lola\http\HttpReply instance of the controller to send the rendered view.

```php
	
	
	public function myAction(Route& $route) {
		$this->myService->doSomething($route->getParam('param'));
	}
}
```

###State Transforms

State transforms are simple statemachines representing certain setup task inside the application,
like for example setting up state for a controller before executing an action.

```php
<?php //MyRequestTransform.php

namespace app\ctrl;

use lola\ctrl\ControllerProcessor;
use lola\inject\IInjectable;

class MyRequestTransform
extends ControllerProcessor
implements IInjectable
{
	
	static public function getDependencyConfig(array $config) {
		return [];
	}
```

State transforms are not by default injectable, but you can easily augment them with this ability.
The transform will be home to the state graph as well as to the actual code implementing the state transitions. 

```php
	public function __construct() {
		parent::__construct([
			'start' => [
				'next' => [
					'success' => 'step1'
				]
			]
			'step1' => [
				'transform' => 'foo',
				'next' => [
					'success' => '',
					'failure' => 'fallback
				]
			],
			'fallback' => [
				'transform' => 'bar',
				'next' => [
					'success' => ''
				]
			]
		]);
	}
```

The state graph defines the order in which the transform should be executed
under different scenarios.

It is recommended to have a `start` state in every transform, since the start state will automatically run if no other state is handed to the instances `->process()` method.

If a state contains a `transform` property, the state transform will try to execute the method with the value of `transform` concatenated with `Step`.
So for example if the transform property contains the value `foo`, the state transform will try to enter the instance method named `fooStep`.

Transform methods can optionally return a return value. The return value will be mapped to the values in `next`.
If the method return nothing or null, its return value will be mapped to `success`. If the value of the next property for the return value is the empty string,
no further states will be entered.

```php

	public function fooStep(MyEntity& myEntity) {
		//...
	}
	
	public function barStep(MyEntity& myEntity) {
		//...
	}
}
```

The actual transformation functions are members of the class. `myEntity` can be set using the instances `->setTarget()` method.
For transforms that are executed as part of the framework defined application flow, this happens automatically.

###Services

All services extend lola\service\AService. Services are not singletons,
but the default service provider uses \lola\prov\SimpleProviderResolver
which will ensure that you will always receive the same service instance when supplying the same service id

```php
<?php //MyService.php

namespace app\service;

use lola\service\AService;

final class MyService
extends AService
{

	static public function getDependencyConfig(array $config) {
		return [];
	}
	
	
	public function doSomething() {
		//...
	}
}
```

Since Services can range from being a mere collection of related functions
to providing models, to exposing complex application logic,
no common interface is provided.
All your application has to ensure is that all services are injectable.