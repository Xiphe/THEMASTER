<?php 
namespace __namespace__;

use Xiphe\THEMASTER as TM;

class Master extends TM\THEWPMASTER {
	/* STATIC VARS */


	/* VARS */

	public $singleton = true; // Prevents the contruction of a second instance by THEBASE.
	public $HTML = true; // This Master Uses the !HTML Class.
	// **EXTENDED** //
	// public $buildMissingClasses = true; // Uncomment to tell THEWPBUILDER to build unknown classes.
	// **EXTENDED_END** //
	
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
		// $this->reg_js( 'script' );
		// $this->reg_less( 'style' );
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
	 * This method is called when the plugin gets activated by the user.
	 *
	 * @return void
	 */
	// public function activate() {
		
	// }

	/**
	 * This method is called when the plugin gets deactivated by the user.
	 *
	 * @return void
	 */
	// public function deactivate() {
		
	// }

	/**
	 * This method is called whenever the plugin gets updated
	 *
	 * @return void
	 */
	// public function update() {

	// }

	/**
	 * Shorthand Plugin Options
	 *
	 * This Adds a "Settings" Link to the Plugins Row where one can
	 * specify some core settings for the plugin.
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