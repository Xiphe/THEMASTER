<?php
/*
Plugin Name: THE MASTER
Plugin URI: https://github.com/Xiphe/-THE-MASTER
Namespace: Xiphe\THEMASTER
Description: A Plugin to provide global access to the THEWPMASTER class. THEWPMASTER provides a lot of handy functions for plugins an themes.
Version: 3.1.0.11
Date: 2012-30-10 22:00:00 +02:00
Author: Hannes Diercks aka Xiphe
Author URI: https://github.com/Xiphe
Update Server: http://plugins.red-thorn.de/v2/api/
Branch: 3.1_Alpha
*/

/*
 Copyright (C) 2012 Hannes Diercks

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Xiphe\THEMASTER;

    /* -------------------- *
     *  DEVELOPMENMT STUFF  *
     * -------------------- */
    
    /*
     * In development i use one central symlinked version of this plugin.
     * Whenever something seems to be wrong i always uncomment this line
     * to check if i use the latest version or if the link was broken
     * through a sync process.
     */
    // die('THEMASTER SYMLINKED');

    /*
     * A quick breakpoint for xdebug.
     */ 
    // \xdebugBreak();

    /*
     * Some settings for development (if anything inside the master is broken).
     */
    // $tmTextID = basename(dirname(__FILE__)).'/'.basename(__FILE__);
    // $tmSettingsID = 'THEMASTER_' . strtoupper($tmTextID);
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);
    // define($tmSettingsID.'_ERRORREPORTING', true);
    // define($tmSettingsID.'_DEBUG', true);
    // define($tmSettingsID.'_DEBUGMODE', 'FirePHP');

    include 'globaldebug.php';
    
/*
 * Define THEMASTER_HAS_WPRDPRESS for use in WP().
 */
if (class_exists('\WP')) {
    define('THEMASTER_HAS_WPRDPRESS', true);
} else {
    define('THEMASTER_HAS_WPRDPRESS', false);
}

/**
 * Shorthand funcion to check if Wordpress is available.
 *
 * @uses    const THEMASTER_HAS_WPRDPRESS
 * @return  bool  true if \WP Class exists - false if not.
 */
function WP()
{
    return THEMASTER_HAS_WPRDPRESS;
}

/*
 * I am using the DS constant as a shorthand for DIRECTORY_SEPARATOR.
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
} elseif (DS !== DIRECTORY_SEPARATOR) {
    $msg = __('The required constant "DS" is not available so !themaster will be deactivated.', 'themaster');
    if (WP()) {
        add_action(
            'admin_notices',
            function () use ($msg) {
                echo "<div class=\"error\"><p>$msg</p></div>";
            }
        );
        deactivate_plugins(__FILE__);
    } else {
        die($msg);
    }
}

/*
 * Register itself to be updatable.
 * This is the easyest way to enable the update logic of THEWPUPDATES
 * to a plugin that does not use !THE MASTER.
 */
$GLOBALS['Xiphe\THEMASTER\Updatable'][] = __FILE__;

/*
 * Save the path to this file.
 */
define('THEMASTER_PROJECTFILE', __FILE__);

/*
 * Save the path to this file.
 */
define('THEMASTER_PROJECTFOLDER', dirname(__FILE__).DS);

/*
 * Save the path to this file.
 */
define('THEMASTER_COREFOLDER', dirname(__FILE__).DS.'core'.DS);


spl_autoload_register(function($class) {
    if (strpos($class, 'Xiphe\THEMASTER\\') === 0) {
        $path = explode('\\', $class);
        $name = end($path);
        $path = array_splice($path, 2, -1);
        $path[] = $name.'.php';
        $path = implode(DS, $path);

        if ((strpos($path, 'classes'.DS) !== 0 && strpos($path, 'models'.DS) !== 0)
            || file_exists(THEMASTER_PROJECTFOLDER.$path)
        ) {
            if (!file_exists(THEMASTER_PROJECTFOLDER.$path)) {
                debug(sprintf('Invalid include path "%s".', THEMASTER_PROJECTFOLDER.$path), 4);
                diebug('callstack');
            } else {
                require(THEMASTER_PROJECTFOLDER.$path);
            }
        }
    } elseif(in_array($class, array('Xiphe\THETOOLS', 'Xiphe\THEWPTOOLS', 'Xiphe\THEDEBUG'))) {
        $file = str_replace('Xiphe\\', '', $class).'.php';
        include(THEMASTER_PROJECTFOLDER.'tools'.DS.$file);
    } elseif(WP()) {
        $path = explode('\\', $class);
        unset($path[0]);
        $path = implode(DS, $path).'.php';
        foreach(array('plugins', 'themes') as $t) {
            $t = \Xiphe\THETOOLS::unify_slashes(ABSPATH).'wp-content'.DS.$t.DS.$path;
            if (file_exists($t)) {
                include $t;
                break;
            }
        }
    }
});


if (WP()) {

    /**
     * Transforms the filepath as if it is a subdirectory of wp-content/themes or
     * wp-content/plugins.
     *
     * This is required for symliked projects because the activation_hooks of wordpress
     * do not work if the project is not located inside the wp-content folder.
     *
     * @param  string  $path     the realpath of the project
     * @param  boolean $isTheme  set true for themes
     * @param  boolean $hasNoDir set true if is one-file-plugin.
     * @return string            the symlink path.
     */
    function get_wpInstallPath($path, $isTheme = false, $hasNoDir = false)
    {
        $rel = !$hasNoDir ? basename(dirname($path)).DS : '';
        $rel .= basename($path);
        $rel = ABSPATH.'wp-content'.DS.(!$isTheme ? 'plugins' : 'themes').DS.$rel;
        return preg_replace('/[\\\|\/]/', DS, $rel);
    }

    /*
     * Register activation hook.
     */
    register_activation_hook(
        get_wpInstallPath(__FILE__),
        function () {
            core\THEWPMASTER::_masterActivate();
        }
    );

    /*
     * Register activation hook.
     */
    register_deactivation_hook(
        get_wpInstallPath(__FILE__),
        function () {
            core\THEWPMASTER::_masterDeactivate();
        }
    );
}


