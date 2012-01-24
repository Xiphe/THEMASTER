<?php 
require_once('wpupdates.php');
require_once('wpmodel.php');
// Version: 2.0.2
define('WPMASTERAVAILABE', true);
class THEWPMASTER extends THEUPDATES {
	/** The Args needed by the Object to get initiated
	 * @package wp
	 */	
	
	public static $currentUser; // Current User, if logged in
	
	static $_notes = array(); // Array of passed Notes to be printet via print_adminMessages()
	static $_postCache = array(); // Array of Posts
	
	
	/** Used by add_contentTag() & do_ContentTags()
	 * @since 2.0.2
	 * @date Dez 14th 2011
	 */
	private static $_hooked = array();
	private static $_contentTags = array();
	
	private static $_ftp_conn_id;
	private static $_folderStructure = array(
		'classes' => 0755,
		// 'models' => 0755,
		'res' => array(
			'chmod' => 0755,
			'css' => 0777,
			'includes' => 0755,
			'js' => 0755,
			'less' => 0755,
		),
		// 'views' => 0755,
	);
	
	/** The init - does nothing for subclasses und calls _masterInit if its called by THEMASTER
	 *
	 * @param array $initArgs
	 * @return void
	 * @access protected
	 * @date Jul 28th 2011
	 */
	protected function init($initArgs) {
		if(get_class($this) == 'THEWPMASTER')
			$this->_masterInit($initArgs);
	}
	
	/** Initiation for a new Instance of THEWPMASTER, generates a new Submaster XYMaster
	 *
	 * @param array $initArgs see $this->requiredInitArgs for required keys
	 * @return void
	 * @access private
	 * @date Jul 28th 2011
	 */
	protected function _masterInit($initArgs) {
		$obj = parent::_masterInit($initArgs);
		
		// TODO: INCLUDE HTML CLASS
		// include_once($this->basePath.'/classes/html/!html.php');
		
		$name = strtoupper($this->prefix).'Master';
		$this->_versionCheck($name, $obj);
		
		if(is_dir($this->basePath.'/languages')) {
			load_plugin_textdomain($this->textdomain, false, $this->folderName.'/languages/');
		}
		
		$pluginPath = isset($this->pluginPath) ? $this->pluginPath : $this->basePath;
		include_once($this->basePath.DS.'classes'.DS.'master.php');
		register_activation_hook($pluginPath.DS.basename($this->basePath).'.php', array($name, 'activate'));
	
		if(!isset(self::$_hooked['masterVersionCheck'])) {
			$this->_versionCheck('themaster', $this);
			self::$_hooked['masterVersionCheck'] = true;
		}
		if(!isset(self::$_hooked['admin_notices'])) {
			add_action('admin_notices', array('THEWPMASTER', 'admin_notices'));
			self::$_hooked['admin_notices'] = true;
		}
		if(!isset(self::$_hooked['register_sources'])) {
			add_action('init', array('THEWPMASTER', 'register_sources'), 100, 0);
			self::$_hooked['register_sources'] = true;
		}
		if(!isset(self::$_hooked['print_jsVars'])) {
			add_action('wp_head', array('THEWPMASTER', 'print_jsVars'), 0, 0);
			add_action('admin_head', array('THEWPMASTER', 'print_adminJsVars'), 0, 0);
			self::$_hooked['print_jsVars'] = true;
		}
	}

	private function _versionCheck($slug, $obj) {
		if($GLOBALS['pagenow'] != 'plugins.php') return;
		if( (defined(($name = 'THEVERSION_'.strtoupper($slug))) && ($version = constant($name)))
		 || (isset($this->theversion) && ($version = $this->theversion))
		) {
			$name = strtolower($name);
			if(version_compare(get_option($name), $version, '<')) {
				if($name == 'theversion_themaster') {
					$r = $this->_masterUpdate();
				} else {
					if(isset($obj->folderStructure) && is_array($obj->folderStructure)) {
						$this->_check_folderStructure($obj->folderStructure, $obj->basePath);
					}
					$r = $obj->update();
				}
				if($r === true) {
					update_option($name, $version);
				} else {
					throw new Exception('Error: Update method for "'.$name.'" failed.', 1);
				}
			}
		}
	}
	
	private function _masterUpdate() {
		$this->_check_folderStructure(self::$_folderStructure, dirname(dirname(__FILE__)).DS);
		return true;
	}
	public function _masterActivate() {
	}
	
