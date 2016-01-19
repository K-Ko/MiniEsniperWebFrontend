<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

ini_set('display_errors', 1);
error_reporting(-1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOTDIR', dirname(__DIR__));

include 'index.inc.php';
include 'index.html';
