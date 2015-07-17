Haml is a very efficient way of creating your HTML templates. It allows you to write shorter tags and avoid the need of closing the tags yourself. This second feature is especially useful because it allows you to avoid situations like hunting unclosed blocks throughout the page. Consider this piece of HTML:

```php
<div id="fairies" class="wide">
	<div class="fairy">
		<div class="name">Tinkerbell</div>
		<div class="tree">Oak</div>
	</div>
</div>
```

In haml it would look like this

```php
#fairies.wide
	.fairy
		.name
			Tinkerbell
		.tree
			Oak
```

In Haml indents describe element nesting, and you may also notice that class and id notations look just like in jQuery which may make your code even more readable. You can add php code and display your variables in Haml like this:

```php
#fairies.wide
	- foreach($fairies as $fairy)
		.fairy
			.name
				= $fairy->name
```

The ‘- ‘ marks this line as PHP code and ‘= ‘ displays a variable. Placing a code inside a loop is done also using indenting.  
PHPixies’ version of Haml also allows you to split your templates into multiple files and then include them like this:

```php
#fairies.wide
	- foreach($fairies as $fairy)
		partial:fairy
```

The ‘partial’ keyword includes a haml subtemplate, in this case _fairy.haml_.  
Filters for javascript and css are supported so you can include css and javascript in your markup like this:

```php
:javascript
	<some javascript code here>
:css
	<some css rules here>
```

You don’t have to worry about using Haml impacting your performance. PHPixie caches rendered templates and later checks their modification time against the modification time of the source haml file, this way each template is rerendered only when it’s modified.  
You can read more about using haml on [http://haml.info](http://haml.info).

