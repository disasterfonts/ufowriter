<?php
require 'vendor/autoload.php';

require './UFOFont.php';

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('ufowriter');
$logger->pushHandler(new StreamHandler('/home/weblite/www/ufowriter/logz/log.txt', Level::Notice));

$exportInfo = file_get_contents('php://input');

if (json_validate($exportInfo)) {
	$fontData = json_decode($exportInfo, true);
	$font = new Disasterfonts\UFOwriter\UFOFont($fontData["fontName"], $fontData["fontDimensions"], $fontData["glyphs"], 1000, 80);
	$font->writeFontFiles('./assets/ufo_output/' . $fontData["fontName"] . '.ufo');
	$logger->notice("Font apparently written.");
} else {
	$exportInfo - array();
	$logger->notice("Invalid font JSON.");
}

