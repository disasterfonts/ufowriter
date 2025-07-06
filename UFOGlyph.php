<?php

namespace Disasterfonts\UFOwriter;

/*
<?xml version="1.0" encoding="UTF-8"?>
<glyph name="A" format="2">
  <advance width="600"/>
  <unicode hex="0041"/>
  <outline>
    <contour>
      <point x="0" y="0" type="line"/>
      <point x="100" y="0" type="line"/>
      <point x="100" y="100" type="line"/>
      <point x="0" y="100" type="line"/>
    </contour>
  </outline>
</glyph>

glyph
	@name
	@format
	advance
		@width
	unicode
		@hex
	outline
		contour
			point
				@x
				@y
				@type



*/

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class UFOGlyph
{
	public $name;
	public $unicode;
	public $width;
	public $height;
	public $pixelSize;
	public $pixelData;
	public $contours;
	
	function __construct($glyphName, $glyphUnicode, $glyphDimensions, $glyphPixelData = array()) {
		$this->name = $glyphName;
		$this->unicode = $glyphUnicode;
		$this->width = $glyphDimensions["width"];
		$this->height = $glyphDimensions["height"];
		switch ($this->height) {
			case 7:
				$this->pixelSize = 80;
				break;
			default:
				$this->pixelSize = 720 / $this->height;
				break;
		}
		
		$this->pixelData = $glyphPixelData;
		$this->contours = $this->buildPixelSquareContours($glyphPixelData);
	}
	
	function __toString() {
		return json_encode($glyphPixelData);
	}
	
	public function buildGlyphXML($root = true) {
		$glyph = [
				"@name" => $this->name,
				"@format" => 2,
				"advance" => [
					"@width" => $this->pixelSize * ($this->width + 1)
				],
				"unicode" => [
					"@hex" => $this->unicode
				],
				"outline" => [
					"contour" => $this->contours,
				]
		];
		
		$encoder = new XmlEncoder();
		
		if ($root) {
			$glyphXML = $encoder->encode($glyph, "xml", [
				'xml_format_output' => true,
				'xml_root_node_name' => 'glyph'
				]
			);
		} else {
			$glyphXML = $encoder->encode($glyph, "xml", [
					'xml_format_output' => true,
					'xml_root_node_name' => 'glyph',
					'encoder_ignored_node_types' => [\XML_PI_NODE],
				]
			);
		}
		
		return $glyphXML;
	}
	
	protected function buildPixelSquareContours($pixels = array()) {
		// turn pixel data into UFO-format square contours
		$contours = array();
		
		for ($row = 0; $row<$this->height; $row ++) {
			for ($col = $this->width-1; $col>=0; $col --) {
				$pixel = array_pop($pixels);
				if ($pixel["status"] == 1) {
					$contour = [
						"point" => [
							[
								"@x" => ($col * $this->pixelSize) + $this->pixelSize,
								"@y" => ($row * $this->pixelSize) + $this->pixelSize,
								"@type" => "line"
							],
							[
								"@x" => ($col * $this->pixelSize),
								"@y" => ($row * $this->pixelSize) + $this->pixelSize,
								"@type" => "line"
							],
							[
								"@x" => ($col * $this->pixelSize),
								"@y" => ($row * $this->pixelSize),
								"@type" => "line"
							],
							[
								"@x" => ($col * $this->pixelSize) + $this->pixelSize,
								"@y" => ($row * $this->pixelSize),
								"@type" => "line"
							],
						],
					];
					array_push($contours, $contour);
				}
			}
		}
		array_reverse($contours);
		return $contours;
	}
}