<?php

namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEAUTOLOADER loads Plugin and Theme Classes
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <info@xiphe.net>
 * @version   3.2.0
 * @link      https://github.com/Xiphe/THEMASTER/
 * @package   THEMASTER
 */
class THEAUTOLOADER {

	public static $knownProjects = array();


	public static function autoload($class) {
		$relPath = explode('\\', $class);

		$baseSpace = implode('\\', array_splice($relPath, 0, 2));

		if (isset(self::$knownProjects[$baseSpace])) {
			$file = X\THETOOLS::unify_slashes(
				str_replace($baseSpace.'\\', self::$knownProjects[$baseSpace], $class),
				DS
			).'.php';
			if (file_exists($file)) {
				include $file;
			}
		}
	}

	public static function add($namespace, $path) {
		self::$knownProjects[$namespace] = $path;
	}
}