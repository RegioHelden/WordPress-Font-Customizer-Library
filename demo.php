<?php
	require_once( dirname( __FILE__ ) . '/index.php' );

	//Start the FontManager
	//You can specify the priority of the Manager in the Customizer with 'section_priority' (default: 40 )
	$fcl = new FontManager( array( 'section_priority' => 1 ) );

	//Load typekit support
	$fcl->add_typekit( '//use.typekit.net/qzc2ote.js' );

	//Basic settings
	$fcl->add(
		'my-id',                               //a unique ID
		'p,li',                                //commaseparated list of CSS elements effected
		'Textelements',                        //title
		'Change the font settings for texts.'  //description
	);

	//Advanced example
	$fcl->add(
		'my-id2',                                 //a unique ID
		'h1,h2,h1.entry-title,h2.entry-title',    //commaseparated list of CSS elements effected
		'Headings',                               //title
		'Change the font settings for headings.', //description
		array(
			'font-size'   => false, //disables the font-size selector
			'font-weight' => true,  //enables the font-weight selector, enabled by default
			'font-family' => true,  //enables the font-family selector, enabled by default
			'line-height' => true,  //enables the line-height selector, enabled by default
		)
	);

	//Define different font weights
	$font_weights = array(
		array(
			'id'    => 'normal',      //a unique ID
			'name'  =>  'Normal 400', //a humanreadable name
			'value' => 'normal',      //The corresponding CSS property
		),
	
		array(
			'id'    => 'bold',
			'name'  => 'Bold 600',
			'value' => 'bold',
		),
	
		array(
			'id'    => 'slim',
			'name'  => 'Slim 200',
			'value' => '200',
		),
	);

	$fcl->add(
		'my-id3',
		'.entry-content p',
		'Posttext',
		'Change the font settings for the post texts.',
		array(
			'font-weight' => $font_weights, //enables the font-weight selector, and uses the defined weights
		)
	);

	//Define different font families
	$font_families = array(
		array(
			'id'    => 'arial', //a unique ID
			'name'  => 'Arial', //a humanreadable name
			'value' => 'Arial', //The corresponding CSS property
			'src'   => false    //No font needs to be loaded
		),	
		array(
			'id'    => 'baloo-da',
			'name'  => 'Baloo Da',
			'value' => '"Baloo Da", cursive',
			'src'   => 'https://fonts.googleapis.com/css?family=Baloo+Da' //URL to font, is used in <link rel="stylesheet" href="[src]">
		),	
	);

	$fcl->add(
		'my-id4',
		'body h2.entry-title',
		'The post title',
		'Change the font settings for the post title.',
		array(
			'font-family' => $font_families, //enables the font-family selector, and uses the defined fonts
		)
	);