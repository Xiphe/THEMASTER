<?php
/**
 * Master file for __projectName__
 *
 * @category: Wordpress Plugin
 * @package: __namespace__
 * @author: __author__
 * @license: __license__
 * @link: __projectURI__
 */

namespace __namespace__\classes;

use Xiphe\THEMASTER as TM;

class Master extends TM\core\THEWPMASTER {
	/**
	 * If true the contruction of a second instance by THEBASE will be prevented.
	 *
	 * @var boolean
	 */
	public $singleton = true;

	/**
	 * If true this will be filled with an instance of Xiphe\HTML
	 *
	 * @var boolean|Xiphe\HTML
	 */
	public $HTML = true;
	// **EXTENDED** //
	// public $buildMissingClasses = true; // Uncomment to tell THEWPBUILDER to build unknown classes.
	// **EXTENDED_END** //

	/**
	 * Action hooks bound to this class.
	 *
	// **EXTENDED** //
	 * A simple Value will look for a similar called method
	 * in this class. The Method-name can be specified by the key. Priority and accepted arguments can be
	 * adjusted by adding them to the hookname. ('init|1|3').
	 * If multiple hooks should be bind to the same method the hooks should be served as an array
	 * ('methodname' = array('HookA', 'HookB'));
	 *
	// **EXTENDED_END** //
	 * @var array
	 */
	// protected $actions_ = array(
	// 	'wpinit' => 'init'
	// );

	/**
	 * Filter hooks bound to this class.
	 *
	 * See description of $actions_
	 * 
	 * @var array
	 */
	// protected $filters_ = array();

	// **EXTENDED** //
	/**
	 * Structure of the projects folder.
	 * Will be regenerated on every update.
	 *
	 * @var array
	 */
	// protected $folderStructure_;
	
	/**
	 * Array of keys for additional arguments that needed to be passed through initiation.
	 *
	 * @var array
	 */
	// protected $requiredInitArgs;

	/**
	 * The author of this project. (initiation argument)
	 *
	 * @var string
	 */
	public $author;

	/**
	 * Absolute path of projects folder. (initiation argument)
	 *
	 * @var string
	 */
	public $basePath;
	
	/**
	 * The URL pointing to the projects folder. (initiation argument)
	 *
	 * @var  string
	 */
	public $baseUrl;

	/**
	 * The file containing the projects information. (initiation argument)
	 *
	 * @var string
	 */
	public $configFile;

	/**
	 * The date of the last update. (initiation argument)
	 *
	 * @var string
	 */
	public $date;

	/**
	 * Name of the projects folder. (initiation argument)
	 *
	 * @var string
	 */
	public $folderName;
	
	/**
	 * Whether or not this is a projects master class. (initiation argument)
	 *
	 * @var boolean
	 */
	public $isMaster;

	/**
	 * The license description of this project. (initiation argument)
	 *
	 * @var string
	 */
	public $license;

	/**
	 * The namespace of the project. (initiation argument)
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * The Description of the Project. (initiation argument)
	 *
	 * @var string
	 */
	public $projectDesc;

	/**
	 * The file containing the projects initiation. (initiation argument)
	 *
	 * @var string
	 */
	public $projectFile;

	/**
	 * The projects name. (initiation argument)
	 *
	 * @var string
	 */
	public $projectName;
	
	/**
	 * Type of the project. (initiation argument)
	 *
	 * @var string
	 */
	public $projectType;

	/**
	 * The projects URI set in configuration file. (initiation argument)
	 *
	 * @var [type]
	 */
	public $projectURI;

	/**
	 * Array of plugins needed to be available before this project
	 * can initiate. (initiation argument)
	 *
	 * @var array
	 */
	public $requiredPlugins;

	/**
	 * The textdomain of this project. (initiation argument)
	 *
	 * @var string
	 */
	public $textdomain;

	/**
	 * The textID of the project. (foldername/filebasename) (initiation argument)
	 *
	 * @var string
	 */
	public $textID;

	/**
	 * If the project can receive updates through THE MASTER. (initiation argument)
	 *
	 * @var boolean
	 */
	public $updatable;

	/**
	 * URI of the update Server if available. (initiation argument)
	 *
	 * @var string
	 */
	public $updateServer;

	/**
	 * The current version of the project. (initiation argument)
	 *
	 * @var string
	 */
	public $version;
	
	// **EXTENDED_END** //




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
	 * Hook has to be activated by uncommenting the protected $actions_ property.
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
	 * This method is called whenever the theme gets updated
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
	 * For detailed and more advanced settings it's recommended to
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
}