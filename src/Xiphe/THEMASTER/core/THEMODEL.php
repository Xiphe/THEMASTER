<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEMODEL is basic class for model objects
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.1.0
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
            foreach ($initArgs as $key => $value) {
                $this->set($key, $value);
            }
        }

        $this->init();
    }

    public function init()
    {
    }

    public function get($name)
    {
        $method = "get_$name";
        if (method_exists($this, $method)) {
            $args = func_get_args();
            unset($args[0]);

            return call_user_func_array(array($this, $method), $args );
        } elseif (isset($this->$name)) {
            return $this->$name;
        } else {
            return;
        }
    }

    public function set($name, $var)
    {
        $method = "set_$name";
        if (method_exists($this, $method)) {
            $args = func_get_args();
            unset($args[0]);

            return call_user_func_array(array($this, $method), $args);
        }

        if ($var === '++' && is_int($this->$name)) {
            $this->$name = $this->$name+1;
        } elseif($var === '--' && is_int($this->$name)) {
            $this->$name--;
        } else {
            $this->$name = $var;
        }

        return $this;
    }
}
