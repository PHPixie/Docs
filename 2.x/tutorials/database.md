PHPixie makes interacting with the database really easy. It offers a solid abstraction that you can use to make your website use any database by adding a driver for it. Currently PHPixie comes with _PDO_ and _Mysqli_ drivers. To connect to a database you need to edit _/assets/config/db.php_ and specify your connections there.

```php
// This is how you connect with PDO
<?php
	return array(
		'default'=>array(
			'driver' => 'PDO',
			'connection' => 'mysql:host=localhost;dbname=pixie',
			'user' => 'root',
			'password' => 'we242!asq'
		)
	);
?>
``````php
// This is how you connect with Mysqli
<?php
	return array(
		'default'=>array(
			'driver' => 'mysql',
			'host' => 'locahost',
			'db' => 'pixie',
			'user' => 'root',
			'password' => 'we242!asq'
		)
	);
?>
```

You can have as many connections as you like specified but unless otherwise specified database queries and ORM models will use the _default_ connection.

> It is really recommended that you also use ORM to access your object as it will make interaction with the database even more simple.

**Querying**  
PHPixie provides you with an object oriented approach to creating your queries, which makes writing them more readable, flexible and fast. It’s easy to illustrate with this simple examples:

```php
//Looking for tinkerbell
$pixie->db->query('select')->table('fairies')
	->where('name','tinkerbell')
	->execute();

//Selecting just a few fields from all fairies
$pixie->db->query('select')->table('fairies')
	->fields('id','name')
	->execute();

//Now let's add Trixie
$pixie->db->query('insert')->table('fairies')
	->data(array('name' => 'Trixie'))
	->execute();
$trixies_id=$pixie->db->insert_id();

//Rename her
$pixie->db->query('update')->table('fairies')
	->data(array('name' => 'Tooth Fairy'))
	->where('id',$trixies_id)
	->execute();

//And remove
$pixie->db->query('delete')->table('fairies')
	->where('id',$trixies_id)
	->execute();

//Counting has the following shortcut
$num=$pixie->db->query('count')->table('fairies')->execute();

//Querying a connection called 'backup'
$pixie->db->query('insert','backup')->table('fairies')
	->data(array('name' => 'Trixie'))
	->execute();
$trixies_id=$pixie->db->insert_id('backup');
```

In the last example you probably noticed some redundancy with having to specify the connection all the time. This can be avoided by getting the connections’ instance.

```php
$db=$pixie->db->get('backup');
$db->build_query('insert')->table('fairies')
	->data(array('name' => 'Trixie'))
	->execute();
$trixies_id=$db->get_insert_id();
```

Let’s take a closer look at _Query\_Database_ class and what methods it provides us with.  
_Where_ is the method that you will probably use the most. It allows you a lot of flexibility in terms of accepted parameters, so you can write the most readable code.

```php
//Searching by field
$pixie->db->query('select')->table('fairies')->where('id',5)->execute();

//Searching with operator
$pixie->db->query('select')->table('fairies')->where('id','>',5)->execute();

//Combining conditions into an 'AND' query
$pixie->db->query('select')->table('fairies')
	->where('id','>',5)
	->where('id','<',8)
	->execute();

//Another way of doing this
$pixie->db->query('select')->table('fairies')->where(array(
		array('id','>',5),
		array('id','<',8)
	))->execute();

//Creating an 'OR' query
$pixie->db->query('select')->table('fairies')
	->where('id','>',5)
	->where('or',array('id','<',3))
	->execute();

//Using arrays you can achieve nested conditions
$pixie->db->query('select')->table('fairies')->where(array(
		array(
			array('name','trixie'),
			array('id','<',6),
		),
		array('or',array(
			array('name','tinkerbell'),
			array('id','>',3)
			)
		)
	))->execute();
//WHERE ( `name` = 'trixie' and `id` < 6) 
//	OR ( `name` = 'tinkerbell' and `id` > 3)
```

It is really easy to get used too. Now let’s take a look at much more simple methods.

```php
$pixie->db->query('select')->table('fairies')

	//Order by name
	->order_by('name','desc')
	
	//Select only 5 rows
	->limit(5)

	//Starting from the third one
	->offset(3)
	->execute();
```

**Advanced querying**  
If you need to use database specific function like _MAX()_, _AVG()_ etc you will need to pass them enclosed in _$pixie->db->expr_ so that they will not get escaped.

```php
//Selecting names that more than 5 fairies have
$pixie->db->query('select')->fields('name',
		$pixie->db->expr("Count(`id`) as `count`")
	)->from('fairies')

	//Grouping
	->group_by('name')

	//Condition. ->having behaves just like ->where
	->having('count','>',5)
	->execute();
```

