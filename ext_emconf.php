<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "single_signon".
 *
 * Auto generated 08-09-2015 17:44
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Single Sign-On',
    'description' => 'The TYPO3 SSO Server provides seamless integration of third-party (i.e. non-TYPO3) applications (SSO Apps) into TYPO3. This includes end-user access to SSO Apps with no additional logon.',
    'category' => 'plugin',
    'version' => '3.0.2',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Bitmotion GmbH',
    'author_email' => 'typo3-ext@bitmotion.de',
    'author_company' => '',
    'constraints' => array(
        'depends' => array(
            'php' => '5.2.0-7.0.99',
            'typo3' => '6.2.0-7.99.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
    'autoload' => array(
        'psr-4' => array(
            'Bitmotion\\SingleSignon\\' => 'Classes',
        ),
    )
);
