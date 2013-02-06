<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEMODEL is basic class for model objects
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/THEMASTER/
 * @package   THEMASTER
 */
class THEMODEL
{
    public $ID;

    public static $table;

    public function __construct($initArgs = null)
    {
        if (is_int($initArgs)) {
            $this->ID = $initArgs;
        } elseif (is_array($initArgs) || is_object($initArgs)) {
            foreach ($initArgs as $k => $v) {
                $this->$k = $v;
            }
        }
        $this->init();
    }

    public function init()
    {
    }

    public function get( $name )
    {
        if ( method_exists( $this, 'get_' . $name ) ) {
            $args = func_get_args();
            unset( $args[0] );

            return call_user_func_array( array( $this, 'get_' . $name ), $args );
        }

        return $this->$name;
    }

    public function set($name, $var)
    {
        if ( method_exists( $this, 'set_' . $name )) {
            return call_user_func( $f );
        }
        if($var == '++' && is_int($this->$name))
            $this->$name = $this->$name+1;
        elseif($var == '--' && is_int($this->$name))
            $this->$name--;
        else
            $this->$name = $var;

        return $this;
    }
}
