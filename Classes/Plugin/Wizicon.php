<?php

namespace Bitmotion\SingleSignon\Plugin;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
/***************************************************************
*  Copyright notice
*
*  (c) 2003 Dietrich Heise (typo3-ext@bitmotion.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use TYPO3\CMS\Core\Utility\PathUtility;

/**
* Class that adds the wizard icon.
*
* @author Dietrich Heise <typo3-ext@bitmotion.de>
*/
class Wizicon
{
    /**
     * @param array $wizardItems
     * @return array
     */
    public function proc($wizardItems)
    {
        $labelArray = $this->includeLocalLang();
        $wizardItems['plugins_tx_singlesignon_pi1'] = [
        'icon' => PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath('single_signon')) . 'Resources/Public/Icons/ce_wiz.gif',
            'title' => $this->getLanguageService()->getLLL('pi1_title', $labelArray),
            'description' => $this->getLanguageService()->getLLL('pi1_plus_wiz_description', $labelArray),
            'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=single_signon_pi1' ];

        return $wizardItems;
    }

    /**
    * This will return the labels array array of this extension
    *
    * @return array  the language translation for this extension
    */
    protected function includeLocalLang()
    {
        return $this->getLanguageService()->includeLLFile('EXT:single_signon/Resources/Private/Language/locallang_tca.xml', false);
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