	private function _check_folderStructure($structure, $basePath) {
		if(!isset(self::$_ftp_conn_id) && defined('FTP_HOST') && defined('FTP_USER') && defined('FTP_PASS')) {
			self::$_ftp_conn_id = ftp_connect(FTP_HOST);
			$login_result = ftp_login(self::$_ftp_conn_id, FTP_USER, FTP_PASS);
			$basePath = str_replace(FTP_WORKING_PATH, '', $basePath);
		}
		self::_check_folderStructureWalker($structure, '', $basePath);
		if(isset(self::$_ftp_conn_id)) {
			ftp_close(self::$_ftp_conn_id);
		}
	}
	
	private static function _check_folderStructureWalker($structure, $basedir, $root) {
		foreach($structure as $folder => $chmod) {
			$subfolders = null;
			if(is_array($chmod)) {
				$t = $chmod['chmod'];
				unset($chmod['chmod']);
				$subfolders = $chmod;
				$chmod = $t;
			}
			$dir = $root.$basedir.DS.$folder.DS;
			if(is_dir($dir)) {
				if(!isset(self::$_ftp_conn_id)) {
					chmod($dir, $chmod);
				} else {
					ftp_chmod(self::$_ftp_conn_id, $chmod, $dir);
				}
			} else {
				if(!isset(self::$_ftp_conn_id)) {
					mkdir($dir, $chmod);
				} else {
					ftp_mkdir(self::$_ftp_conn_id, $dir);
					ftp_chmod(self::$_ftp_conn_id, $chmod, $dir);
				}
			}
			
			if(is_array($subfolders)) {
				self::_check_folderStructureWalker($subfolders, $basedir.DS.$folder, $root);
			}
		}
	}
	
	private function _get_pluginSymlinkPath($file) {
	    // If the file is already in the plugin directory we can save processing time.
	    if ( preg_match( '/'.preg_quote( WP_PLUGIN_DIR, '/' ).'/i', $file ) ) return $file;
	
		$path = '';
	    // Examine each segment of the path in reverse
	    foreach ( array_reverse( explode( '/', $file ) ) as $segment )
	    {
	        // Rebuild the path starting from the WordPress plugin directory
	        // until both resolved paths match.
	
	        $path = rtrim($segment .'/'. $path, '/');       
	
	        if ( __FILE__ == realpath( WP_PLUGIN_DIR . '/' . $path ) )
	        {
	            return WP_PLUGIN_DIR . '/' . $path;
	        }
	    }
	
	    // If all else fails, return the original path.
	    return $file;
	}
	
	
	public function print_adminJsVars() {
		self::print_jsVars(true);
	}
	public function print_jsVars($admin = false) {
		$HTML = self::inst()->get_HTML();
		$source = $admin ? self::$registeredAdminJsVars : self::$registeredJsVars;
		$HTML->sg_script();
		foreach($source as $name => $var) {
			$HTML->blank('var '.$name.' = '.json_encode($var).';');
		}
		$HTML->end();
	}
	
	public function register_sources() {
		foreach(THEBASE::$registeredSources as $dest => $sources) {
			foreach($sources as $type => $files) {
				foreach($files as $file => $url) {
					if($type == 'js') {
						wp_enqueue_script('twpm.'.$file, $url);
					} elseif($type == 'css') {
						wp_enqueue_style('twpm.'.$file, $url);
					}
				}
			}
		}
	}
	
	/** Default Hooks addet to every subclass called via get_instance()
	 *
	 * @param object $obj the new instance
	 * @return void
	 * @access protected
	 * @date Jul 28th 2011
	 */
	protected function _hooks($obj) {
		parent::_hooks($obj);
		if(method_exists($obj, 'wpinit')) {
			$prio = isset($obj->wpinitPriority) ? $obj->wpinitPriority : null;
			add_action('init', array($obj, 'wpinit'),$prio);
		}
	}
	
	/** Can be called to print Admin Messages setted via set_adminMessage()
	 *
	 * @return void
	 * @access public
	 * @date Sep 22th 2011
	 */
	public static function admin_notices() {
		if(!isset($_SESSION['tm_admin_notes']) || !is_array($_SESSION['tm_admin_notes']))
			$_SESSION['tm_admin_notes'] = array();
		$messages = array_merge($_SESSION['tm_admin_notes'], self::$_notes);
		unset($_SESSION['tm_admin_notes']);
		$HTML = self::sget_HTML();
		
		foreach($messages as $note) {
			$HTML->s_div($note['attr'])->b_p($note['inner'])->end();
		}
	}
	
