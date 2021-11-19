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
    'version' => '4.0.0',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Leuchtfeuer Digital Marketing GmbH',
    'author_email' => 'team-yd@leuchtfeuer.com',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => array(
        'psr-4' => array(
            'Bitmotion\\SingleSignon\\' => 'Classes',
        ),
    )
);
