<?php

namespace TYPO3\VmfdsKool\Connectors;

class koolConnector {
	private $db;
	private $connection = array();
		
		
	public function __construct() {
		$this->connection = array(
			'db' => $GLOBALS['TYPO3_CONF_VARS']['kOOL']['db'] ? $GLOBALS['TYPO3_CONF_VARS']['kOOL']['db'] : $GLOBALS['TYPO3_CONF_VARS']['DB']['database'], 
			'host' => $GLOBALS['TYPO3_CONF_VARS']['kOOL']['host'] ? $GLOBALS['TYPO3_CONF_VARS']['kOOL']['host'] : $GLOBALS['TYPO3_CONF_VARS']['DB']['host'], 
			'username' => $GLOBALS['TYPO3_CONF_VARS']['kOOL']['username'] ? $GLOBALS['TYPO3_CONF_VARS']['kOOL']['password'] : $GLOBALS['TYPO3_CONF_VARS']['DB']['username'],
			'password' => $GLOBALS['TYPO3_CONF_VARS']['kOOL']['password'] ? $GLOBALS['TYPO3_CONF_VARS']['kOOL']['password'] : $GLOBALS['TYPO3_CONF_VARS']['DB']['password'],			 
		);
	}
	
	/**
	* Query the kOOL database
	* 
	* @param string $sql The query string
	* @return mixed Array of results or false if query failed
	*/
	public function query($sql) {
		if (!$this->db) {
			$this->db = mysql_connect($this->connection['host'], $this->connection['username'], $this->connection['password']);
			mysql_query("SET NAMES 'utf8'", $this->db);
		}
		mysql_select_db($this->connection['db'], $this->db);
		$res = mysql_query($sql, $this->db);			
		mysql_select_db($GLOBALS['TYPO3_CONF_VARS']['DB']['database'], $this->db);

		if ($res) {
			while ($row = mysql_fetch_assoc($res)) $recs[] = $row;
			$result = $recs;
		} else {
			$result = FALSE;
		}
		
		$GLOBALS['TYPO3_DB']->connectDB();
		
		return $result;
	}
	
	
}
