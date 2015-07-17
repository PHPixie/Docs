> This tutorial will explain the installation process via Composer, which is recommended. However if you don’t wish to use it, you can always [download the latest snapshot of PHPixie.](http://phpixie.com/phpixie.zip "PHPixie MVC Framework")

**Getting Composer**  
_Composer_ is a package manager for PHP, if you ever used Linux you may be familiar with applications like _apt_ or _yum_ that are used to install all kinds of software. Composer does the very same thing with PHP libraries, you tell it which ones you want, and it downloads them for you. Getting composer is easy, let’s assume we want to install it into our _/var/www/_ folder, we just need to run this:

```php
cd /var/www/
php -r "eval('?>'.file_get_contents('http://getcomposer.org/installer'));"
```

After the script executes you should have a _composer.phar_ file in that directory.

**Setting up the skeleton project**  
PHPixie tries to be as flexible as possible, you can dow whatever you want with the API that it provides, but the most standard site would follow a common skeleton architecture. After you have _composer.phar_ in _/var/www/_ you can just run the following:

```php
cd /var/www
php composer.phar create-project phpixie/project tutorial 2.*-dev --prefer-dist
cd tutorial

#If you are on Linux
php ../composer.phar update -o

#If you are on Windows
php ..\composer.phar update -o
```

These commands will download the skeleton project together with all its dependencies and optimize your autoloader.

**Alternative: Setting up the skeleton project manually**

> You can get the skeleton application here: [**https://github.com/dracony/PHPixie**](https://github.com/dracony/PHPixie "PHP MVC Framework") . Download is a Zip Archive and extract to _/var/www/tutorial_.

**Instructions for Ubuntu 14.04**

There is nothing special with installing PHPixie on Ubuntu, but since it is such a popular environment, here are some additional details on Apache configuration.

Put the following into _/etc/apache2/sites-available/001-phpixie.conf_

```php
<VirtualHost *:80>
        ServerName pixie.local

        ServerAdmin webmaster@localhost

        #Assuming you installed PHPixie into /var/www/tutorial
        DocumentRoot /var/www/tutorial/web

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
        <Directory /var/www/tutorial/web>
             AllowOverride All
        </Directory>
</VirtualHost>
```

Create a symlink to the file by running:

```php
sudo ln -s /etc/apache2/sites-available/001-phpixie.conf /etc/apache2/sites-enabled/001-phpixie.conf
```

Add this line to your _/etc/hosts_:

```php
127.0.0.1 pixie.local
```

Enable Apache Rewrite module:

```php
sudo a2enmod rewrite
```

Restart Apache:

```php
sudo /etc/init.d/apache2 restart
```

Visit [http://pixie.local/](http://pixie.local/) in your browser

Now we need to get all of the PHPixie libraries. This is where Composer comes in, we will use it to download everything that our project needs. In the _composer.json_ file you will see a list of required dependencies like this:

```php
{
"require": {
        "phpixie/core": "2.*@dev",
        "phpixie/db": "2.*@dev",
        "phpixie/orm": "2.*@dev"
    }	
}
```

This requests PHPixie core and database libraries, if you ever need to add a PHPixie module, or any PHP library to your project you can use that file to request it. Now run this to install dependencies:

```php
cd /var/www/tutorial
php ../composer.phar install -o --prefer-dist
```

When the script finishes you should have all required libraries installed in your vendor folder. If you ever want to update them to the latest version just run:

```php
cd /var/www/tutorial
php ../composer.phar update -o --prefer-dist
```

Now all that is left to do is configure your Apache to point it to the _/var/www/tutorial/web/_ folder, be careful and don’t miss out the _/web_ part of the path. You should see a greeting from PHPixie, so you are on the right track.

