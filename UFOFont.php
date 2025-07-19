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
	public $glyphComposites = array();
	public $logger;
	
	
	function __construct($fontName, $fontDimensions, $glyphs, $fontUPM = 1000, $pixelSize = 100) {
		$this->logger = new Logger('ufowriter');
		$this->logger->pushHandler(new StreamHandler('/home/weblite/www/ufowriter/logz/log.txt', Level::Notice));
		
		switch ($fontDimensions["height"]) {
			case 7:
				$pixelSize = $pixelSize;
				break;
			default:
				$pixelSize = 720 / $fontDimensions["height"];
				break;
		}
		$capHeight = $pixelSize * $fontDimensions["height"];
		$this->name = $fontName;
		$this->dimensions = [
			"height" => $fontDimensions["height"],
			"width" => $fontDimensions["width"],
			"ascender" => 800,
			"descender" => -200,
			"xHeight" => $capHeight,
			"capHeight" => $capHeight,
		];
		$this->pixelSize = $pixelSize;
		$this->UPM = $fontUPM;
		$this->glyphComposites = $this->glyphComposites();
		$this->glyphs = $this->orderGlyphsByUnicode($glyphs);
	}
	
	
	private function orderGlyphsByUnicode($glyphs) {
		usort($glyphs, function($a, $b) {
			return hexdec($a["unicode"]) - hexdec($b["unicode"]);
		});
		return $glyphs;
	}
	
	
	private function getGlyphIndex($glyph) {
		$this->logger->notice("composites index array: " . $glyph["glyphName"]);
		return $glyph["glyphName"];
	}
	private function getGlyphCompositeIndex($glyph) {
		return $glyph[0];
	}
	
	private function glyphFilenamePad($glyphIndexName) {
		// get initial, insert _ if alpha caps
		// *** TODO: Æ Œ Ð etc
		$initial_char = substr($glyphIndexName,0,1);
		if (ord($initial_char) >= 65 && ord($initial_char) <= 91) {
			$glyphFilename = substr($glyphIndexName,0,1) . "_" . substr($glyphIndexName,1) . '.glif';
		} else {
			$glyphFilename = $glyphIndexName . '.glif';
		}
		return $glyphFilename;
	}
	
	
	private function glyphComposites() {
		$glyphComposites = [
			// name, unicode, base, xoffset, yoffset, accent 
			["Agrave",        "00C0", "A", 0, 1, "grave"],
			["Aacute",        "00C1", "A", 0, 1, "acute"],
			["Acircumflex",   "00C2", "A", 0, 1, "circumflex"],
			["Atilde",        "00C3", "A", 0, 1, "tilde"],
			["Adieresis",     "00C4", "A", 0, 1, "dieresis"],
			["Aring",         "00C5", "A", 0, 1, "ring"],
			["Amacron",       "0100", "A",-1, 1, "macron"],
			["Abreve",        "0102", "A", 0, 1, "breve"],
			["Aogonek",       "0104", "A", 1,-2, "ogonek"],
			
			["Ccedilla",      "00C7", "C", 1,-2, "cedilla"],
			["Cacute",        "0106", "C", 0, 1, "acute"],
			["Ccircumflex",   "0108", "C", 0, 1, "circumflex"],
			["Cdotaccent",    "010A", "C", 0, 1, "dotaccent"],
			["Ccaron",        "010C", "C", 0, 1, "caron"],
			
			["Dcaron",        "010E", "D", 0, 1, "caron"],
			
			["Egrave",        "00C8", "E", 0, 1, "grave"],
			["Eacute",        "00C9", "E", 0, 1, "acute"],
			["Ecircumflex",   "00CA", "E", 0, 1, "circumflex"],
			["Edieresis",     "00CB", "E", 0, 1, "dieresis"],
			["Emacron",       "0112", "E",-1, 1, "macron"],
			["Ebreve",        "0114", "E", 0, 1, "breve"],
			["Ecaron",        "011A", "E", 0, 1, "caron"],
			["Edotaccent",    "0116", "E", 0, 1, "dotaccent"],
			["Eogonek",       "0118", "E", 1,-2, "ogonek"],
			
			["Gcircumflex",   "011C", "G", 0, 1, "circumflex"],
			["Gbreve",        "011E", "G", 0, 1, "breve"],
			["Gdotaccent",    "0120", "G", 0, 1, "dotaccent"],
			["uni0122",       "0122", "G", 0,-3, "commaaccent"],
			
			["Igrave",        "00CC", "I", 0, 1, "grave"],
			["Iacute",        "00CD", "I", 0, 1, "acute"],
			["Icircumflex",   "00CE", "I", 0, 1, "circumflex"],
			["Idieresis",     "00CF", "I", 0, 1, "dieresis"],
			["Itilde",        "0128", "I", 0, 1, "tilde"],
			["Imacron",       "012A", "I",-1, 1, "macron"],
			["Iogonek",       "012E", "I", 1,-2, "ogonek"],
			
			["Lacute",        "0139", "L",-2, 1, "acute"],
			["uni013B",       "013B", "L", 0,-4, "commaaccent"],
			["Lcaron",        "013D", "L", 1, 0, "quotesingle"],
			["Ldot",          "013F", "L", 1, 0, "dotaccent"],
			
			["Nacute",        "0143", "N", 0, 1, "acute"],
			["uni0145",       "0145", "N", 0,-3, "commaaccent"],
			["Ncaron",        "0147", "N", 0, 1, "caron"],
			["Ntilde",        "00D1", "N", 0, 1, "tilde"],
			
			["Ograve",        "00D2", "O", 0, 1, "grave"],
			["Oacute",        "00D3", "O", 0, 1, "acute"],
			["Ocircumflex",   "00D4", "O", 0, 1, "circumflex"],
			["Otilde",        "00D5", "O", 0, 1, "tilde"],
			["Odieresis",     "00D6", "O", 0, 1, "dieresis"],
			["Omacron",       "014C", "O",-1, 1, "macron"],
			["Obreve",        "014E", "O", 0, 1, "breve"],
			["Ohungarumlaut", "0150", "O",-1, 1, "hungarumlaut"],
			
			["Ugrave",        "00D9", "U", 0, 1, "grave"],
			["Uacute",        "00DA", "U", 0, 1, "acute"],
			["Ucircumflex",   "00DB", "U", 0, 1, "circumflex"],
			["Udieresis",     "00DC", "U", 0, 1, "dieresis"],
			["Utilde",        "0168", "U", 0, 1, "tilde"],
			["Umacron",       "016A", "U",-1, 1, "macron"],
			["Ubreve",        "016C", "U", 0, 1, "breve"],
			["Uring",         "016E", "U", 0, 1, "ring"],
			["Uhungarumlaut", "0170", "U",-1, 1, "hungarumlaut"],
			["Uogonek",       "0172", "U", 0,-2, "ogonek"],
			
			["Wgrave",        "1E80", "W", 0, 1, "grave"],
			["Wacute",        "1E82", "W", 0, 1, "acute"],
			["Wcircumflex",   "0174", "W", 0, 1, "circumflex"],
			["Wdieresis",     "1E84", "W", 0, 1, "dieresis"],
			
			["Yacute",        "00DD", "Y",-1, 1, "acute"],
			["Ycircumflex",   "0176", "Y",-1, 1, "circumflex"],
			["Ydieresis",     "0178", "Y", 0, 1, "dieresis"],
			
			["Zacute",        "0179", "Z", 0, 1, "acute"],
			["Zdotaccent",    "017B", "Z", 0, 1, "dotaccent"],
			["Zcaron",        "017D", "Z", 0, 1, "caron"],
			
		];
		usort($glyphComposites, function($a, $b) {
			return hexdec($a[1]) - hexdec($b[1]);
		});
		return $glyphComposites;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function buildFontXML() {
		
		$capHeight = $this->pixelSize * $this->dimensions["height"];
		$this->logger->notice($this->name . " build start, " . "dimensions: " . print_r($this->dimensions,1));
		
		// fontinfo.plist
		$fontinfo_plist = new CFPropertyList();
		$fontinfo_plist->add( $dict = new CFDictionary() );
		$dict->add( 'designer', new CFString( 'FONTMINT' ) );
		$dict->add( 'familyName', new CFString( $this->name ) );
		$dict->add( 'unitsPerEm', new CFNumber( $this->UPM ) );
		$dict->add( 'capHeight', new CFNumber( $capHeight ) );
		$dict->add( 'xHeight', new CFNumber( $capHeight ) );
		$dict->add( 'descender', new CFNumber( -200 ) );
		$dict->add( 'ascender', new CFNumber( 800 ) );
		$fontinfo_xml = $fontinfo_plist->toXML();
		
		// metainfo.plist
		$metainfo_plist = new CFPropertyList();
		$metainfo_plist->add( $dict = new CFDictionary() );
		$dict->add( 'creator', new CFString( 'uk.co.disasterfonts' ) );
		$dict->add( 'formatVersion', new CFString( '2' ) );
		$metainfo_xml = $metainfo_plist->toXML();
		
		// groups.plist
		$groups_plist = new CFPropertyList();
		$groups_plist->add( $dict = new CFDictionary() );
		$groups_xml = $groups_plist->toXML();
		
		
		$glyphIndexArray = array_map(
			[$this, 'getGlyphIndex'],
			$this->glyphs
		);
		$glyphCompositesIndexArray = array_map(
			[$this, 'getGlyphCompositeIndex'],
			$this->glyphComposites
		);
		
		
		// lib.plist
		$lib_plist = new CFPropertyList();
		$lib_plist->add( $dict = new CFDictionary() );
		$dict->add('public.glyphOrder', $glyphList_array = new CFArray());
		foreach($glyphIndexArray as $glyphIndex) {
			$glyphList_array->add(new CFString($glyphIndex));
		}
		foreach($glyphCompositesIndexArray as $glyphIndex) {
			$glyphList_array->add(new CFString($glyphIndex));
		}
		$lib_xml = $lib_plist->toXML();
		
		// glyphs
		$glyphs_xml_array = $this->buildGlyphsXML($this->glyphs);
		$composite_glyphs_xml_array = $this->buildCompositeGlyphsXML($this->glyphComposites);
		$glyphs_xml_array = array_merge($glyphs_xml_array, $composite_glyphs_xml_array);
		
		// glyphs/contents.plist
		$contents_plist = new CFPropertyList();
		$contents_plist->add( $dict = new CFDictionary() );
		foreach($glyphIndexArray as $glyphIndexName) {
			$glyphFilename = $this->glyphFilenamePad($glyphIndexName);
			$dict->add($glyphIndexName, new CFString($glyphFilename) );
		}
		foreach($glyphCompositesIndexArray as $glyphIndexName) {
			$glyphFilename = $this->glyphFilenamePad($glyphIndexName);
			$dict->add($glyphIndexName, new CFString($glyphFilename) );
		}
		$contents_xml = $contents_plist->toXML();
		
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
	
	
	private function buildCompositeGlyphsXML($compositeGlyphs) {
		$composite_glyphs_xml_array = [];
		// [0 name, 1 unicode, 2 base, 3 xoffset, 4 yoffset, 5 accent]
		
		foreach($compositeGlyphs as $glyphComposite) {
			
			$glyph_ufo = new UFOGlyph(
				$glyphComposite[0], // name
				$glyphComposite[1], // unicode
				$this->dimensions,
				[],
			);
			$composite_glyphs_xml_array[$glyphComposite[0]] = $glyph_ufo->buildCompositeGlyphXML($glyphComposite[2], $glyphComposite[5], $glyphComposite[3], $glyphComposite[4]);
		}
		return $composite_glyphs_xml_array;
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