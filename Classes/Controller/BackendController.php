<?php

namespace Bitmotion\SingleSignon\Controller;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/*
 * This file is part of the "Single Signon" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Yassine Abid <yassine.abid@leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 */

class BackendController extends ActionController
{
    /** @var BackendTemplateView */
    protected $view;

    protected $defaultViewObjectName = BackendTemplateView::class;

    public function __construct()
    {
        if (version_compare(TYPO3_version, '10.0.0', '<')) {
            parent::__construct();
        }
    }

    public function infoAction(): void
    {
        // Just an empty view
    }

    public function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);
    }

    protected function addButton(string $label, string $actionName, string $controllerName, string $icon): void
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();

        $linkButton = $buttonBar->makeLinkButton()
            ->setTitle($this->getTranslation($label))
            ->setHref($this->getUriBuilder()->reset()->uriFor($actionName, [], $controllerName))
            ->setIcon($this->view->getModuleTemplate()->getIconFactory()->getIcon($icon, Icon::SIZE_SMALL));

        $buttonBar->addButton($linkButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    protected function getUriBuilder(): UriBuilder
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        return $uriBuilder;
    }

    protected function getTranslation($key): string
    {
        return $this->getLanguageService()->sL('LLL:EXT:single_signon/Resources/Private/Language/Module/locallang_mod.xlf:' . $key);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
