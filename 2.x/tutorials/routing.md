**Routing** allows you to customize your URLs by specifying which URL patterns refer to which controllers and actions. To add your own routes edit your configuration file _/assets/config/routes.php_. You will see that each route is represented by a name and an array with 2 items:

```php
'default' => array('(/<controller>(/<action>(/<id>)))', array(
				'controller' => 'fairies',
				'action' => 'index'
				)
			)
```

The name of each route **must be unique** , if there is more than one route with the same name only the last one will be used.

The first member of the array is the rule for this route. Before you learn about customizing the URL, let’s look at a quick example, the default route.

```php
(/<controller>(/<action>(/<id>)))
```

This means that the first part of the URL is treated as the name of the controller that should be used, the second part is the action and the last one is an _id_ parameter that we can pass along. For example the link _/fairies/view/4_ will fire _action\_view_ in _Fairies\_Controller_ and you can access the _id_ parameter by using:

```php
$this->request->param('id');
```

The _param_ method also accepts a second parameter, a default value, for situations where _id_ will not be set.

Now what do those brackets mean? The brackets are simply a way of marking that this part of the route is optional. Please not that slashes always go at the beginning of the url segment. So while this is valid:

```php
/<controller>(/<action>)
```

this is not:

```php
/<controller>/(<action>)
```

You can add any number of parameters to a route, the only rule here is that they must be enclosed in tags. By default each parameter can consist of both letters and numbers, but what if you want to specify a specific format, for example force it to be numeric? For such situations you can pass an array instead of a string as a route, like this:

```php
'default' => array(
			array(
				'/(<controller>(/<action>(/fairy-<uid>)))',
				array(
					'uid'=>'\d+'
				)
			),
			array(
				'controller' => 'home',
				'action' => 'index'
			)
		)
```

The ‘_\d+_‘ notation is _regular expression_ that means ‘one or more digits’. You can specify any regular expression that you need your parameter to match. This way the URL _/fairies/view/fairy-4_ will be accepted, but the url _/fairies/view/fairy-tinkerbell_ won’t. If the given URL doesn’t match the first route it will be matched against the next ones until a suitable Route is found. This is why you need to define more specific routes earlier, and more general ones later. **The default route should always be the last one.**

The second parameter is an array of defaults. Values from these array will be appended to the parameters fetched from the URL. This way you can specify the default controller and action to use. This way you can generate even more customized shortcuts, like this:

```php
'add' => array('/add', array(
			'controller' => 'fairies',
			'action' => 'add'
			)
		)
```

This will direct us to _action\_add_ in _Fairies\_Controller_ where we can add one more fairy to our site. You can also specify defaults for your custom parameters that will be used if they are not present in the URL:

```php
'view' => array('/view/<fairy>', array(
			'controller' => 'fairies',
			'action' => 'view',
			'fairy' => 'trixie'
			)
		)
```

If you notice that your routes are getting much too complex to use only patterns you can also pass a function as a second item of the route. This function will be passed the URL string on execution, and it must return an array of parameters or _False_ if the URL doesn’t match this route and should be processed by the next one. Let’s look at a simple example:

```php
'tinkerbell' => array(function($url){
			if($url=='tinkerbell' || $url=='small-green-fairy')
				return array('fairy' => 'tinkerbell');
			return false;
		},
		array(
			'controller' => 'fairies',
			'action' => 'view'
		)
	)
```

This way visiting /tinkerbell /small-green-fairy will both take us to Tinkerbells’ page.

**The main rule you need to follow is that after the routes’ parameters are extracted and the defaults are applied the resulting array must always have a _controller_ and an _action_ defined.**

**Matching only specific HTTP methods**  
In some cases, like developing REST applications, you may want certains routes to match only for specific HTTP methods. It’s very easy to achive this behavior, by specifying and additional parameter in the route definition, like this:

```php

'route1' => array('/url', array(
				'controller' => 'template',
				'action' => 'view'
			), 'POST' // Will only match for POST requests
		),
'route2' => array('/url', array(
				'controller' => 'template',
				'action' => 'view'
			), array('POST', 'PUT') // Will match for both POST and PUT
		),
```

**Generating URLs using routes**  
Routes can also be used in the opposite direction, to generate URLs. This allows you to avoid the problem of having to edit links inside your views every time you update a route. For example let’s take a look at the default route:

```php
'default' => array('(/<controller>(/<action>(/<id>)))', array(
			'controller' => 'home',
			'action' => 'index'
			)
		)
```

We can now generate a url like this:

```php

//Will generate /fairies/view/5
$this->pixie->router->get('default')->url(array(
	'controller'=>'fairies',
	'action'=>'view',
	'id'=>5
));

//Default parameters may be omitted
//So the following will generate /home/index/5
$this->pixie->router->get('default')->url(array('id'=>5));

//Optional parameters may be omitted to,
//so yyou can get /fairies/view like this:
$this->pixie->router->get('default')->url(array(
	'controller'=>'fairies',
	'action'=>'view'
));
```