/*
 * Include core File - automaticaly includes required core files and instantiates a base instance.
 */
if (!defined('THEWPMASTERAVAILABE')) {
    try {
        if (!defined('THEMINIWPMASTERAVAILABLE') && WP()) {
            $GLOBALS['THEMINIWPMASTER'] = new core\THEWPMASTER('MINIMASTER');
            define('THEMINIWPMASTERAVAILABLE', true);
        } elseif (!defined('THEMINIMASTERAVAILABLE')) {
            $GLOBALS['THEMINIMASTER'] = new core\THEMASTER('MINIMASTER');
            define('THEMINIMASTERAVAILABLE', true);
        }
    } catch (\Exception $e) {
        /*
         * Errors Occured -> try to write an admin notice.
         */
        collect_tmInitErrors($e);
    }
}

/**
 * Basic Exception Handler.
 * 
 * @param  Exception $e Runtime Exception from MINIMASTER init.
 * @return void
 */
function collect_tmInitErrors($e) {
    if (!isset($GLOBALS['THEWPMASTERINITERRORS'])) {
        if (WP()) {
            $GLOBALS['THEWPMASTERINITERRORS'] = array();
            add_action('admin_notices', function() {
                foreach($GLOBALS['THEWPMASTERINITERRORS'] as $e) {
                    echo '<div class="error"><p>'.$e->getMessage().'<br />File:'.$e->getFile().' Line:'.$e->getLine().'</p></div>';
                }
            });
        } else {
            echo '<div class="error"><p>'.$e->getMessage().'<br />File:'.$e->getFile().' Line:'.$e->getLine().'</p></div>';
            return;
        }
    }
    $k = $e->getFile().$e->getLine();
    $GLOBALS['THEWPMASTERINITERRORS'][$k] = $e;
}

/**
 * Initiation for Plugins and Themes that want to use THE MASTER.
 * Automaticaly parses initiation args from main plugin file or 
 * theme style.
 *
 * @param   mixed  $initArgs optional additional initiation arguments. 
 *                           Keys are overwriting parsed init args.
 *                           Set Key "projectName" to prevent auto parsing.
 *                           This can also be a path to the projects info
 *                           file so the debug backtrace is not required
 *                           to get the called file.
 * @param   string $file     This can be a path to infofile if the first
 *                           param contains additional init args.    
 * @return  object           Instance of the Projects Master or false if error.
 */
function INIT($initArgs = null, $file = null)
{
    /*
     * If init args or key projectName is not set and given project-info-file is valid.
     */
    if ((!is_array($initArgs) || !isset($initArgs['projectName']))
     && (null === $initArgs
         || (is_string($initArgs) && file_exists($initArgs))
         || (is_string($file) && file_exists($file))
    )) {
        /*
         * Get initiation arguments from project file.
         */
        $filesInitArgs = core\THEWPBUILDER::get_initArgs($initArgs, $file);

        /*
         * Error occurred.
         */
        if (empty($filesInitArgs)) {
            return false;
        }

        /*
         * Check if additional args were given and merge them.
         */
        if (is_array($initArgs)) {
            $initArgs = array_merge($filesInitArgs, $initArgs);
        } else {
            $initArgs = $filesInitArgs;
        }
        
        /*
         * Add Masterflag.
         */
        $initArgs['isMaster'] = true;
    } 

    /*
     * Try to build a new Master with the initiation arguments.
     */
    try {
        $r = core\THEWPMASTER::get_instance( 'Master', $initArgs );
    } catch (\Exception $e) {
        /*
         * Errors Occured -> try to write an admin notice.
         */
        collect_tmInitErrors($e);

        /*
         * Do not return the object because it was not initated correctly.
         */
        $r = false;
    }
    return $r;
}

/**
 * This function can be used to automaticaly build skeletion projects.
 *
 * 1.  Check out `wp-content/plugins/_themaster/templates/new`.
 * 2.  Copy the theme or plugin folder into your wp-content/themes 
 *     or wp-content/plugins folder.
 * 3.  Rename the projects foldername
 * 3.b If you are generating a plugin also rename the plugin file.
 * 4.  Set the Project name in style.css or plugin file.
 * 5.  Activate the Project.
 * 6.  Revisit the style.css or project file and fill in additional informations.
 *
 * @param  bool   $extended set false if the project should just contain the absolute basic files and logic.
 * @param  string $template the name of the template that should be used. Default is "def" and it's the
 *                         only one that's delivered at the time of 3.0
 * @return void
 */
function BUILD($extended = true, $template = 'def')
{
    $args = core\THEWPBUILDER::get_initArgs();

    if (!isset($args['projectName'])) {
        throw new \Exception('BUILDTHEWPMASTERPLUGIN called in invalid file.', 1);
    } elseif (!isset($args['date'])) {
        core\THEWPBUILDER::sbuild('init', $args, $template, $extended);
    } elseif (!isset($args['version'])) {
        core\THEWPBUILDER::missing_initArgs($args);
    } else {
        core\THEWPBUILDER::sbuild('full', $args, $template, $extended);
    }
}
?>