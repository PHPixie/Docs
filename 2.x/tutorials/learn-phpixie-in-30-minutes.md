This small tutorial will get you started in developing with PHPixie in just 30 minutes. Actually if you have experience with any other PHP framework it will probably take much less. Hopefully you will see how simple, flexible and powerful this our pixie really is.

> First follow the [Download Instructions](http://phpixie.com/tutorials/download-instructions/ "Download PHPixie") to install PHPixie via Composer.

**Our little website**  
We will now create a small website for keeping track of fairies. We should be able to list fairies, view and add them. For that purpose we need to create a table with 3 columns: _id_, _name_ and _interests_. You can create that table yourself, or use a MySQL dump file from https://github.com/dracony/PHPixie-Sample-App/blob/master/fairies.sql . PHPixie is a MVC framework, MVC standing for _Model, View, Controller_, all of which we will learn about in a while. As we need to create three pages to manage fairies it’s time to learn about _Controllers_.

**Controllers and Routing**  
Controllers are the heart of your website, each controller represents a group of related pages, and each method inside a controller is one of those pages. Since all our pages are concerned with fairies we only need a single controller with three _actions_ (pages). The most simple example would be this:

```php
// /classes/App/Controller/Fairies.php
namespace App\Controller;

class Fairies extends \App\Page {

	//'index' is the default action
	public function action_index() {

		//The value of $this->response->body
		//Will be echoed to the user
		$this->response->body="This is the listing page";
	}

	public function action_view() {
		$this->response->body="You can view a single fairy on this page";
	}

	public function action_add() {
		$this->response->body="Here will be a form for adding fairies";
	}

}
```

When you visit a URL it is matched against a set of rules called _Routes_ that decide which _Controller_ and which _Action_ will be called. We will only have a single route defined in our routes.php config file, it should like this:

```php
// /assets/config/routes.php
return array(
	'default' => array('(/<controller>(/<action>(/<id>)))', array(

					//Make 'fairies' the default controller
					'controller' => 'fairies',

					//Default action
					'action' => 'index'
					)
				),
);
```

Which means that for example a URL like _http://localhost/fairies/view/1_ would trigger the _action\_view()_ inside the _Fairies_ controller, and pass it and _id_ parameter with the value of 1. The default action is defined to be _index_ and the default controller is _fairies_, so visiting _http://localhost/_ is the same as _http://localhost/fairies/index/_ . You can add any amount of complex routes yourself to this file, a detailed explanation of how to do so is in our Routing Tutorial, but for now let’s start adding HTML to our pages, that is what _Views_ are for.

**Views**  
Views are basically PHP template files, you can pass variables to the view from the controller and them render the view when needed. The most simple example would be this:

```php
// /classes/App/Controller/Fairies.php
public function action_index(){
	//Get the /assets/views/hello.php view
	$view = $this->pixie->view('hello');

	//Pass a variable to the view
	$view->message = 'hello';

	//Render the view and display it
	$this->response->body = $view->render();
}
``````php
// /assets/views/hello.php
<!-- $_() is a function that will escape and print a string. -->
<!-- It's the equivalent of "echo htmlentities($message)" -->
<h2><?php $_($message); ?></h2>
```

Notice the $pixie object that we used to get the view, you will encounter it quite frequently later on, especially whenever you access something. PHPixie doesn’t use static methods and properties, and discourages instantiating classes in random places, all global things are handled using the $pixie object, that is accessible almost everywhere.

**Page layout and our first controller**  
The flaw with using a separate view for each page is that you may end up copying common layout like header and footer to every page, which is not usable at all. Instead it’s convenient to have on main view that defines the general layout, and include subtemplates into it. It’s very easy to achieve using the _before()_ and _after()_ methods of the Controller. The skeleton application has them already implemented, but let’s take a closer look at it.

```php
// /classes/app/page.php
namespace App;

class Page extends \PHPixie\Controller {

	//This is where we store our view
	protected $view;

	//This function will execute before each action
	//We will use it to initialize a common layout
	public function before() {

		//Here we reference the /assets/view/main.php view
		$this->view = $this->pixie->view('main');
	}

	//This function will execute after each action
	public function after() {
		//It will render the view template and output
		//the response to the user
		$this->response->body = $this->view->render();
	}

}
```

For our view we will use Twitters’ Bootstrap CSS, so that we don’t have to worry about styles.

```php
// /assets/views/main.php
<!DOCTYPE html>
<html>
	<head>
		<title>Fairies</title>
		<link href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">
			<div class="span4 offset4">
				<h2>Fairies</h2>
				<!--
					Here we will include the file
					specified in the $subview
				 -->
				<?php include($subview.'.php');?>
			</div>
		</div>
	</body>
</html>
```

All our actual controller has to do now is pass a _$subview_ parameter to the view to specify which page to render on each action, optionally it can also pass some additional variables, e.g the name of the fairy etc.

**Setting up the database**  
Before we can manage the data inside the _fairies_ table, we need to configure a connection to the database. All configuration files are inside the _/assets/config_ folder, edit the db.php config file to suite your setup:

```php
// /assets/config/db.php
return array(
	'default' => array(
		'user'=>'root',
		'password' => '',
		'driver' => 'PDO',

		//We named our database 'pixies', if you called it
		//something else, just put that name instead.
		//'Connection' is required if you use the PDO driver
		'connection'=>'mysql:host=localhost;dbname=pixies',

		// 'db' and 'host' are required if you use Mysql driver
		'db' => 'pixies',
		'host'=>'localhost'
	)
);
```

Now let’s create a _model_ for our fairies.

**Models**  
A Model is a class that usually represents some entity, it may be a user, a post, a category, anything. In our case it will be a fairy. It takes a lot of code to make a model, you have to write methods for storing it’s data into the database, reading, deleting it, etc. Thankfully PHPixie will do all that for you using its ORM module that we already have installed via composer. All we have to do for transforming our _fairies_ table into a model is this:

```php
// /classes/app/model/fairy.php
namespace App\Model;

//PHPixie will guess the name of the table
//from the class name
class Fairy extends \PHPixie\ORM\Model {

}
```

**That is it!** We don’t have to worry ourselves with database access anymore! It’s all just a piece of cake from now on. There is much more that ORM can do, but that’s all that we need for this tutorial, check out the ORM guide for more information.

**Fairies Controller**  
Now that we have everything ready we can start working on managing out fairies. First let’s create a listing page to see all fairies that we have in our table:

```php
// /classes/app/controller/fairies.php
namespace App\Controller;

class Fairies extends \App\Page {

	public function action_index() {

		//Include the list.php subtemplate
		$this->view->subview = 'list';

		//Find all fairies and pass them to the view
		//ORM takes care of that
		$this->view->fairies = $this->pixie->orm->get('fairy')->find_all();
	}
}
```

Wasn’t that simple? It only took 2 lines of actual code. Now we need to create a subview in _/assets/views/list.php_ like this:

```php
// /assets/views/list.php
<table class="table table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th>Name</th>
		</tr>
	</thead>
	<tbody>
		<!--
			Display the ID and name of each fairy
			And link to her page
		 -->
		<?php foreach($fairies as $fairy):?>
			<tr>
				<td><?php $_($fairy->id);?></td>
				<td>
					<a href="/fairies/view/<?php echo $fairy->id;?>">
						<?php $_($fairy->name);?>
					</a>
				</td>
			</tr>
		<?php endforeach;?>
	</tbody>
</table>
<!-- Link to fairy creation form -->
<a href="/fairies/add" class="btn btn-success">Add a new fairy</a>
```

Viewing a single fairy is also simple and very similar to the listing page, all we need to do is get her ID from the URL and find her by that ID.

```php
// /classes/app/controller/fairies.php
namespace App\Controller;

class Fairies extends \App\Page {

	//... action_index() code ...//

	public function action_view() {

		//Show the single fairy page
		$this->view->subview = 'view';

		//Get the ID of the fairy from URL parameters
		$id = $this->request->param('id');

		//Find a fairy by ID and pass her to the view
		//ORM makes it very trivial too
		$this->view->fairy = $this->pixie->orm->get('fairy', $id);
	}

}
```

And the view, even simpler than the previous one:

```php
// /assets/views/view.php
<h3><?php echo $fairy->name;?></h3>
<p class="lead">
	<?php $_($fairy->interests);?>
</p>
<a href="/" class="btn btn-success">Back to the list of fairies</a>
```

Now for something a bit more complex, we have to be able to also add new fairies. It will take a bit more code to do that ( WHOLE 6 lines of code!!! =3 ), so this time let’s first look at the view:

```php
// /assets/views/add.php
<form method="POST">
	<fieldset>
		<legend>Add a new fairy</legend>
		<label>Name</label>
		<input name="name" type="text" placeholder="Type something…"/>
		<label>Interests</label>
		<div>
			<textarea name="interests"></textarea>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</fieldset>
</form>
```

What we have here is a simple form that submits its data using the POST method back to the same page. The controller will have to check if the form is submitted and process it, otherwise it should just display it.

```php
// /classes/app/controller/fairies.php
namespace App\Controller;

class Fairies extends \App\Page {

	//... action_index() code ...//

	//... action_view() code ...//

	public function action_add() {

		//If the HTTP method is 'POST'
		//it means that the form got submitted
		//and we should process it
		if ($this->request->method == 'POST') {

			//Create a new fairy
			$fairy = $this->pixie-> orm->get('fairy');

			//Set her name from the form POST data
			$fairy->name = $this->request->post('name');

			//Set her interests from the form POST data
			$fairy->interests = $this->request->post('interests');

			//Save her
			$fairy->save();

			//And redirect the user back to the list
			return $this->redirect('/');
		}

		//Show the form
		$this->view->subview = 'add';
	}

}
```

This is it! Now we can manage our fairies!

> You can get the full source code for this tutorial on [**https://github.com/dracony/PHPixie-Sample-App/**](https://github.com/dracony/PHPixie-Sample-App/ "PHPixie Sample App")

Now let’s dive in for some more theory =)

**The Big Picture**  
So here is how it all works:

- A URL is tested against a set of Routes to find the appropriate Controller and Action to Run
- Our Page Controller initializes the main view
- An action is the Fairies Controller executes, retrieves some data and passes it into the View
- The Page Controller renders the view and outputs it

One more important thing is the _$this->pixie_ object. It’s the heart of the framework, its dependency container. All classes are instantiated inside it, and it acts as the global scope. You will also notice that PHPixie never uses any static methods nor properties, things like that are also stored inside the pixie. We encourage you to follow the same pattern for better testability of your application. Try placing static and global things inside the Pixie. You can add anything to it by editing the _/classes/app/pixie.php_:

```php
// /classes/app/pixie.php
namespace App;

class Pixie extends \PHPixie\Pixie {

	//This is how we enable PHPixie modules
	//after they are installed
	protected $modules = array(
		'db' => '\PHPixie\DB',
		'orm' => '\PHPixie\ORM'
	);

	//You can add whatever methods/properties
	//you want here
}
```

**Start making it your own!**  
You’ve completed our little tutorial, now it’s time to delete the sample Models and Controllers and make something of your own. If you run into any problems, please let us know on the forums, and we will be sure to help you.

