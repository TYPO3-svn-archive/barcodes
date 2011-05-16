<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array(
	0 => 'BARCODE',
	1 => 'EXT:barcodes/Classes/TypoScript/Barcode.php:user_barcode',
);

?>
