<?php
require 'vendor/autoload.php';

require './UFOFont.php';

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use League\Flysystem\Local;

$logger = new Logger('ufowriter');
$logger->pushHandler(new StreamHandler('/home/weblite/www/ufowriter/logz/log.txt', Level::Notice));

$exportInfo = file_get_contents('php://input');

if (json_validate($exportInfo)) {
	// dump fontmint json
	$fontData = json_decode($exportInfo, true);
	$adapter = new \League\Flysystem\Local\LocalFilesystemAdapter("./assets/json_data");
	$filesystem = new \League\Flysystem\Filesystem($adapter);
	$filesystem->write($fontData["fontName"] . ".json", $exportInfo);
	
	// build UFO font
	$font = new Disasterfonts\UFOwriter\UFOFont($fontData["fontName"], $fontData["fontDimensions"], $fontData["glyphs"], 1000, 100);
	$font->writeFontFiles('./assets/ufo_output/' . $fontData["fontName"] . '.ufo');
	$logger->notice("Font apparently written.");
} else {
	$exportInfo - array();
	$logger->notice("Invalid font JSON.");
}

