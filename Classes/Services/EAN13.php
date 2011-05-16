<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Xavier Perseguers (xavier@causal.ch)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Barcode generator, type EAN-13/UPC.
 *
 * @category    Services
 * @package     TYPO3
 * @subpackage  tx_barcodes
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @company     Causal Sàrl, http://causal.ch
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Barcodes_Services_EAN13 extends tslib_gifBuilder {

	protected $x;
	protected $colorOn;
	protected $colorOff;

	/**
	 * @var tslib_cObj
	 */
	protected $cObj;

	/**
	 * Default constructor.
	 *
	 * @param tslib_cObj $cObj
	 */
	public function __construct(tslib_cObj $cObj) {
		$this->cObj = $cObj;
	}

	/**
	 * Configures the barcode generator.
	 *
	 * @param array $conf
	 * @return void
	 */
	public function start(array $conf) {
		// Apply stdWrap on each param
		foreach ($conf as $key => $value) {
			if (substr($key, -1) === '.') {
				$baseKey = substr($key, 0, strlen($key) - 1);
				$conf[$baseKey] = $this->cObj->stdWrap($conf[$baseKey], $value);
			}
		}

		$XY = array(104 * $conf['thickness'], 50);

		if (!preg_match('/^[0-9]$/', $conf['prefix'])) {
			die('Prefix must contain exactly 1 digit');
		}
		if (!preg_match('/^[0-9]{12}$/', $conf['digits'])) {
			die('Digits must contain exactly 12 digits');
		}

		$this->setup = $conf;
		$this->setup['XY'] = $XY[0] . ',' . $XY[1];
	}

	/**
	 * The actual rendering of the image file.
	 * Basically sets the dimensions, the background color, the traverses the array of GIFBUILDER objects and finally setting the transparent color if defined.
	 * Creates a GDlib resource in $this->im and works on that
	 * Called by gifBuild()
	 *
	 * @return	void
	 * @access private
	 */
	public function make() {
		// L/G/R patterns (http://en.wikipedia.org/wiki/EAN-13)
		$ean13Patterns = array(
			0 => array('L' => '0001101', 'G' => '0100111', 'R' => '1110010'),
			1 => array('L' => '0011001', 'G' => '0110011', 'R' => '1100110'),
			2 => array('L' => '0010011', 'G' => '0011011', 'R' => '1101100'),
			3 => array('L' => '0111101', 'G' => '0100001', 'R' => '1000010'),
			4 => array('L' => '0100011', 'G' => '0100011', 'R' => '1011100'),
			5 => array('L' => '0110001', 'G' => '0111001', 'R' => '1001110'),
			6 => array('L' => '0101111', 'G' => '0000101', 'R' => '1010000'),
			7 => array('L' => '0111011', 'G' => '0010001', 'R' => '1000100'),
			8 => array('L' => '0110111', 'G' => '0001001', 'R' => '1001000'),
			9 => array('L' => '0001011', 'G' => '0010111', 'R' => '1110100'),
		);

		// Structure by prefix digit
		$structures = array(
			0 => 'LLLLLLRRRRRR',
			1 => 'LLGLGGRRRRRR',
			2 => 'LLGGLGRRRRRR',
			3 => 'LLGGGLRRRRRR',
			4 => 'LGLLGGRRRRRR',
			5 => 'LGGLLGRRRRRR',
			6 => 'LGGGLLRRRRRR',
			7 => 'LGLGLGRRRRRR',
			8 => 'LGLGGLRRRRRR',
			9 => 'LGGLGLRRRRRR',
		);

		$boundaryPatterns = array(
			'S' => '101',
			'M' => '01010',
			'E' => '101',
		);

			// Get trivial data
		$XY = t3lib_div::intExplode(',', $this->setup['XY']);

			// Gif-start
		$this->im = ImageCreateTrueColor($XY[0], $XY[1]);
		$this->w = $XY[0];
		$this->h = $XY[1];

		$this->colorOn = ImageColorAllocate($this->im, 0x00, 0x00, 0x00);
		$this->colorOff = imageColorAllocate($this->im, 0xFF, 0xFF, 0xFF);

			// backColor is set
		$BGcols = $this->convertColor($this->setup['backColor']);
		$Bcolor = ImageColorAllocate($this->im, $BGcols[0], $BGcols[1], $BGcols[2]);
		ImageFilledRectangle($this->im, 0, 0, $this->w, $this->h, $Bcolor);

		$this->x = 0;
		$digits = $this->setup['digits'];

		// show prefix
		$prefix = $this->setup['prefix'];
		$this->drawPattern('00000000', $prefix, floor(.8 * $this->h));
		// start pattern
		$patternStructure = $structures[$prefix];
		$this->drawPattern($boundaryPatterns['S'], '', $this->h);
		for ($i = 0; $i < 6; $i++) {
			$char = $digits{$i};
			$type = $patternStructure{$i};
			$this->drawPattern($ean13Patterns[$char][$type], $char, floor(.8 * $this->h));
		}
		$this->drawPattern($boundaryPatterns['M'], '', $this->h);
		for ($i = 6; $i < 12; $i++) {
			$char = $digits{$i};
			$type = $patternStructure{$i};
			$this->drawPattern($ean13Patterns[$char][$type], $char, floor(.8 * $this->h));
		}
		$this->drawPattern($boundaryPatterns['E'], '', $this->h);

		if ($this->setup['transparentBackground'])	{
				// Auto transparent background is set
			$Bcolor = ImageColorClosest($this->im, $BGcols[0], $BGcols[1], $BGcols[2]);
			ImageColorTransparent($this->im, $Bcolor);
		} elseif (is_array($this->setup['transparentColor_array']))	{
				// Multiple transparent colors are set. This is done via the trick that all transparent colors get converted to one color and then this one gets set as transparent as png/gif can just have one transparent color.
			$Tcolor = $this->unifyColors($this->im, $this->setup['transparentColor_array'], intval($this->setup['transparentColor.']['closest']));
			if ($Tcolor>=0)	{
				ImageColorTransparent($this->im, $Tcolor);
			}
		}
	}

	/**
	 * Draws an EAN-13 barcode pattern.
	 *
	 * @param string $pattern Bit pattern (string with 0's and 1's)
	 * @param string $char Optional character to be printed below
	 * @param integer $height
	 * @return void
	 */
	protected function drawPattern($pattern, $char, $height) {
		if ($char !== '') {
			ImageChar($this->im, 2, $this->x + $this->setup['thickness'], $height - 1, $char, $this->colorOn);
		}
		for ($c = 0; $c < strlen($pattern); $c++) {
			for ($i = 0; $i < $this->setup['thickness']; $i++) {
				$color = $pattern{$c} === '1' ? $this->colorOn : $this->colorOff;
				ImageLine($this->im, $this->x, 0, $this->x, $height, $color);
				$this->x++;
			}
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/Services/EAN13.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/Services/EAN13.php']);
}

?>