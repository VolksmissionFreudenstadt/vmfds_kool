<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// add static config
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'kOOL Connector');

// add registration plugin
Tx_Extbase_Utility_Extension::registerPlugin($_EXTKEY, 'registration', 'Benutzerregistrierung aus kOOL');

Tx_Extbase_Utility_Extension::registerPlugin($_EXTKEY, 'myaccount', 'Benutzerkonto aus kOOL');

?>