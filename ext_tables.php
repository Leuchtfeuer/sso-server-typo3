<?php
defined('TYPO3_MODE') or die('Access denied.');

call_user_func(
    function ($extensionKey) {

        // Register Backend Module
        $controllerActions = [
            \Bitmotion\SingleSignon\Controller\BackendController::class => 'info',
            \Bitmotion\SingleSignon\Controller\MappingTableController::class => 'list,edit,new,delete,selectFolder,createForm,update',
        ];
        $extensionName = $extensionKey;
        if (version_compare(TYPO3_version, '10.0.0', '<')) {
            $controllerActions = [
                'Backend' => 'info',
                'MappingTable' => 'list,edit,new,delete,selectFolder,createForm,update',
            ];
            $extensionName = 'Bitmotion.' . ucfirst($extensionKey);
        }
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            $extensionName,
            'tools',
            'txsinglesignonM1',
            'bottom',
            $controllerActions,
            [
                'access' => 'admin',
                'icon' => 'EXT:single_signon/Resources/Public/Icons/ce_wiz.gif',
                'labels' => 'LLL:EXT:single_signon/Resources/Private/Language/Module/locallang_mod.xlf'
            ]
        );
    }, 'single_signon'
);
