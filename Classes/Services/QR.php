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


require_once(t3lib_extMgm::extPath('barcodes') . 'Classes/Library/phpqrcode/qrlib.php');

/**
 * Barcode generator, type QR.
 *
 * @category    Services
 * @package     TYPO3
 * @subpackage  tx_barcodes
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @company     Causal Sàrl, http://causal.ch
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Barcodes_Services_QR {

	protected $tempPath = 'typo3temp/';
	protected $setup;

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
	public function start($conf) {
		// Apply stdWrap on each param
		foreach ($conf as $key => $value) {
			if (substr($key, -1) === '.') {
				$baseKey = substr($key, 0, strlen($key) - 1);
				$conf[$baseKey] = $this->cObj->stdWrap($conf[$baseKey], $value);
			}
		}

		if (!trim($conf['content'])) {
			die('No content provided');
		}

		$this->setup = $conf;
	}

	/**
	 * Generates the QR-code.
	 *
	 * @param string $filename
	 * @return void
	 */
	protected function make($filename) {
		$errorCorrectionLevel = $this->setup['correctionLevel'];
		$matrixPointSize = min(max((int)$this->setup['matrixPointSize'], 1), 10);

		QRcode::png($this->setup['content'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);
	}

	/**
	 * Calculates the GIFBUILDER output filename/path based on a serialized, hashed value of this->setup
	 *
	 * @param	string		Filename prefix, eg. "GB_"
	 * @return	string		The relative filepath (relative to PATH_site)
	 * @access private
	 */
	protected function fileName($pre)  {
		$meaningfulPrefix = '';

		if ($GLOBALS['TSFE']->config['config']['meaningfulTempFilePrefix']) {
			/** @var $basicFileFunctions t3lib_basicFileFunctions */
			$basicFileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');

			$meaningfulPrefix = implode('_', $this->setup['data']);
			$meaningfulPrefix = $basicFileFunctions->cleanFileName($meaningfulPrefix);
			$meaningfulPrefix = substr($meaningfulPrefix, 0, intval($GLOBALS['TSFE']->config['config']['meaningfulTempFilePrefix'])) . '_';
		}

		return $this->tempPath .
				$pre .
				$meaningfulPrefix .
				t3lib_div::shortMD5(serialize($this->setup)) .
				'.png';
	}

	/**
	 * Creates subdirectory in typo3temp/ if not already found.
	 *
	 * @param string Name of sub directory
	 * @return boolean Result of t3lib_div::mkdir(), TRUE if it went well.
	 */
	protected function createTempSubDir($dirName) {

			// Checking if the this->tempPath is already prefixed with PATH_site and if not, prefix it with that constant.
		if (t3lib_div::isFirstPartOfStr($this->tempPath, PATH_site)) {
			$tmpPath = $this->tempPath;
		} else {
			$tmpPath = PATH_site . $this->tempPath;
		}

			// Making the temporary filename:
		if (!@is_dir($tmpPath . $dirName)) {
			return t3lib_div::mkdir($tmpPath . $dirName);
		}
	}

	/**
	 * Initiates the image file generation if ->setup is TRUE and if the file did not exist already.
	 * Gets filename from fileName() and if file exists in typo3temp/ dir it will - of course - not be rendered again.
	 * Otherwise rendering means calling ->make(), then ->output(), then ->destroy()
	 *
	 * @return string The filename for the created GIF/PNG file. The filename will be prefixed "GB_"
	 */
	public function gifBuild() {
		if ($this->setup) {
			$gifFileName = $this->fileName('GB/');	// Relative to PATH_site
			if (!file_exists($gifFileName))	{		// File exists

					// Create temporary directory if not done:
				$this->createTempSubDir('GB/');

					// Create file:
				$this->make($gifFileName);
			}
			return $gifFileName;
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/Services/QR.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/Services/QR.php']);
}

?>