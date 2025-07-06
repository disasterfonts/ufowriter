<?php

namespace Disasterfonts\UFOwriter;

/*
fontinfo.plist:
	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	    <key>designer</key>
	    <string>Andrew Young</string>
	    <key>familyName</key>
	    <string>UFOTEST</string>
	    <key>unitsPerEm</key>
	    <integer>1000</integer>
	    <key>capHeight</key>
	    <integer>500</integer>
	</dict>
	</plist>

	plist
		@version
		dict
			key
			string



metainfo.plst:
	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	    <key>creator</key>
	    <string>uk.co.disasterfonts</string>
	    <key>formatVersion</key>
	    <integer>2</integer>
	</dict>
	</plist>

	plist
		@version
		dict
			key
			string



lib.plist:
	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	  <key>public.glyphOrder</key>
	  <array>
	    <string>A</string>
	  </array>
	</dict>
	</plist>

	plist
		@version
		dict
			key
			array
				string



groups.plist:
	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	</dict>
	</plist>

	plist
		@version
		dict


glyphs/contents.plist:
	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	  <key>A</key>
	  <string>A_.glif</string>
	  <key>B</key>
	  <string>B_.glif</string>
	  <key>C</key>
	  <string>C_.glif</string>
	</dict>
	</plist>


*/

require './UFOGlyph.php';

use CFPropertyList\CFPropertyList;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFNumber;
use CFPropertyList\CFString;
use CFPropertyList\CFArray;
use League\Flysystem\Local;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Disasterfonts\UFOwriter\UFOGlyph;

class UFOFont
{
	public $name;
	public $UPM;
	public $dimensions;
	public $pixelSize;
	public $glyphs = array();
	
	public $logger;
	
	
	function __construct($fontName, $fontDimensions, $glyphs, $fontUPM = 1000, $pixelSize = 100) {
		switch ($fontDimensions["height"]) {
			case 7:
				$pixelSize = $pixelsize;
				break;
			default:
				$pixelSize = 720 / $fontDimensions["height"];
				break;
		}
		$this->name = $fontName;
		$this->dimensions = $fontDimensions;
		$this->pixelSize = $pixelSize;
		$this->UPM = $fontUPM;
		$this->glyphs = $this->orderGlyphsByUnicode($glyphs);
		$this->logger = new Logger('ufowriter');
	}
	
	
	private function orderGlyphsByUnicode($glyphs) {
		usort($glyphs, function($a, $b) {
			return hexdec($a["unicode"]) - hexdec($b["unicode"]);
		});
		return $glyphs;
	}
	
	
	private function getGlyphIndex($glyph) {
		return $glyph["glyphName"];
	}
	
	
	private function glyphFilenamePad($glyphIndexName) {
		if (ord($glyphIndexName) >= 65 && ord($glyphIndexName) <= 91) {
			$glyphFilename = str_pad($glyphIndexName, 2, "_") . '.glif';
		} else {
			$glyphFilename = $glyphIndexName . '.glif';
		}
		return $glyphFilename;
	}
	
	
	public function buildFontXML() {
		$this->logger->pushHandler(new StreamHandler('/home/weblite/www/ufowriter/assets/ufo_output/log.txt', Level::Notice));
		
		$this->logger->notice($this->name . " build start");
		
		$fontinfo_plist = new CFPropertyList();
		$fontinfo_plist->add( $dict = new CFDictionary() );
		$dict->add( 'designer', new CFString( 'FONTMINT' ) );
		$dict->add( 'familyName', new CFString( $this->name ) );
		$dict->add( 'unitsPerEm', new CFNumber( $this->UPM ) );
		$dict->add( 'capHeight', new CFNumber( $this->pixelSize * $this->dimensions["height"] ) );
		$fontinfo_xml = $fontinfo_plist->toXML();
		
		$metainfo_plist = new CFPropertyList();
		$metainfo_plist->add( $dict = new CFDictionary() );
		$dict->add( 'creator', new CFString( 'uk.co.disasterfonts' ) );
		$dict->add( 'formatVersion', new CFString( '2' ) );
		$metainfo_xml = $metainfo_plist->toXML();
		
		$groups_plist = new CFPropertyList();
		$groups_plist->add( $dict = new CFDictionary() );
		$groups_xml = $groups_plist->toXML();
		
		$glyphIndexArray = array_map(
			[$this, 'getGlyphIndex'],
			$this->glyphs
		);
		
		$lib_plist = new CFPropertyList();
		$lib_plist->add( $dict = new CFDictionary() );
		$dict->add('public.glyphOrder', $glyphList_array = new CFArray());
		foreach($glyphIndexArray as $glyphIndex) {
			$glyphList_array->add(new CFString($glyphIndex));
		}
		$lib_xml = $lib_plist->toXML();
		
		
		$contents_plist = new CFPropertyList();
		$contents_plist->add( $dict = new CFDictionary() );
		foreach($glyphIndexArray as $glyphIndexName) {
			$glyphFilename = $this->glyphFilenamePad($glyphIndexName);
			$dict->add($glyphIndexName, new CFString($glyphFilename) );
		}
		$contents_xml = $contents_plist->toXML();
		
		$glyphs_xml_array = $this->buildGlyphsXML($this->glyphs);
		
		return [
			"fontinfo.plist" => $fontinfo_xml,
			"groups.plist" => $groups_xml,
			"lib.plist" => $lib_xml,
			"metainfo.plist" => $metainfo_xml,
			"glyphs" => [
				"contents.plist" => $contents_xml,
				"glyph_list" => $glyphs_xml_array,
			],
		];
	}
	
	
	private function buildGlyphsXML($glyphs) {
		$glyphs_xml_array = [];
		
		foreach($glyphs as $glyph) {
			$glyph_ufo = new UFOGlyph(
				$glyph["glyphName"],
				$glyph["unicode"],
				$this->dimensions,
				$glyph["cells"]
			);
			$glyphs_xml_array[$glyph["glyphName"]] = $glyph_ufo->buildGlyphXML();
		}
		return $glyphs_xml_array;
	}
	
	
	public function writeFontFiles($outputDir) {
		$adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($outputDir);
		$filesystem = new \League\Flysystem\Filesystem($adapter);
		
		$xml_files = $this->buildFontXML();
		
		foreach($xml_files as $filename => $xml_file) {
			if (substr($filename, -6) == ".plist") {
				// write root .plist files
				$filesystem->write($filename, $xml_file);
			} else {
				// make glyphs dir
				//$filesystem->deleteDirectory($filename);
				$filesystem->createDirectory($filename);
				
				foreach ($xml_file as $glyph_filename => $glyph_file) {
					// write contents.plist
					if (substr($glyph_filename, -6) == '.plist') {
						$filesystem->write("glyphs/" . $glyph_filename, $glyph_file);
					} else {
						foreach($glyph_file as $glyphName=>$glyph) {
							// write glyph file
							$filesystem->write("glyphs/" . $this->glyphFilenamePad($glyphName), $glyph);
						}
					}
				}
			}
		}
	}
}