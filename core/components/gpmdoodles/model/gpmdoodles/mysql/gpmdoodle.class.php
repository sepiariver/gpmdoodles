<?php
/**
 * @package gpmdoodles
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gpmdoodle.class.php');
class GPMDoodle_mysql extends GPMDoodle {}
?>