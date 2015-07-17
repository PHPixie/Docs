**Validate** provides you with a set of useful validation methods and an validator, you can install it via composer by adding this to your _composer.json_ file:

```php
{
	"require":{
	//other requirements
	"phpixie/validate":"2.*@dev"
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

		'validate' => '\PHPixie\Validate'
	);
}
```

Validate module comes with a lot of useful methods for validation, you can access them easily like this:

```php

//Checks if the parameter consists of letters and numbers only
$is_alpha_numeric = $pixie->validate->ruleset->rule_alpha_numeric('a1b2c3');
```

There are a lot of them that you may find useful, but the highlight of this module is the ability to validate arrays of data using a set of defined rules.

```php
//Validate POST data
$validator = $this->pixie->validate->get($this->request->post());

//Fairy name is required and must consist of letters only
$validator->field('name')
		->rules('filled', 'alpha');

//Password is required, must have at least 8 characters
$validator->field('password')
		->rule('filled')
		->rule('min_length', 8);

//Password confirmation is required, must be same as password
$validator->field('password_confirm')
		->rule('filled')
		->rule('same_as', 'password');

//Height is optional, so we don't set the 'filled' rule on it
$validator->field('height')
		->rule('numeric');

//Check if the fields are valid
$validator->valid();

//Get errors for each field
$validator->errors();
```

By default all fields are optional, so if don’t add a ‘filled’ rule and the field is empty it will always be considered valid. Some rules (like _min\_length_) require additional parameters, others (like _alpha\_numeric_) don’t. If you want to add some rules that don’t need extra parameters, you can add them using the _rules()_ method like in the above example. Rules with extra parameters must be added using _rule()_. For the full list of rules take a look at the _\PHPixie\Validate\Ruleset_ class.

You can also invert the rules. Meaning they will be considered valid when they don’t match and vice versa. This maye seem rather useless, but we’ll be using those in a conditional rules.

```php
//Age field must be 
$validator->field('age')
		->rule('!filled');

```

**Conditional Rules**  
You can define a set of conditions for when to apply particular rules. This is very usefull for forms where some fields depend on others. A condition is itself a field definition. All conditions have to match in order for the rules to be processed.

```php

//Height must always be filled
$validator->field('height')
		->rule('filled');

//Height must be between 2 and 5 inches if the 'type' field is 'pixie'
$validator->field('height')
		->rule('between',2,5);
		->condition('type')
			->rule('filled')
			->rule('equals','pixie');

//Or height must be between 7 and 10 inches if the 'type' field is 'fairy'
$validator->field('height')
		->rule('between',7,10);
		->condition('type')
			->rule('filled')
			->rule('equals','fairy');
```

Note that field definitions added to the same field of the form have an AND logic, so that the ‘filled’ rule will apply regardless of the ‘type’ field. Conditions make a lot of use of inverting rules, for example some field may be required when other is empty. This is usefull if you want to provide your users with a list of options and and ‘Enter your own’ text field where they can enter their own option.

```php
//Make own_option required
//only if selected_option is not set
$validator->field('own_option')
		->rule('filled')
		->condition('selected_option')
			->rule('!filled');
```

Of course you always could wrap those rules inside and _if_ statement and just apply them whenever you would need them. But the largest drawback of such an approach would be that it would be far less portable. This way you could set you could set your _Validator_ object once and then use it to validate different arrays of data like this:

```php
//Validate new data
$validator->input($data);
print_r($validator->errors());
```

**Custom rules**  
There are two ways of adding your own rules to the validator. The easy approach is to use the callback rule that takes a function as a parameter.

```php
$validator->field('username')
		->rule('callback', function($username){
			$is_valid = ... //Somehow validate the username;
			return $is_valid;
		})
```

The callback function will actually get called with three parameters, but you probably will need only the first one. The other two are the name of the field that is validated and $validator object which can be used to access other field values.

```php
$validator->field('max_flowers')
		->rule('callback', function($max_flowers, $field, $validator){

			//max_flowers must be greater than min_flowers
			return $max_flowers > $validator->get('min_flowers');
		})
```

Of course you can achieve the same by passing $validator inside a use() statement to the callback.

The second approach is adding your rule to the _Ruleset_ class. To do this you will have to extend \PHPixie\Validate\Ruleset to \App\Validate\Ruleset. Then you should also extend \PHPixie\Validate to \App\Validate, modify the _build\_ruleset()_ method to return your modified ruleset and use \App\Validate as a Validate module in \APP\Pixie. If you follow this approach remember to prefix your rule method with ‘rule\_’.

**Customizing errors**  
Fo each field definition you can set your own error message. That message will be returned when at least one rule doesn’t match.

```php
$validator->field('amount')
		->rule('numeric')
		->rule('between',5,10)
		->error("Amount is invalid.")
```

Sometimes you might detect that someone is passing some wrong data on purpose to try and break your app. For example someone might modify the hidden ‘id’ field of the form trying to modify a post he doesn’t have access to etc. In these occasions it’s not a good idea to tell the attacker what rule he didn’t succeed in bypassing, instead it’s much better to throw some sort of exception and log the attempt. The _throw\_on\_error()_ method allows you to mark aset of rules, so that they throw an exception if they aren’t valid.

```php
$validator->field('sensitive')
		->error("Attack detected.")
		->throw_on_error();
//Or a shortcut
$validator->field('sensitive')
		->throw_on_error("Attack detected.");
```

**Step-by-step validation**  
By default all rules for all field groups will be validated. You can mark a group of rules so they will only be validated if all previous rules defined for this field were valid. This will allow you to skip complex validations (like validating if the username is not already taken) if simpler rules are not met (e.g. username is too short). Just pass _true_ as the second parameter when defining a set of rules.

```php
$validator->field('username')
		->rules('filled','alphanumeric')
		->rule('min_length', 8);

//This will only be checked if the previous rules were passed
$validator->field('username', true)
		->rule('callback',function(){
			//Check if the username is unique
		});
```

Combining this with callbacks can bring great results

```php
$parent = null;
$validator->field('parent_id')
		->rule('callback', function($parent_id) use ($parent){
			$parent = ... //Get the parent model
			return $parent->loaded();
		})
		->error('Parent doesn't exist');

//Since we passed parent in a use() statement in previous callback
//It will be already present in the second one.
//Furthemore the following rule will run only if the first one was valid
$validator->field('parent_id', true)
		->rule('callback', function($parent_id) use ($parent){
			//Check if $parent can be a parent for the current item
		})
		->error('The parent specified is invalid');

```