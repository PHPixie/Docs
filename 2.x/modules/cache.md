Caching is a great way to reduce the load on your webserver and make your site run faster. PHPixies’ cache module comes with support of 4 drivers:

- APC
- Database
- File
- XCache

Of course APC and XCache will provide you with the most speed, but they require additional plugins to be added to your PHP installation. Caching into files works great for large data like arrays, HTML fragments, etc. Storing into the database is useful if you want to have your cache easily portable (as you can cache into SQLite files) and works best for scenarios where you have to cache a lot of small data, e.g. number of users online etc.

Using this module is very simple, first you need to configure your cache:

```php
// /application/config/cache.php
<?php
return array(
	'default' => array(
		
		//Supprted drivers are: apc, database, file, xcache
		'driver' => 'file',
		
		//Default liefetime for cached objects in seconds
		'default_lifetime' => 3600,

		//Cache directory for 'file' driver
		'cache_dir' => ROOTDIR.'/modules/cache/cache/',
		
		//Database connection name for 'database' driver
		'connecton' => 'default'
	)
);
```

You can define more than one caching profile, but the ‘default’ one can be easily accessed via these methods:

```php
//Caching an array of fairies with 7000 seconds lifetime
Cache::set('fairies',array('Tinkerbell','Trixie'),7000);

//Retrieving fairies from cache. If they are not found
//in cache or have expired return the default value (empty array)
Cache::get('fairies',array());

//Delete a cached object
Cache::delete('fairies');

//Clear all cache
Cache::clear();

//Remove expired objects.
//If you are using database or file cache
//you might want to set up a cron job to call
//this method from time to time to clear expired data.
//APC and XCache remove expired items automatically
Cache::garbage_collect();
```

If you set up more than one caching profile you can access them in two ways:

```php
//By passing a profile parameter
Cache::set('fairies',array('Tinkerbell','Trixie'),7000,'custom_profile');

//Or by using an instance
$cache=Cache::instance('custom_profile');
$cache->set('fairies',array('Tinkerbell','Trixie'),7000);
```

_Note that not all objects can be cached_. Only serializable objects can be cached, that’s why you should convert database and ORM results into arrays or generic objects before storing them (they cannot be serialized as of yet because they depend on having a link to the database), e.g.:

```php
//You can use as_array() to convert database result into array
Cache::set('fairies',DB::query('select')
			->table('fairies')
			->execute()
			->as_array());

//Or as_array(true) to convert ORM result into an array
Cache::set('fairies',ORM::factory('fairy')
			->find_all()
			->as_array(true));
```

Extended support for caching ORM results without converting them to arrays will be added soon.

