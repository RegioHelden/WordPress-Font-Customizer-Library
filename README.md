FontManager Library for WordPress
================
If you are a theme developer and you want to extend the WordPress Customizer, so your user can easily set
some font defaults like

* Font Weight,
* Font Type,
* Font Color,
* Line Height,
* Font Size

this library is for you. You can easily include the file in your themes `functions.php` and add selector for the different CSS elements you want your users to be able to customize.

Example 
-------
```
<?php
require_once( dirname( __FILE__ ) . '/font-manager/index.php' );

//Start the FontManager
$fcl = new FontManager();

//Basic settings
$fcl->add(
	'my-id',                               //a unique ID
	'p,li',                                //commaseparated list of CSS elements effected
	'Textelements',                        //title
	'Change the font settings for texts.'  //description
);
?>
```

For more detailed examples see the demo.php.

After adding the CSS elements you want to be customizable, a new panel will be created in the Customizer - the "Fonts Manager". Here your users will be able to easily adjust the font settings to their needs.