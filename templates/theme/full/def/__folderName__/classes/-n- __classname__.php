<?php class __PREFIX____ClassName__ extends THEWPMASTER {

	/* VARS */

	public $singleton = true; // Prevents the contruction of a second instance by THEBASE.
	// public $HTML = true; // This Master Uses the !HTML Class.
	
	// **EXTENDED** //
	/* 
	 * Add Filters and Actions into this array. A simple Value will look for a similar called method
	 * in this class. The Methodname can be specifyed by the key. Priority and accepted args can be
	 * adjusted by adding them to the hookname. ( 'init|1|3' ).
	 */
	// **EXTENDED_END** //
	// protected $actions_ = array( 'wpinit' => 'init' );
	// protected $filters_ = array();


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

	// **EXTENDED_END** //
} ?>