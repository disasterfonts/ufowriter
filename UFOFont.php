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

use CFPropertyList\CFPropertyList;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFNumber;
use CFPropertyList\CFString;
use CFPropertyList\CFArray;
use League\Flysystem\Local;

class UFOFont
{
	public $name;
	public $fontUPM;
	public $pixelSize;
	public $glyphs = array();
	
	
	function __construct($fontName, $fontDimensions, $glyphs, $fontUPM = 1000, $pixelSize = 80) {
		switch ($fontDimensions["height"]) {
			case 7:
				$pixelSize = $pixelsize;
				break;
			default:
				$pixelSize = 720 / $fontDimensions["height"];
				break;
		}
		$this->name = $fontName;
		$this->pixelSize = $pixelSize;
		$this->fontUPM = $fontUPM;
		$this->glyphs = $this->orderGlyphsByUnicode($glyphs);
	}

	
	public function buildFontXML() {

		$fontinfo_plist = new CFPropertyList();
		$fontinfo_plist->add( $dict = new CFDictionary() );
		$dict->add( 'designer', new CFString( 'FONTMINT' ) );
		$dict->add( 'familyName', new CFString( $fontName ) );
		$dict->add( 'unitsPerEm', new CFNumber( $fontUPM ) );
		$dict->add( 'capHeight', new CFNumber( $pixelSize * $fontDimensions["height"] ) );
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
		foreach($glyphIndexArray as $glyphIndex) {
			if (ord($glyphIndex) >= 65 && ord($glyphIndex) <= 91) {
				$glyphFile = str_pad($glyphIndex, 2, "_") . '.glif';
			} else {
				$glyphFile = $glyphIndex . '.glif';
			}
			$dict->add($glyphIndex, new CFString($glyphFile) );
		}
		$contents_xml = $contents_plist->toXML();
		
		
		
		return array(
			$fontinfo_xml,
			$groups_xml,
			$lib_xml,
			$metainfo_xml,
			$contents_xml,
			// $glyphs_xml,
		);
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
	
	public function writeFontFiles($outputDir) {
		$adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($outputDir . '/storage');
	}
}