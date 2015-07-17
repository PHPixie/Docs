Scaling, cropping and watermarking images are things that come up often in web development. This module allows you to do those and other things easily and help you avoid some common frustrations. First install it like any other PHPixie module by adding this to your _composer.json_ file:

```php
{
	"require":{
	//other requirements
	"phpixie/image":"2.*@dev"
	}
}
//Remember to run:
//php composer.phar update -o --prefer-dist
//to update your vendors
```

Add it to your $pixie:

```php
// /classes/app/pixie.php
namespace App;
class Pixie extends \PHPixie\Pixie {
	protected $modules = array(
		//Other modules...

		'image' => '\PHPixie\Image'
	);
}
```

Now you need to choose the driver you would like to use (make sure your PHP installation supports it):

- **GD** – The most used PHP imaging library. Available practically everywhere since it comes built-in with PHP. Offers slightly worse quality and far worse performance than alternatives. It’s good enough for most cases, but if you will be processing a lot of images you should better try a different library.
- **Imagick** – PHP wrapper for the popular ImageMagick library. Offers solid performance and quality. It may be present on some shared hostings, and it’s pretty easy to setup if you have a VPS.
- **Gmagick** – PHP GraphicsMagick bindings. GraphicsMagick is a stripped-down performance optimized ImageMagick fork. It offers even better performance than Imagick and produces same quality images. It’s the one I personally use with my projects. It might be tricky to set up though, usually you will have to compile the PHP module yourself.

After you selected a driver add it to your configuration file:

```php
// /assets/config/image.php
return array(
	'default' => array(
		'driver' => 'GD'
	)
);
```

Alright we can try it out now, here are a few examples:

**Reading, creating, saving and displaying**  
The module provides two ways of getting an image, by either reading it from file or creating a blank one filled with specified color.

```php
//Read an image
$image = $pixie->image->read('pixie.png');

//Create 300x200 half-transparent red image
//0xff0000 is a hex representation of the color
//same as in css, just with '0x' instead of '#'
$image = $pixie->create(300, 200, 0xff0000, 0.5);

//Save to file
//By default PHPixie will guess the image format
//from the file name
$image->save('pixie.png');

//You can always specify the format manually though
$image->save('pixie.jpg', 'jpeg');

//This will output the image directly to the browser
$image->render('png');
```

When saving images in JPEG format PHPixie always adds a white background behind the image since JPEG doesnt support transparency.

**Resizing and Cropping**  
It’s as straightforward as it can possibly get.

```php
//Resize it to 400px width, aspect ratio is maintained
$image->resize(400);

//Resize to 200px in height
$image->resize(null, 200);

//Resize to fit in a 200x100 box
//A 300x300 image would become 100x100
//it's as if you specify the maximum size
$image->resize(200, 100);

//Resize to *fill* a 200x100 box
//A 300x300 image would become 200x200
//it's as if you specify the minumum size
$image->resize(200, 100, false);

//Scale image using a ratio
//This would make it twice as big
$image->scale(2);

//Crop image to 100x150 with 10 horizontal offset
//and 15 vertical
$image->crop(100, 100, 10, 15);
```

If you would like to have user avatars of a fixed size, you would have to resize them to be as close to the needed size as possible and then crop them, like this:

```php
//Let's assume $image is 300x200
//and we want to make 100x100 avatars.

//Note how you can chain the methods together
$image->resize(100, 100, false) //becomes 150x100
	->crop(100, 100)
	->save('avatar.png');

//We even have a predefined fill() function for this =)
$image->fill(100, 100)->save('avatar.png'); //thats it
```

**Rotating and flipping**  
Rotating and flipping images are things that rarely come up, but they are as easy as resizing and cropping:

```php
//Rotate the image 45 degrees counter clockwise
//filling the background with semitransparent white
$image->rotate(45, 0xffffff, 0.5);

$image->flip(true); //flip horizontally
$image->flip(false, true); //flip vertically
$image->flip(true, true); //flip bloth
```

**Overlaying Images**  
Overlaying is most useful for watermarking images or creating some fancy avatars. You can overlay any number of images by chaining _overlay()_ calls:

```php
$meadow = $pixie->image->read('meadow.png');
$fairy = $pixie->image->read('fairy.png');
$flower = $pixie->image->read('flower.png');

//Put fairy at coordinates 40, 50
$meadow->overlay($fairy, 40, 50)
	->overlay($flower, 100, 200)
	->save('meadow2.png');
```

Note that overlaying an image will not auto expand the existing one, meaning that if you overlay a 500×300 image over a 100×100 one you will get a 100×100 result with the access cropped out. You can work around this by creating a canvas layer:

```php
$large = $pixie->image->read('large.png');// 500x300
$small = $pixie->image->read('small.png');// 100x100

//Make transparent canvas the size of large image
$canvas = $pixie->image->create($large->width, $large->height);
$canvas->overlay($small)
	->overlay($large)
	->save('merged.png');
```

**Drawing Text**  
Drawing text is the source of numerous frustrations with imaging libraries, luckily PHPixie will do all the hard work for you, all you need to do is use a simple _text()_ method. But before we get down to it, let’s take a quick look at font metrics:

 ![Font Metrics](http://phpixie.com/wp-content/uploads/2013/07/Typography_Line_Terms.svg_.png)

Font Metrics

When specifying text coordinates we will be specifying the coordinates of the _baseline_, so the text will appear slightly higher.

```php
//Make white background
$image = $this->pixie->create(500, 500, 0xffffff, 1);

//Write "tinkerbell" using font.ttf font and font size 30
//Put it in coordinates 50, 60 (baseline coordinates)
//And make it half transparent red color
$image->text("Tinkerbell", 30, 'font.ttf', 50, 60, 0xff0000, 0.5);

//Wrap text so that its 200 pixel wide
$text = "Trixie is a nice little fairy that like spicking flowers";
$image->text($text, 30, 'font.ttf', 50, 60, 0xff0000, 0.5, 200);

//Increase Line spacing by 50%
$image->text($text, 30, 'font.ttf', 50, 60, 0xff0000, 0.5, 200, 1.5);

//Write text under a 45 degree counter clockwise angle:
$image->text("Tinkerbell", 30, 'font.ttf', 50, 60, 0xff0000, 0.5, null, 1, 45);
```

That’s it =) You should be able to cope with pretty much any image now.