	/** Setter for Admin Messages
	 *
	 * @param string $message the message
	 * @param mixed $attr optional attrs in HTML Class style default "updated"
	 * @return void
	 * @access protected
	 * @date Jul 28th 2011
	 */
	protected function set_adminMessage($message, $attr = 'updated', $session = false) {
		if(empty($message))
			return;
		if(!$session)
			self::$_notes[] = array('inner' => $message, 'attr' => $attr);
		else {
			$_SESSION['tm_admin_notes'][] = array('inner' => $message, 'attr' => $attr);
		}
	}
	
	/** returns the current user or a specific key of current user
	 *
	 * @param string the key name or null for User Object
	 * @return mixed
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function get_user($key = null) {
		if(!isset(self::$currentUser)) {
			self::$currentUser = get_userdata($GLOBALS['user_ID']);
		}
		if($key == null)
			return self::$currentUser;
		else
			return $this->recursive_get(self::$currentUser, $key);
	}
	
	public function set_user($key, $value) {
		$this->get_user();
		self::$currentUser->$key = $value;
	}
	
	/** returns "the_content()"
	 *
	 * @param object $post post object, containing the content
	 * @return string
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function get_filtered_content($post) {
		$content = str_replace(']]>', ']]&gt;', apply_filters('the_content', $post->post_content));
		if(substr($content, 0, 5) == '<p><p' && $this->minify(substr($content, strlen($content)-9, 9), true) == '</p></p>') {
			$content = substr($content, 3, strlen($content)-7);
		}
		return $content;
	}
	
	public function fireContentTag($tag) {
		foreach(self::$_contentTags as $cTag) {
			if($tag == $cTag['tag']) {
				echo call_user_func($cTag['cb']);
				break;
			}
		}		
	}
	
	/** This function hookes into "the_content" and replaces [$tag] with $callback
	 *
	 * @param string $tag the [tag] that should be replaced
	 * @param mixed $callback array or string of method or function containing the replacement
	 * @return void
	 * @access public
	 * @date Dez 14th 2011
	 */
	protected function add_contentTag($tag, $callback) {
		if(!isset(self::$_hooked['the_content'])) {
			add_filter('the_content', array('THEWPMASTER', 'do_ContentTags'));
			self::$_hooked['the_content'] = true;
		}
		self::$_contentTags[] = array('tag' => $tag, 'cb' => $callback);
	}
	
	/** The hook callback from add_contentTag() called on "the_content"
	 *
	 * @param string $content the content string
	 * @return string the new content string
	 * @date Dez 14th 2011
	 */
	public static function do_ContentTags($content) {
		foreach(self::$_contentTags as $contentTag) {
			extract($contentTag);
			if(preg_match('/\['.$tag.'\]/', $content)) 
				$content = preg_replace('/(\<p\>)?\['.$tag.'\](\<\/p\>)?/', call_user_func($cb), $content);
		}
		return $content;
	}
	
	/** killer for THEBASE::echo_sources(), sources will be included by
	 *
	 * @return void
	 * @date Dez 15th 2011
	 */
	public function echo_sources() {
	}
	public function force_echo_sources() {
		parent::echo_sources();
	}
	
	public function get_post($post_ID, $key = null) {
		if(!isset(self::$_postCache[$post_ID])) {
			$this->_set_temp_globals();
			$query = new WP_Query('ID='.$post_ID);
			$post = apply_filters('the_posts', $query->the_post(), $query);
			$this->_unset_temp_globals();
			self::$_postCache[$post_ID] = $post[0];
		}
			
		if($key == null)
			return self::$_postCache[$post_ID];
		if(count(($e = explode('|', $key))) > 0) {
			$r = self::$_postCache[$post_ID];
			foreach($e as $subkey) {
				if(is_object($r))
					$r = $r->$subkey;
				elseif(is_array($r)) {
					$r = $r[$subkey];
				} else
					break;
			}
			return $r;
		} elseif(isset(self::$_postCache[$post_ID]->$key))
			return self::$_postCache[$post_ID]->$key;
	}
	
	
	public function post_authored_by_user($post_ID, $user_ID = null) {
		$user_ID = $user_ID === null ? $this->get_user('ID') : intval($user_ID);
		if($this->get_post($post_ID, 'post_author') == $user_ID)
			return true;
		return false;
	}
	
}	
?>