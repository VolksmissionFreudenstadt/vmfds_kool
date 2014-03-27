<?php

namespace VMFDS\VmfdsKool\Controller;

// override autoload:
require_once(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:vmfds_kool/Classes/Domain/Repository/KoolUserRepository.php'));
require_once(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:vmfds_kool/Classes/Domain/Repository/KoolGroupRepository.php'));

class UserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	protected $koolUserRepository;
	protected $koolGroupRepository;
	
	
	/**
	* frontendUserRepository
	*
	* @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
	* @inject
	*/
	protected $frontendUserRepository;

	/**
	* frontendUserGroupRepository
	*
	* @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	* @inject
	*/
	protected $frontendUserGroupRepository;
	
		
	public function __construct() {
		parent::__construct();
		$this->koolUserRepository = new \VMFDS\VmfdsKool\Domain\Repository\KoolUserRepository();
		$this->koolGroupRepository = new \VMFDS\VmfdsKool\Domain\Repository\KoolGroupRepository();
	}
		
	public function helloAction() {
	}
	
	/**
	* pick Action
	* 
	* The user has submitted his first and last name
	* and should now be able to pick a corresponding
	* person from the kOOL database
	*/
	public function pickAction() {
		// find all kOOL people matching this description:
		$req = $this->request->getArguments();
		$people = $this->koolUserRepository->findByName($req['first_name'], $req['last_name']);
		
		$this->view->assign('request', $req);
		$this->view->assign('people', $people);
	}
	
	/**
	* identify Action
	* 
	* The user has picked a person from kOOL.
	* Now we either send him an email with a confirm link
	* or we ask for the birthdate to confirm (if there is no email address).
	*/
	public function identifyAction() {
		$req = $this->request->getArguments();
		$person = $this->koolUserRepository->findByUid($req['id']);
		if ($person['geburtsdatum']=='0000-00-00') $person['geburtsdatum']='';
		
		// check if we have an email address
		$email = ($person['email'] ? $person['email'] : $person['email_g']);
		if ($email) {
			$this->sendTemplateEmail(array($email => $person['vorname'].' '.$person['nachname']),
									 array('freudenstadt@volksmission.de' => 'Volksmission Freudenstadt'),
									 'Bitte bestätige deine Anmeldung',
									 'RequestConfirmation',
									 array('person' => $person, 'saltedcode' => $this->getSalt($person)) 
									);
		}
		
		$this->view->assign('person', $person);
		$this->view->assign('email', $email);		
	}

	public function confirmByDOBAction() {
		$req = $this->request->getArguments();
		$person = $this->koolUserRepository->findByUid($req['id']);
		
		$tmp = explode('.', $req['dob']);
		$tmp2 = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
		if ($person['geburtsdatum']==$tmp2) {
			$code = $this->getSalt($person);
			$this->redirect('choosePassword', NULL, NULL, array('id' => $req['id'], 'code' => $code));
		}
	}
		
	public function confirmByMailAction() {
		$req = $this->request->getArguments();
		$person = $this->koolUserRepository->findByUid($req['id']);
		
		$this->view->assign('person', $person);
		
		if ($this->checkSalt($req['code'], $person)) {
			$this->redirect('choosePassword', NULL, NULL, array('id' => $req['id'], 'code' => $req['code']));		
		}
	}
	
	protected function makeUserName($person) {
		$suffix = '';
		$tmp = $this->koolUserRepository->findByName($person['vorname'], $person['nachname']);
		if (count($tmp)>1) {
			$ct = 0;
			foreach ($tmp as $p) {
				$ct++;
				if ($p==$person) {
					$suffix = $ct;
				}
			}
		}
		
		$person['username'] = strtolower($person['vorname'].'.'.$person['nachname']).$suffix; 
		return $person;		
	}
	
	protected function getPersonChecked($req = NULL) {
		$req = $req ? $req : $this->request->getArguments();
		
		if ($req['id']) {
			$person = $this->koolUserRepository->findByUid($req['id']);
		} else {
			$this->redirect('hello');	
		} 

		if ($this->checkSalt($req['code'], $person)) {
			return $person;
		} else {
			$this->redirect('hello');
		}
	}
	
	public function choosePasswordAction() {
		$req = $this->request->getArguments();
		$person = $this->getPersonChecked($req);
		$person = $this->makeUserName($person);
		$this->view->assign('person', $person);
		$this->view->assign('code', $req['code']);
	}
	
	public function createUserAction() {
		$req = $this->request->getArguments();
		$person = $this->getPersonChecked($req);
		$person = $this->makeUserName($person);
		
		$personGroups = $this->koolGroupRepository->findByPerson($person, 'vmfds_kool_typo3integration_usergroup', '(vmfds_kool_typo3integration_usergroup>0)');
		$userGroups = array();
		foreach ($personGroups as $g) {
			$userGroups[] = $g['vmfds_kool_typo3integration_usergroup'];
		}	
		
		$user = new \TYPO3\CMS\Extbase\Domain\Model\FrontendUser();
		$user->setFirstName($person['vorname']);
		$user->setLastName($person['nachname']);
		$user->setName($person['vorname'].' '.$person['nachname']);
		$user->setUsername($person['username']);
		$user->setPassword($req['password']);
		
		// add default groups specified by TS
		$defaultGroups = explode(',',$this->settings['defaultGroups']);
		foreach ($defaultGroups as $gid) {
			$ug = $this->frontendUserGroupRepository->findByUid($gid);
			$user->addUsergroup($ug);
		}
		
		// add groups defined in kOOL, if not already set
		foreach ($userGroups as $gid) {
			if (!in_array($gid, $defaultGroups)) {
				$ug = $this->frontendUserGroupRepository->findByUid($gid);
				$user->addUsergroup($ug);
			}
		}
		
		// add new user to typo3
		$this->frontendUserRepository->add($user);
		
		// link the accounts
		$this->koolUserRepository->linkAccounts($person, $person['username']);
	}
	
	public function myAccountAction() {
		$fe_user = $GLOBALS['TSFE']->fe_user->user['username'];
		$person = $this->koolUserRepository->findByUsername($fe_user);
		
		$this->view->assign('person', $person);
	}

	/**
	* Send an email from a fluid template
	*
	* @param array $recipient recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
	* @param array $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
	* @param string $subject subject of the email
	* @param string $templateName template name (UpperCamelCase)
	* @param array $variables variables to be passed to the Fluid view
	* @return boolean TRUE on success, otherwise false
	*/
	protected function sendTemplateEmail(array $recipient, array $sender, $subject, $templateName, array $variables = array()) {
		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		
		// set correct extension name		
		$emailView->getRequest()->setControllerExtensionName($this->request->getControllerExtensionName());

		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		//die ($templateRootPath);
		$templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.html';
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		$emailView->assignMultiple($variables);
		$emailBody = $emailView->render();
	
		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$message->setTo($recipient)
			  ->setFrom($sender)
			  ->setSubject($subject);
	
		// Possible attachments here
		//foreach ($attachments as $attachment) {
		//	$message->attach($attachment);
		//}
	
		// Plain text example
		$message->setBody($emailBody, 'text/plain');
	
		// HTML Email
		#$message->setBody($emailBody, 'text/html');
	
		$message->send();
		return $message->isSent();
	}
	
	protected function getSalt($s) {
		return md5($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'].md5($s));
	}
	
	protected function checkSalt($s, $object) {
		return ($s==$this->getSalt($object));
	}
}
	
?>