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
 * Handles TS content object BARCODE.
 *
 * @category    TypoScript
 * @package     TYPO3
 * @subpackage  tx_barcodes
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @company     Causal Sàrl, http://causal.ch
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class user_barcode {

	/**
	 * Rendering the cObject, BARCODE
	 *
	 * @param string $name Name of the cObject ('BARCODE')
	 * @param array $conf Array of TypoScript properties
	 * @param string $TSkey TS key set to this cObject
	 * @param tslib_cObj $cObj
	 * @return string
	 */
	public function cObjGetSingleExt($name, array $conf, $TSkey, tslib_cObj $cObj) {
		switch ($conf['type']) {
			case 'UPC':
				$output = $this->getUPC($conf);
				break;
			case 'QR':
				$output = $this->getQR($conf, $cObj);
				break;
			default:
				$output = 'Invalid type "' . $conf['type'] . '"';
				break;
		}

		return $output;
	}

	/**
	 * Generates an UPC barcode.
	 *
	 * @param array $conf
	 * @return string
	 */
	protected function getUPC(array $conf) {
		/** @var $upc Tx_Barcodes_Services_UPC */
		$upc = t3lib_div::makeInstance('Tx_Barcodes_Services_UPC');

		$upc->start($conf);
		return $upc->gifBuild();
	}

	/**
	 * Generates a QR-code.
	 *
	 * @param array $conf
	 * @param tslib_cObj $cObj
	 * @return string
	 */
	protected function getQR(array $conf, tslib_cObj $cObj) {
		/** @var $upc Tx_Barcodes_Services_QR */
		$qr = t3lib_div::makeInstance('Tx_Barcodes_Services_QR', $cObj);

		$qr->start($conf);
		return $qr->gifBuild();
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/TypoScript/Barcode.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/barcodes/Classes/TypoScript/Barcode.php']);
}

?>