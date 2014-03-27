<?php
	
	if(!defined('TYPO3_MODE')) die ('Access denied.');
	
	Tx_Extbase_Utility_Extension::configurePlugin(
		'VMFDS.'.$_EXTKEY,
		'registration',
		array(
			'User' => 'hello,pick,identify,confirmByMail,confirmByDOB,choosePassword,createUser'
		),
		array()
	);

	
	Tx_Extbase_Utility_Extension::configurePlugin(
		'VMFDS.'.$_EXTKEY,
		'myaccount',
		array(
			'User' => 'myAccount'
		),
		array()
	);
			
?>