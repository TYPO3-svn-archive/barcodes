<?php
$extensionPath = t3lib_extMgm::extPath('barcodes');
return array(
	'tx_barcodes_services_upc' => $extensionPath . 'Classes/Services/UPC.php',
	'tx_barcodes_services_qr' => $extensionPath . 'Classes/Services/QR.php',
);
?>