PHPixie also gives you a way to join tables and specify join conditions similarly to _where_ method.

```php
$pixie->db->query('select')->from('fairies')

//We specify a table, join conditions and join type
	->join('trees',array('trees.id','fairies.tree_id'),'left')
	->execute();

//If you need to add an alias to the table
$pixie->db->query('select')->from('fairies')
	->join(array('trees','t'),array('trees.id','fairies.tree_id'))
	->execute();
```

If you are adding joins automatically, like ORM does, you may find yourself in need of generating aliases automatically. It can be simply achieved in this way:

```php
$query=$pixie->db->query('select')->from('fairies');
$query->lastAlias(); // returns 'fairies'
$query->addAlias(); // returns 'a1'
$query->addAlias(); // returns 'a2'
$query->lastAlias(); // returns 'a2'
```

**Subqueries and Unions**  
Sometimes you may need to write nested queries, this is also possible with the query builder. All you need to do is pass another query builder as an argument.

```php
//Select only fairies that are protecting trees
$pixie->db->query('select')
	->table('fairies')
	->where('id','IN',
		$pixie->db->query('select')
		->table('trees')
		->fields('protector_id')
	)->execute();

//Or you can always write the subquery manually
$pixie->db->query('select')
	->table('fairies')
	//Mind the brackets around the select
	->where('id','IN',$pixie->db->expr("(SELECT protector_id FROM trees)"))
	->execute();
```

Similarly you can use a subquery anywhere you specify a table

```php
$young_fairies=$pixie->db->query('select')
	->table('fairies')
	->where('age','<',18);

$young_trees=$pixie->db->query('select')
	->table('trees')
	->where('age','<',5);

//We can use a nested query as a table to select from
$pixie->db->query('select')

	//You can also specify an alias
	//To name the subquery table
	->table($young_fairies,'young_fairies')

	//We can also join on nested queries
	->join(
		array($young_trees,'young_trees'),
		array('young_fairies.id','=','young_trees.protector_id')
	)->execute();
```

You can create unioned results using the _union()_ method. By default unions are treated as _UNION ALL_ meaning that duplicate rows will be preserved.

```php
$pixie->db->query('select')
	->table('fairies')
	->where('id','>',7)
	->union(
		$pixie->db->query('select')
			->table('fairies')
			->where('id','<',3)
	)
	->execute();

//To ommit duplicate rows just pass 'false'
//As a second parameter to union()
$pixie->db->query('select')
	->table('fairies')
	->where('id','>',10)
	->where('id','<',15
	->union(
		$pixie->db->query('select')
			->table('fairies')
			->where('id','<',12)
		,false)
	->execute();
//The result of the above will include all rows from 1 to 14
//without duplicates
```

If there are unions added to the query all the calls to limit() and offset() will modify only the first union subquery. If you need to use a limit on the result of the union you may use nested queries. It’s easy to demonstrate using an example:

```php
$union=$pixie->db->query('select')->table('pixies');
$pixie->db->query('select')->table('fairies')->limit(10)->union($union);
//The above will produce this query:
//(SELECT * FROM fairies limit 10) UNION ALL (SELECT * FROM pixies)

//Now let's try applying the limit to the result of the union
$pixie->db->query('select')
	->table(
		$pixie->db->query('select')
		->table('fairies')
		->union($union)
	)->limit(10)

//SELECT * FROM ((SELECT * FROM fairies) UNION ALL (SELECT * from pixies)) as a0 LIMIT 10
```

**Working with results**  
At this point you are probably curious as to what exactly does _execute()_ return? It returns an _iterable_ result that you can put in a _foreach_ block. Each row is represented as an object, and you can access the column as properties, like so:

```php
$fairies=$pixie->db->query('select')->from('fairies')->execute();
foreach($fairies as $fairy){
	echo "Fairy name: ".$fairy->name."\n";
}

//If you are certain the result will be just one row
//you can access it using 'current'
$fairy=$pixie->db->query('select')->from('fairies')
	->limit(1)
	->execute()->current();
echo "Fairy name: ".$fairy->name."\n";

//To get all rows as array
$fairies=$pixie->db->query('select')->from('fairies')->execute()->as_array();

//Counting returns the number of rows instead of a result set
$pixie->db->query('count')->table('fairies')->execute();
```> Remember that though you can use _foreach_ on the result set **it is not an array**. You need to use **as\_array()** if you want to use **json\_encode** for example.

Most likely you will rarely need to query the database yourself, it is much better to use ORM for accessing your object.

