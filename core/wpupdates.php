<?php 
require_once('master.php');
class THEUPDATES extends THEMASTER {

	private static $_updatables = array();
	private static $_hooked = false;
	private static $_checked = false;
	private static $_forced = false;
	
	function __construct($initArgs) {
		$this->requiredInitArgs[] = 'updatable';
		parent::__construct($initArgs);
	}
	
	public function updatable($slug) {
		self::$_updatables[] = $slug;
		$this->_updatehooks();
	}
	
	protected function _masterInit($initArgs) {
		$obj = parent::_masterInit($initArgs);
		
		$this->_checkForcing();
				
		if(isset($this->updatable) && $this->updatable == true)
			$this->updatable($this->textdomain);
		
		return $obj;
	}
	
	protected function _hooks() {
		parent::_hooks();
		add_action('plugins_loaded', array($this, '_checkConstants'));
	}
	
	private function _checkForcing() {
		if(self::$_forced === true) return;
		if($this->_get_Setting('forceUpdates'))
			set_site_transient('update_plugins', null, 1);
		self::$_forced = true;
	}
	
	public function _checkConstants() {
		if(self::$_checked === true) return;
		$const = get_defined_constants(true);
		foreach($const['user'] as $const => $name) {
			if(strstr($const, 'THEUPDATES_UPDATABLE')) {
				$this->updatable($name);
			}
		}
		self::$_checked = true;
	}
	
	private function _updatehooks() {
		if(self::$_hooked === true) return;
		add_filter('pre_set_site_transient_update_plugins', array($this, '_check_for_plugin_update'));
		add_filter('plugins_api', array($this, '_plugin_api_call'), 10, 3);
		self::$_hooked = true;
	}
	
	public function _check_for_plugin_update($checked_data) {
		$this->_checkConstants();
		if (empty($checked_data->checked))
			return $checked_data;
		
		foreach(self::$_updatables as $plugin_slug) {
			$request_args = array(
				'slug' => $plugin_slug,
				'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
			);
			
			$request_string = $this->_prepare_request('basic_check', $request_args);
			
			// Start checking for an update
			$raw_response = wp_remote_post($this->updateApiUrl, $request_string);
				
			if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
				$response = unserialize($raw_response['body']);
			
			if (is_object($response) && !empty($response)) // Feed the update data into WP updater
				$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
		}
		return $checked_data;
	}

	public function _plugin_api_call($def, $action, $args) {
		
		if(in_array($args->slug, self::$_updatables))
			$plugin_slug = $args->slug;
		else
			return false;
		
		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
		$args->version = $current_version;
		
		$request_string = $this->_prepare_request($action, $args);
		
		$request = wp_remote_post($this->updateApiUrl, $request_string);
		
		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);
			
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}
		
		return $res;
	}
	
	private function _prepare_request($action, $args) {
		global $wp_version;
		
		return array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);	
	}
} ?>