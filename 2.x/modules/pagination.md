Splitting content items into pages is a trivial task that is done on practically every website. This module will allow you to quickly set up pagination of any kind of items and it’s also well integrated with PHPixie ORM module. First install it like any other PHPixie module by adding this to your _composer.json_ file:

```php
{
	"require":{
	//other requirements
	"phpixie/paginate":"2.*@dev"
	}
}
//Remember to run:
//php composer.phar update -o --prefer-dist
//to update your vendors
```

And add it to your $pixie:

```php
// /classes/App/Pixie.php
namespace App;
class Pixie extends \PHPixie\Pixie {
	protected $modules = array(
		//Other modules...

		'paginate' => '\PHPixie\Paginate'
	);
}
```

The most common case is paginating ORM items and it’s very straightforward. First we need to add a route that would allow us to get pretty urls for our pages:

```php
// /assets/config/routes.php
return array(
	//We add a possible <page> parameter, make it optional and default to 1
	'fairies' => array('/fairies(/page-<page>)', array(
					'controller' => 'Fairies',
					'action' => 'index',
					'page' => 1
					)
				),
	//Other routes...
);
```

Now lets create a controller for the pagination.

```php
// /classes/App/Controller/Fairies.php
//...Rest of the controller
public function action_index() {
	//First we get the current page number from the URL parameters
	$current_page = $this->request->param('page');

	//Now we initialize an ORM model
	$fairies = $this->pixie->orm->get('fairy');

	//If you need to add some conditions to the
	//Items you can do that too
	$fairies->where('tree_id', 1);

	//Now we create a pager for our model
	//And set it to display 5 items per page
	$pager = $this->pixie->paginate->orm($fairies, $current_page, 5);

	//We also need to define a pattern for generating page URLs
	//the #page# in our pattern
	
	//Now we define the route to be used for url generation
	//It's also possible to use this method to pass additional
	//parameters for route generation
	$pager->set_url_route('fairies');

	//Pass the pager to the view
	$this->view->pager = $pager;
```

And a simple view:

```php
<h1>Fairies</h1>
<?php foreach($pager->current_items() as $fairy):?>
	<div>
		<?php echo $fairy->name; ?>
	</div>
<?php endforeach;?>

<!-- Render pager links -->
<ul>
	<?php for($i=1; $i<=$pager->num_pages; $i++): ?>
		<li>
			<a href="<?php echo $pager->url($i);?>"><?php echo $i;?></a>
		</li>	
	<?php endfor;?>
</ul>
```

That’s it. Of course you way want to customize the pager and add some styling to it.

**URL Generation**  
Using routes is not the only way of generating URLs for pages. You can also define a custom pattern or a callback function:

```php
//In this case the string pattern will be used
//with #page# being substituted with an actual number of the page
$pager->set_url_pattern("/page-#page#");

//Or you can define a function that will handle URL Generation
$pager->set_url_callback(function($page){
	return "/page-$page";
});
```

**Defining the URL of the first page**  
Obviously youw ould probably like to have the _/fairies_ URL for the first page instead of the _/fairies/page-1_ that would get generated. You can achieve this by passing an additional parameter to the constructor defining the URL of the first page.

```php
$pager = $this->pixie->paginate->orm($fairies, $current_page, 5, '/f');
```