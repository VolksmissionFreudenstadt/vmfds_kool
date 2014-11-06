<?php
namespace TYPO3\VmfdsKool\ViewHelpers;

/**
 *
 * Example
 * {namespace kool=TYPO3\VmfdsKool\ViewHelpers}
 * <kool:birthdayList />
 *
 * @package VMFDS
 * @subpackage vmfds_kool
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class BirthdayListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Renders a birthday list from kOOL
     *
     * @param int $startDate start date of the list
     * @param int $endDate end date of the list 
  	 * @author Christoph Fischer <christoph.fischer@volksmission.de>
     */
    public function render($startDate, $endDate) {
    	$kool = new \TYPO3\VmfdsKool\Connectors\KoolConnector();
    	$o = '';
		$sql = 'SELECT vorname, nachname, geburtsdatum FROM `ko_leute` '
			  .'WHERE (DAYOFYEAR(geburtsdatum) BETWEEN '.strftime('%j', $startDate).' ' 
			  .'AND '.strftime('%j', $endDate).') '
			  .'AND FIND_IN_SET(\'g000120:r000001\', groups) '
			  .'ORDER BY DAYOFYEAR(DATE_ADD(geburtsdatum, INTERVAL (YEAR(NOW()) - YEAR(geburtsdatum)) YEAR))';
	  	$people = $kool->query($sql);
	  	if ($people) {
	  		$o = '<h3>Geburtstage</h3>';
			if (count($people)) {
				$o .= '<table border="0" width="100%" cellpadding="1" cellspacing="0"><tr><td valign="top"><table border="0" width="100%" cellpadding="1" cellspacing="0">';
				$break = ceil(count($people)/2);
				$ct = 0;
				foreach ($people as $person) {
					$o .= '<tr><td width="1.1cm" valign="top">'.strftime('%d.%m.', strtotime($person['geburtsdatum'])).'</td>'
						 .'<td valign="top">'.$person['vorname'].' '.$person['nachname'].'</td></tr>';
					
					$ct++;			
					if ($ct==$break) $o .= '</table></td><td valign="top"><table border="0" width="100%" cellpadding="1" cellspacing="0">';
				}
				$o .= '</table></td></tr></table>';
			} else {
				$o .= 'In dieser Woche hat in unserer Gemeinde niemand Geburtstag.';
			}
	  	}
    	
    	return $o;
    }
}

?>

