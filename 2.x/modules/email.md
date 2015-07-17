Sending emails is something that almoast every site does, so PHPixie has it’s own Email module that wraps around the powerful Swift Mailer library. You can install it via composer by adding this to your _composer.json_ file:

```php
{
	"require":{
	//other requirements
	"phpixie/email":"2.*@dev"
	}
}
//Remember to run:
//php composer.phar update -o --prefer-dist
//to update your vendors
```

And add it to your $pixie:

```php
// /classes/app/pixie.php
namespace App;
class Pixie extends \PHPixie\Pixie {
	protected $modules = array(
		//Other modules...

		'email' => '\PHPixie\Email'
	);
}
```

The configuration supports multiple profiles, just like database does:

```php
// /assets/config/email.php
return array(
	'default' => array(

		//Type can be either 'smtp', 'sendmail' or 'native'
		'type' => 'native',

		//Settings for smtp connection
		'hostname' => 'localhost',
		'port' => '25',
		'username' => null,
		'password' => null,
		'encryption' => null, // 'ssl' and 'tls' are supported
		'timeout' => null, // timeout in seconds, defaults to 5

		//Sendmail command (for sendmail), defaults to "/usr/sbin/sendmail -bs"
		'sendmail_command' => null,

		//Additional parameters for native mail() function, defaults to "-f%s"
		'mail_parameters' => null
	)
);
```

After you configured your connection you can send email like this:

```php
//Send email to tinkerbell@phpixie.com
//From trixie@phpixie.com
//With subject "Hello" and
//"Hi, Tinkerbell" text
$pixie->email->send('tinkerbell@phpixie.com', 'trixie@phpixie.com', "Hello","Hi, Tinkerbell");

//You can also specify a name for both the sender an receiver
//like this
$pixie->email->send(array('tinkerbell@phpixie.com'=>"Tinkerbell"),
	array('trixie@phpixie.com'=>"Trixie"),
	"Hello","Hi, Tinkerbell");

//It is also possible to specify
//multiple recepients, CC and Bcc
$pixie->email->send(array(
	'to' => array(
		'tinkerbell@phpixie.com',
		 array('trixie@phpixie.com' => 'Trixie')
	       ),
	'cc' => array(
		'fairy@phpixie.com'
	       ),
	'bcc' => array(
	        array('pixie@phpixie.com' => 'Pixie')
	       )
	),
	array('trixie@phpixie.com'=>"Trixie"),
	"Hello","Hi, Tinkerbell");

//You can send HTML messages by 
//setting a html flag to 'true'
$pixie->email->send('tinkerbell@phpixie.com', 'trixie@phpixie.com', "Hello",$html_text,true);
```

Since it’s just a wrapper over the Swift Mailer library you will be able to use it directly:

```php
$mailer=$pixie->email->mailer();
//Now you can use the $mailer
//as as a Swift_Mailer instance
```