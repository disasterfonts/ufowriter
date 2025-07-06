<?php
require 'vendor/autoload.php';

require './UFOGlyph.php';

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$output_path = "/home/weblite/www/ufowriter";

$exportInfo = file_get_contents('php://input');

if (json_validate($exportInfo)) {
	$exportInfo = json_decode($exportInfo, true);
} else {
	$exportInfo - array();
}

$logger = new Logger('ufowriter');
$logger->pushHandler(new StreamHandler($output_path . '/logz/log.txt', Level::Notice));
$logger->notice(json_encode($exportInfo));



$template_base_dir = $output_path . "/ufo_template/";
$output_base_dir = $output_path . "/ufo_output/";
$font_dir = $name . ".ufo";

// assume 1000upm, 750 cap height

