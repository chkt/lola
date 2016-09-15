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
which will do basic bootstrapping and initialization of the elementary facilities.

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

###Modules

Lola uses Modules, Dependency Injection and Providers.

To create your own module, create a Module class in to root of your module folder structure.

```php
 
namespace myModule;

use lola\module\AModule;

use myOtherModule\myService;


class Module
extends AModule
{
```

For convenience, you can just extend from lola\module\AModule. Alternatively you can implement \lola\module\IModule.

```php
	getDependencyConfig(array $config) {
		return [[
			'type' => Injector::TYPE_SERVICE,
			'id' => 'my'
		]];
	}
```

Define your dependencies inside ::getDependencyConfig(). All defined dependencies will be available in ->__construct()
	
```php
	public function __construct(MyService& $mySerivce) {
		$myService->doModuleTask('myModule');
	}
```

Inside the constructor you can run arbitrary code, for example add a folder of template files to your view service. 

```
	public function getModuleConfig() {
		return [];
	}
}
```

Define the locations of your controllers, services and other locateable resources inside ->getModuleConfig().
All defined resources will automatically available to providers and the injector.


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

	static function getDependencyConfig(array $config) {
		return [[
			'type' => Injector::TYPE_INJECTOR
		],[
			'type' => Injector::TYPE_SERVICE,
			'id' => 'myOtherModule:my'
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
		
		$this->setRequestProcessor($injector->produce('\\myModule\\controller\\MyControllerRequestProcessor'));
		$this->setReplyProcessor($injector->produce('\\myModule\\controller\\MyControllerReplyProcessor'));
	}
```

Controllers use request and reply processors to perform task common to all actions within the controller,
or even within the whole application. All processors extend from /lola/ctrl/ControllerProcessor.
When you first create a controller, its processors will be empty.

When entering an action on a controller by calling $ctrl->enter(),
first the request processor will be executed on the controller,
then the action will be invoked and last the reply processor will be executed.
Only methods ending in *Action will be enterable. 

If your controller is run in reaction to a http request,
you could for example set up your reply processor to render the view with the return value of your action,
and then use the \lola\http\HttpReply instance of the controller to send the rendered view.

```php
	
	
	public function myAction(Route& $route) {
		$this->myService->doSomething($route->getParam('param'));
	}
}
```

###Services

All services extend lola\service\AService. Services are not singletons,
but the default service provider uses \lola\prov\SimpleProviderResolver
which will ensure that you will always receive the same service instance when supplying the same service id

```php
<?php //MyService.php

namespace app\service\MyService;

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