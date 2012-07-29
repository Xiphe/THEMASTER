<?php class __PREFIX__Master extends THEWPMASTER {
	/* STATIC VARS */


	/* VARS */

	protected $singleton = true; // Prevents the contruction of a second instance by THEBASE.
	protected $HTML = true; // This Master Uses the !HTML Class.
	
	// **EXTENDED** //
	/* 
	 * Add Filters and Actions into this array. A simple Value will look for a similar called method
	 * in this class. The Methodname can be specifyed by the key. Priority and accepted args can be
	 * adjusted by adding them to the hookname. ( 'init|1|3' ).
	 */
	// **EXTENDED_END** //
	// protected $actions_ = array( 'wpinit' => 'init' );
	// protected $filters_ = array();

	// given throu initiation.
	public $projectFile, $basePath, $folderName, $projectType, $author,
		$textdomain, $baseUrl, $projectName, $version, $prefix, $date,
		$updatable;


	/* METHODS */

	/**
	 * Basic initiation before Wordpress is available.
	 *
	 * Internal and !THEMASTER related logic should be used here.
	 * Initiation arguments passed to THEWPMASTERINIT function and
	 * parsed from __baseConfigFile__ are available as class variables.
	 *
	 * @return void
	 */
	public function init() {

		// **EXTENDED** //
		// $this->reg_js( '__lcprefix__script' );
		// $this->reg_less( '__lcprefix__style' );
		// **EXTENDED_END** //
	}
	// **EXTENDED** //

	/**
	 * Second initiation method called on the init action hook of Wordpress.
	 *
	 * Wordpress related logic can be used here.
	 * Hook has to be activated by uncommenting the protected $actions_ class variable.
	 *
	 * @return void
	 */
	// public function wpinit() {
		
	// }
	
	/**
	 * This method is called whenever the theme gets updated
	 *
	 * @return void
	 */
	// public function update() {

	// }

	/**
	 * Shorthand Theme Options
	 *
	 * This Adds a "Settings" Page to the Theme Menu.
	 * For detailed and more advanced settings it's recomended to
	 * use the settings functionality of Wordpress.
	 *
	 * @return array the options and defaults.
	 */
	// public function settings() {
	// 	return array(
	// 		'updateApikey' => array(
	// 			'label' => __( 'Product Key', '__textdomain__' ),
	// 			'type' => 'input',
	// 			'default' => ''
	// 		)
	// 	);
	// }
	
	// **EXTENDED_END** //
} ?>