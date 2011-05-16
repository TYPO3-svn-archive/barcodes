<?php
$extensionPath = t3lib_extMgm::extPath('barcodes');
return array(
	'tx_barcodes_services_ean13' => $extensionPath . 'Classes/Services/EAN13.php',
	'tx_barcodes_services_qr' => $extensionPath . 'Classes/Services/QR.php',
);
?>