If there are few developers working on a single project they often face a problem of keeping their databases up to date with the latest changes. If they are adding tables and modifying columns on a day to day basis, keeping track of those changes may be really troublesome since the database cannot be placed under SVN or Git. The workaround here is to use special files that when executed apply a series of changes to the system. In this way you can easily notify other developers that you changed something by adding that file to your source code. That’s how database migrations work and PHPixie just got one of its own.

To enable migrations you need to [download the migration module from Github](https://github.com/dracony/PHPixie "Database Migration Module for PHPixie")&nbsp;put it inside your _modules_ folder and add ‘migrate’ to the _modules_ array in _/application/config/core.php_. After this let’s configure the module to use our database:

```php
// /modules/migrate/config/migrate.php
&amp;lt;?php
return array (
	
	//As with the datatabasethe migration module can handle multiple
	//configurations. 'Default' is the default one
	'default' =&amp;gt; 
		array (

			//Specify a database connection to use
			'connection' =&amp;gt; 'default',

			//Path to a folder where migration files for this 
			//configuration are stored
			'path' =&amp;gt; '/modules/migrate/migrations/',

			//Name of the last migration applied to the database,
			//it will be automatically updated when you migrate
			'current_version' =&amp;gt; null
		  ),
	);
```

Now let’s look at the migration files themselves. There are some supplied with the module by default so we’ll use them as reference. First thing you should take note of id the naming of the files, the migrations are incremental and are applied based on file name in an ascending order. So that _1\_adding\_fairies\_table.php_ will be first, then _2\_adding\_pixies\_table.php_ and so on. Most of the other systems have you use a command line tool to add migrations or force you to write them as classes with _up()_ and _down()_ methods used to upgrade to and revert from a migration, but that is very cumbersome when the changes are small and frequent. PHPixie let’s you specify changes using simple arrays like this:

```php
// /modules/migrate/migrations/1_adding_fairies_table.php
&amp;lt;?php
return array(
	
	//Each element of this array will be treated as table,
	//with array key treated as table name and its value as
	//an array of column definitions. If the table was not
	//referenced in a migration before this one it will be created,
	//otherwise it will be altered.
	'fairies' =&amp;gt; array(
		'id'=&amp;gt;array(

			//'id' type is a shorthand for
			// INT AUTO_INCREMENT PRIMARY_KEY
			'type' =&amp;gt; 'id'
		),
		'name'=&amp;gt;array(

			//'Name' will be a column of type VARCHAR(255)
			'type'=&amp;gt;'varchar',
			'size'=&amp;gt;255
		)
	)
);
```

Now our second migration will alter the newly created _fairies_ table and add a new _pixies_ one:

```php
// /modules/migrate/migrations/2_adding_pixies_table.php
&amp;lt;?php
return array(

	//Because 'fairies' already exists, this will alter it.
	'fairies' =&amp;gt; array(
		'name'=&amp;gt;array(

			//Altering the type of an existing column to VARCHAR(30)
			'type'=&amp;gt;'varchar',
			'size' =&amp;gt; 230,
			
			//To rename the column we just specify a new 'name' for it
			'name'=&amp;gt;'fairy_name'
		)
	),
	
	//Creating another table
	'pixies' =&amp;gt; array(
		'id'=&amp;gt;array(
			'type'=&amp;gt;'id',
		),
		'tree'=&amp;gt;array(
			'type'=&amp;gt;'text'
		),
		'count'=&amp;gt;array(
			'type'=&amp;gt;'int'
		)
	)
	
);
```

And now let’s drop the _fairies_ table and rename _pixies_ into _fairies_.

```php
// /modules/migrate/migrations/3_some_altering.php
&amp;lt;?php
return array(
	
	//Dropping a table
	'fairies' =&amp;gt; 'drop',
	
	'pixies' =&amp;gt; array(
	
		//Renaming a table is similar to renaming a column
		//but because 'name' is quite a popular name for columns
		//you should use 'rename' to specify table renaming
		'rename' =&amp;gt; 'fairies',
		
		//Dropping a column is just the same as dropping a table
		'tree' =&amp;gt; 'drop'
	)
	
);
```

The important part of this third file is to remember that the alterations are made in the same succession as they appear in the file. So that if you want to drop a table and then rename the other into the same name you need to do this in this order. Otherwise it will try renaming the _pixies_ table before the _fairies_ one is dropped and you will receive an error. Note that though migrations let you recover your table structure by reverting back it will not recover the data inside it.

> Now the whole beaty of this modules comes from that _you dont need to write anything for reverting back to previous migration_. the module will deduce how to undo the changes automatically!

Alright, now that we know how to use the system, let’s try actually applying the migrations. For that just navigate to [http://localhost/migrate/](http://localhost/migrate/) and see this handy interface:

 ![Migration module screenshot](http://phpixie.com/wp-content/uploads/2013/01/migrations.png)

Migration module screenshot

  
Using it you can update or revert your database to any revision just by clicking a button.

Note that this system is designed for developer use and not intended for your website users, that’s why for security reasons you should disable the module by removing it from _modules_ array in _core.php_ after your migration is complete.

