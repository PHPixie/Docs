Logging in users to your site and granting them different permissions based on their access level is a very common task, this module will allow you to do this in a very easy way. You can install it via composer by adding this to your _composer.json_ file:

```php
{

	"require":{

	//other requirements

	"phpixie/auth":"2.*@dev"

	}

}

//Remember to run:

//php composer.phar update -o --prefer-dist

//to update your vendors
```

And add it to your $pixie:

```php/classes/app/pixie.php
namespace App;

class Pixie extends \PHPixie\Pixie {

	protected $modules = array(

		//Other modules...

		'auth' => '\PHPixie\Auth'

	);

}
```

In a nutshell we will use it like this:

```php
$auth=$pixie->auth->service('config_name');

//Or if you want to use only the 'default' config

$auth=$pixie->auth;

//Check if the user is already logged in,

//if so print his name

if($auth->user())

	echo $auth->user()->name;

//Check if current user has the 'admin' role

$auth->has_role('admin');

//Attempt to log user in using his password

$logged_in = $auth->provider('password')

		->login($username,$password);

if($logged_in)

	echo 'welcome';
```

The interface is very intuitive and straightforward, all you need to do is configure it. But before we continue to configuring let’s take a quick look at how it all works.

**Login Providers**  
A Login Provider is a class that performs a certain type of user login. Currently there are two login providers present:

- _Password_ – that does the usual login/password login
- _Facebook_ – that authenticates the user using his facebook account

The Service class simply checks if the user is logged in with any of the login providers, that is how authorization is performed. We will take a closer look at them a bit later.

**Role Drivers**  
Checking for user roles is very similar to how Login Providers work. You configure a Role Driver and it takes care of looking up user roles. Current Role Drivers are:

- _Field_ – used when you keep the roles in your user table
- _Relation_ – used if a user has a has\_many or belongs\_to relationship with a role model

For example if you have a table with columns: _id_, _username_, _role_ (where _role_ can be either ‘user’ or ‘admin’) you would use the simple _Field_ driver.

