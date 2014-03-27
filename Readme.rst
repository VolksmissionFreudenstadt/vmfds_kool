Extension Manual
=================

Install:
* Install as a normal typo3 extension
* Add the typo3_feuser field (VARCHAR(255) NULL) to ko_leute.
* Configure the kOOL database connection through typo3conf/LocalConfiguration.php by adding a new section to the configuration: 'kOOL' => array('db' => 'yourdbname')


Limitations:
* The databases for kOOL and typo3 need to be on the same MySQL server and accessible to the same user.



(c) Volksmission Freudenstadt, Christoph Fischer <christoph.fischer@volksmission.de>
