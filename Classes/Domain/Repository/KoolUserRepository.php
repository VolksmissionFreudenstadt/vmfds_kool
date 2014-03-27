<?php

namespace VMFDS\VmfdsKool\Domain\Repository;

// override autoload:
require_once(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:vmfds_kool/Classes/Connectors/KoolConnector.php'));

class KoolUserRepository {
	// kool connector
	protected $kool; 
	
			
	public function __construct() {
		$this->kool = new \TYPO3\VmfdsKool\Connectors\koolConnector;		
	}
	
	public function findByQuery($sql) {
		return $this->kool->query($sql);
	}
	
	public function findByUid($uid) {
		$sql = 'SELECT * FROM ko_leute WHERE (id='.$uid.');';
		$people = $this->findByQuery($sql);
		return $people[0];
	}
	
	public function findByName($first, $last) {
		$sql = 'SELECT * FROM ko_leute WHERE (vorname=\''.$first.'\') AND (nachname=\''.$last.'\');';
		$people = $this->findByQuery($sql);
		return $people;
	}
	
	public function linkAccounts($person, $username) {
		$sql = 'UPDATE ko_leute SET typo3_feuser=\''.$username.'\' WHERE (id='.$person['id'].');';
		$this->kool->query($sql);
	}
	
	public function findByUsername($username) {
		$sql = 'SELECT * FROM ko_leute WHERE (typo3_feuser=\''.$username.'\');';
		$people = $this->findByQuery($sql);
		return $people[0];
	}
}