The _Relation_ driver allows much more flexability. Let’s take a look at these ORM models. If the code below looks confusing to you, you should probably take a look at the [ORM Tutorial](http://phpixie.com/tutorials/orm/ "PHP ORM tutorial").

```php/classes/app/model/fairy.php
namespace App\Model;

class Fairy extends \PHPixie\ORM\Model{

	//If the fairy belongs to a single role

	//you can use the belongs_to relationship

	protected $belongs_to=array('role');

	//If you want each fairy to have multiple

	//roles you need to use the many-to-many relationship

	protected $has_many=array('role'=>array('through'=>'fairies_roles'));

}
```

```php/classes/app/model/role.php
namespace App\Model;

class Role extends \PHPixie\ORM\Model{

}
```

The _Relation_ driver takes care of checking for roles by their name. _Though each Auth can use many Login Providers it can have only a single Role Driver_.

**Let’s make it all work now!**  
Let’s assume our _fairies_ tables has these columns: _id_, _username_, _password_, _name_ and _fb\_id_. The _fb\_id_ field is for storing Facebook id. There is no _role_ field as we will use a many-to-many relationship as described before. First let’s start with configuring our Auth module:

```php/assets/config/auth.php
return array(

	'default' => array(

		'model' => 'fairy',

		//Login providers

		'login' => array(

			'password' => array(

				'login_field' => 'username',

				//Make sure that the corresponding field in the database

				//is at least 50 characters long

				'password_field' => 'password'

			),

			'facebook' => array(

				//Facebook App ID and Secret

				'app_id' => '138626646318836',

				'app_secret' => '49451a54b61464645321d9fbcb70000',

				//Permissions to request from the user

				'permissions' => array('user_about_me'),

			

	'fbid_field' => 'fb_id',

				//Redirect user here after he logs in

				'return_url' => '/fairies'

			)

		),

		//Role driver configuration

		'roles' => array(

			'driver' => 'relation',

			'type' => 'has_many',

			//Field in the roles table

			//that holds the models name

			'name_field' => 'name',

			'relation' => 'roles'

		)

	)

);
```

This is an example of how you can modify your _Page_ Controller to protect pages from unauthorized access:

```php/classes/app/page.php
<?php

namespace App;

abstract class Page extends \PHPixie\Controller{

	protected $auth;

	protected $view;

	public function before(){

		//This is our main page layout

		$this->view = $this->pixie->view('main');

	}

	public function after(){

		$this->response->body = $this->view->render();

	}

	//This method will redirect the user to the login page

	//if he is not logged in yet, or present him with a message

	//if he lacks the required role.

	protected function logged_in($role = null){

		if($this->pixie->auth->user() == null){

			$this->redirect('/fairies/login');

			return false;

		}

		if($role && !$this->pixie->auth->has_role($role)){

			$this->response->body = "You don't have the permissions to access this page";

			$this->execute=false;

			return false;

		}

		return true;

	}

}
```

Let’s also create a basic page layout that we can use:

```xml; title: /assets/views/main.php
<!DOCTYPE html>

<html>

	<head>

		<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"/>

	</head>

	<body>

		<div class="container">

			<div class="row">

				<div class="span8 offset2">

					<?php

						//Include the subview

						include($subview.'.php');

					?>

				</div>

			</div>

		</div>

	</body>

</html>
```

The hard part is over now, we can now create an actual simple controller with all features.

```php/classes/app/controller/fairies.php
namespace App\Controller;

class Fairies extends Page{

	public function action_index(){

		//Only allow users with the 'pixie' role.

		if(!$this->logged_in('pixie'))

			return;

		$this->view->fairy = $this->pixie->auth->user();

		//Include 'hello.php' subview

		$this->view->subview = 'hello';

	}

	public function action_login() {

		if($this->request->method == 'POST'){

			$login = $this->request->post('username');

			$password = $this->request->post('password');

			//Attempt to login the user using his

			//username and password

			$logged = $this->pixie->auth

					->provider('password')

					->login($login, $password);

			//On successful login redirect the user to

			//our protected page

			if ($logged)

				return $this->redirect('/fairies');

		}

		//Include 'login.php' subview

		$this->view->subview = 'login';

	}

	public function action_logout() {

		$this->pixie->auth->logout();

		$this->redirect('/fairies/login');

	}

}
```

Now to create the subviews, the success page:

```xml; title: /assets/views/hello.php
<h3>Hello, <?php echo $fairy->name;?></h3>

<div>

	<a href="/fairies/logout">Logout</a>

</div>
```

And the login page:

```xml; title: /assets/views/login.php
<form method="POST">

	<fieldset>

		<legend>Fairy login</legend>

		<label>Username</label>

		<input type="text" name="username" placeholder="Your name...">

		<label>Password</label>

		<input type="password" name="password">

		<span class="help-block">Enter the fairy realm.</span>

		<button type="submit" class="btn">Submit</button>

	</fieldset>

</form>

<!-- Link to facebook login -->

<div>

	<a href="/facebook">Login via Facebook</a>

</div>

<script>

	//A very basic way to open a popup

	function popup(link, windowname) {

		window.open(link.href, windowname, 'width=400,height=200,scrollbars=yes');

		return false;

	}

</script>

<div>

	<a href="/facebook/popup" onclick="return popup(this,'fblogin')">Login via Facebook Popup</a>

</div>
```

We are almost there! We still need to create a controller for the facebook login.

**Facebook Login**  
The Facebook Login Provider logs the user based on his access token, but we still need to obtain it. Luckily the Auth module comes with a full-featured Facebook Controller template that you can extend. The only thing you need to specify is what to do when a user that is not already in your database tries to log in with facebook. In most cases you either will send him to the registration page or just register him based on the information from his Facebook profile. Let’s try doing exactly that:

```php/classes/app/controller/facebook.php
class Facebook extends \PHPixie\Auth\Controller\Facebook{

	//This method gets called for new users

	//$access_token is the users access token

	//$return_url is the url to redirect the user to

	//after you are done (it can be null if you are

	//using the popup way, it means that the popup

	//will be closed after the login)

	//$display_mode is either 'page' or 'popup'

	public function new_user($access_token, $return_url, $display_mode) {

		//Facebook provider allows use to request

		//URLs with CURL, but you can use any other way of

		//fetching a URL here.

		$data = $this->provider

			->request("https://graph.facebook.com/me?access_token=".$access_token);

		$data = json_decode($data);

		//Save the new user

		$fairy = $this->pixie->orm->get('fairy');

		$fairy->name = $data->first_name;

		$fairy->fb_id = $data->id;

		$fairy->save();

		//Get the 'pixie' role

		$role=$this->pixie->orm->get('role')

			->where('name','pixie')

			->find();

		//Add the 'pixie' role to the user

		$fairy->add('roles',$role);

		//Finally set the user inside the provider

		$this->provider->set_user($fairy, $access_token);

		//And redirect him back.

		$this->return_to_url($display_mode, $return_url);

	}

}
```

Now you should have a fully functional login, that allows your users to login both via facebook and using login/password.  
Because the Auth module is itself very modular in structure it is very easy to add your own Login Providers or Role Drivers for it to work with.

**Generating Password Hashes**  
By default the Password Login Provider works with hashed and salted passwords. During user registration you should save the hashed version of the password instead of the raw one. It’s very easy to generate a password hash like this:

```php
$password='password123';

$hash = $this->pixie->auth->provider('password')->hash_password($password);

$user->password=$hash;

$user->save();
```

When a user tries to log in, the password he enters also gets hashed, and it’s the hashes that are compared, not the actual passwords. Password hashes add an extra layer of security, meaning that even if someone get’s the hold of your database he won’t be able to get real passwords. This also means that you won’t be able to read the actual passwords of your users, so if this sounds too much complicated you can always disable hashes by setting _hash\_method_ to _false_ in the _password_ section of the auth.php config file.

