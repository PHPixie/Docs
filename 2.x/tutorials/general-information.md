**Installing into a subfolder**

By default PHPixie expects to be installed into the root folder of your site. If you wish to install it into a subfolder e.g. _http://localhost/pixie/_ you need to make a few changes. First you have to put the main code somewhere outside of the web root, e.g. if your main site is installed to /var/www/site/ you can put PHPixie in /var/www/pixie-code . Then copy the contents of /var/www/pixie-code/web/ to wherever you want your site to be, in our case it’s the /var/www/site/pixie-web/ subfolder. Now there are a few small changes you need to make:

```php
// /var/www/site/pixie/.htaccess
//Change:
RewriteBase /
//To:
RewriteBase /pixie-web/
``````php
// /var/www/site/pixie-web/index.php
//Change:
$root = dirname( __DIR__ );
//To:
$root ='/var/www/pixie-code/';
``````php
// /var/www/site/pixie-code/classes/app/pixie.php
namespace App;

class Pixie extends \PHPixie\Pixie {

	public $basepath='/pixie-web/';
}
```

After this you can access your application using the _http://localhost/pixie-web/_ URL.

**Output escaping and helper functions**  
To escape output inside the view use a _$\_()_. There is nothing magical about this call, since it’s just a function assigned to the $\_ variable.  
It is defined inside the PHPixie\View\Helper class. A $helper instance of this class is always passed to every view, it also defines a set of aliases that will be created for the methods of the class. It may sound a bit complicated so let’s try understanding it by adding our own method to the helper. First we extend it:

```php
// /classes/App/View/Helper
namespace App\View;

class Helper extends \PHPixie\View\Helper{

    protected $aliases = array(
	'_' => 'output',
	//We alias the repeat method to $_r variable
	'_r' => 'repeat'
    );
    //Lets define a new method for our helper
    public function repeat($str, $times) {
        for($i = 0;$i<$times; $i++)
            echo $str;
    }
}
```

Then we have to tell Pixie to use your Helper instead of the default one:

```php
// /classes/App/Pixie.php
class Pixie extends \PHPixie\Pixie{
     public function view_helper(){
         return new \App\View\Helper;
     }
}
```

Now we can use it inside the view:

```php
<!-- Will print <br/> 3 times -->
<?php $_r('<br/>',3) ?>
```

**Controller Lifecycle**  
Before calling any _action_ controller will call it’s _before()_ method. And it will call its _after()_ method after the _action_ is called. These two methods don’t do anything by default, but you can override them to do common actions, like for example, checking if the user is logged in before executing and action. If at any point you will wish to stop controller execution, for example if the user is not logged in and must be redirected to login page, you may want to prevent any further execution of controller methods. To do this you must set the _execute_ property to _False_. Here is a simple example:

```php
namespace \App\Controller;

class Profile extends \PHPixie\Controller{
	public function before(){

		//Let's assume current_user() is a method
		//that returns a user if he is logged in
		//and False if he is not
		$user=$this->pixie->current_user();
		if(!$user){
			$this->response->redirect('/login');

			//Prevent action and after() from firing
			$this->execute=false;
			return;
		}
	}
	//... Rest of Controller code
}
```

**Debugging**  
To log some values for later view use the _$pixie->debug->log()_ function. Values you pass to it will be displayed alongside the stacktrace when an error occurs in the code. If you want logged values to be always viewable just display the contents of the _$pixie->debug->logged_ array somewhere in your view.

```php
<div id="logs">
	<?php foreach ($pixie->debug->logged as $log): ?>
		<pre><?php var_export($log);?></pre>
	<?php endforeach;?>
</div>
```

**Error Handling**  
When an error happens PHPixie displays an error page with the exception itself and a backtrace. Of course you will not want users to see that, so you can disable them during your Pixie initialization:

```php
// /classes/App/Pixie.php
namespace App;

class Pixie extends \PHPixie\Pixie {

	//This method will run just after bootstrap
	public function after_bootsrap(){
		$this->debug->display_errors = false;
	}
}
```

This way if an error happens the user will just get a simple message. You may want to customize this behavior you can override Pixie _handle\_exception()_ method:

```php
// /classes/App/Pixie.php
namespace App;

class Pixie extends \PHPixie\Pixie{

	public function handle_exception($exception){
		//If its a Page Not Found error redirect the user to 404 Page
		if ($exception instanceof \PHPixie\Exception\PageNotFound){
			header('Location: /sorry/'); 
		}else{
			$http_status = "503 Service Temporarily Unavailable";
			header($_SERVER["SERVER_PROTOCOL"].' '.$http_status);
			header("Status: ".$http_status);
			echo("Sorry, something is wrong");
		}
	}
}
```

**Autoloading**  
PHPixie uses Composer for autoloading, which follows the PSr-0 standard. This means that you should put class _App\Controller\Fairy_ into _/classes/App/Controller/Fairy.php_ file. Pay close attention to letter case, since Linux unlike Windows makes a distinction between ‘fairy.php’ and ‘Fairy.php’. If you’ll encounter that some classes that work on your Windows machine don’t work on a Linux host make sure to doublecheck your file and folder names.

**Separating Backend and Frontend**  
Separating your backend from frontend can be easily achieved using namespaces. Put all your admin controllers into the App\Admin\Controller namespace, and define a route like this:

```php
'admin' => array('/admin(/<controller>(/<action>(/<id>)))', array(
		'namespace' => 'App\Admin',
		'controller' => 'Fairies',
		'action' => 'index'
		)
	),
```

Now accessing _/admin/_ will call _App\Admin\Controller\Fairies_ controller while calling _/admin/pixies_ will route to _App\Admin\Controller\Pixies_. Having a separate Admin namespace also allows you to use it for backend specific business logic. Your frontend controllers will continue working normally, so _/fairies_ eould still route to _App\Controller\Fairies_.

