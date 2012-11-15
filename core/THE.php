<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * Shorthand class for THE... full Namespaces.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.1.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THE {
	/**
     * Array of all classes based on THEBASE
     *
     * @access public
     * @var    array
     */
    public static $CLASSES = array(
        'Xiphe\THEMASTER\core\THEBASE',
        'Xiphe\THEMASTER\core\THEMASTER',
        'Xiphe\THEMASTER\core\THEMODEL',
        'Xiphe\THEMASTER\core\THESETTINGS',
        'Xiphe\THEMASTER\core\THEWPBUILDER',
        'Xiphe\THEMASTER\core\THEWPMASTER',
        'Xiphe\THEMASTER\core\THEWPMODEL',
        'Xiphe\THEMASTER\core\THEWPSETTINGS',
        'Xiphe\THEMASTER\core\THEWPUPDATES'
    );

    public static $TOOLS = array(
    	'Xiphe\THETOOLS',
    	'Xiphe\THEWPTOOLS',
    	'Xiphe\THEDEBUG'
    );

    const BASE = 'Xiphe\THEMASTER\core\THEBASE';
    const MASTER = 'Xiphe\THEMASTER\core\THEMASTER';
    const MODEL = 'Xiphe\THEMASTER\core\THEMODEL';
    const SETTINGS = 'Xiphe\THEMASTER\core\THESETTINGS';
    const WPBUILDER = 'Xiphe\THEMASTER\core\THEWPBUILDER';
    const WPMASTER = 'Xiphe\THEMASTER\core\THEWPMASTER';
    const WPMODEL = 'Xiphe\THEMASTER\core\THEWPMODEL';
    const WPSETTINGS = 'Xiphe\THEMASTER\core\THEWPSETTINGS';
    const WPUPDATES = 'Xiphe\THEMASTER\core\THEWPUPDATES';

    const TOOLS = 'Xiphe\THETOOLS';
    const WPTOOLS = 'Xiphe\THEWPTOOLS';
    const DEBUG = 'Xiphe\THEDEBUG';

}