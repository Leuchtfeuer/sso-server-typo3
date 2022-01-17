<?php

$EM_CONF['single_signon'] = [
    'title' => 'Single Sign-On',
    'description' => 'The TYPO3 SSO Server provides seamless integration of third-party (i.e. non-TYPO3) applications (SSO Apps) into TYPO3. This includes end-user access to SSO Apps with no additional logon.',
    'category' => 'plugin',
    'version' => '4.0.1',
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
    'autoload' => [
        'psr-4' => [
            'Bitmotion\\SingleSignon\\' => 'Classes',
        ],
    ]
];
