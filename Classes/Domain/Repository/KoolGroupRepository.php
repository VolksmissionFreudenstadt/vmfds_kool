<?php

namespace VMFDS\VmfdsKool\Domain\Repository;

// override autoload:
require_once(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:vmfds_kool/Classes/Connectors/KoolConnector.php'));

class KoolGroupRepository {
	// kool connector
	protected $kool; 
	
			
	public function __construct() {
		$this->kool = new \TYPO3\VmfdsKool\Connectors\koolConnector;		
	}
	
	public function findByQuery($sql) {
		return $this->kool->query($sql);
	}
	
	public function findByPerson($person, $columns='*', $addWhere='') {
		$groups = explode(',', $person['groups']);
		$gids = array();
		foreach ($groups as $g) {
			$parts = explode(':', $g);
			if (substr($parts[count($parts)-1], 0, 1)=='r') $idx = count($parts)-2; else $idx = count($parts)-1;
			$gids[] = substr($parts[$idx], 1);  
		}
		sort($gids);
		 
		$sql = 'SELECT '.$columns.' FROM ko_groups WHERE (id IN ('.join(',', $gids).')) '.($addWhere ? ' AND '.$addWhere : '').';';
		$res = $this->findByQuery($sql);
		
		return $res;
	}
}
