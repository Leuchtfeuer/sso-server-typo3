<?php
namespace Bitmotion\SingleSignon\Module;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005  (Dietrich Heise <typo3-ext@bitmotion.de>)
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

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Module 'mapping' for the 'usermapping' extension.
 * The Main Class of this extension to Display the BE
 * @author  Dietrich Heise <typo3-ext@bitmotion.de>
 */
class ModuleController extends BaseScriptClass {

	/**
	 * @var array
	 */
	protected $pageinfo;

	/**
	 * @var string
	 */
	protected $table_properties = 'tx_singlesignon_properties';

	/**
	 * @var string
	 */
	protected $table_usermap = 'tx_singlesignon_usermap';

	/**
	 * @var array
	 */
	protected $extConf = array();

	/**
	 * @var array
	 */
	protected $userlist = array();

	public function __construct() {
		// This checks permissions and exits if the users has no permission for entry.
		$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], 1);

		$this->doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['single_signon']);
		$this->getLanguageService()->includeLLFile('EXT:single_signon/Resources/Private/Language/Module/locallang.xml');
	}

	/**
	 * Adds items to the->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return void
	 */
	function menuConfig() {
		$this->MOD_MENU = array('function' => array(
				'1' => $this->getLanguageService()->getLL('function1'), //Info
				'6' => $this->getLanguageService()->getLL('function6'), //Create a new Mapping
				'2' => $this->getLanguageService()->getLL('function2'), //Edit a mapping Table
				'4' => $this->getLanguageService()->getLL('function4'), //Delete a mapping Table
				'5' => $this->getLanguageService()->getLL('function5'), //Copy a mapping Table
		));
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return void
	 */
	function main() {
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($this->getBackendUserAuthentication()->user['admin'] && !$this->id)) {
			// Insert the Banner
			$this->doc->form = '<a href="http://www.single-signon.com" target="_blank" title="www.single-signon.com"><span class="banner"></span></a><form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '" method="POST">';
			// JavaScript
			$scriptRelPath = ExtensionManagementUtility::extRelPath('single_signon');
			$this->doc->JScode = '
					<link rel="stylesheet" type="text/css" href="'. htmlspecialchars($scriptRelPath) . 'Resources/Public/Css/single-signon.css" />
					<script type="text/javascript" language="javascript">
					script_ended = 0; function jumpToUrl(URL) { document.location = URL; }
					</script>
					';
			$this->doc->postCode = '
					<script type="text/javascript" language="javascript">
					script_ended = 1;
					if (top.theMenu) top.theMenu.recentuid = ' . intval($this->id) . ';
					</script>
					';
			$this->content .= $this->doc->startPage($this->getLanguageService()->getLL('title'));
			$this->content .= $this->doc->header($this->getLanguageService()->getLL('title'));
			$this->content .= $this->doc->section('', $this->doc->funcMenu('', BackendUtility::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));

			// Render content:
			$this->moduleContent();
		} else {
			// If no access or if ID == zero
			$this->content .= $this->doc->startPage($this->getLanguageService()->getLL('title'));
			$this->content .= $this->doc->header($this->getLanguageService()->getLL('title'));
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return string  Print out the HTML Code.
	 */
	function printContent() {
		$this->content .= $this->doc->divider(10);
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return void
	 */
	function moduleContent() {
		switch ((string)$this->MOD_SETTINGS['function']) {
			case 1:
				// Print Info Page
				$this->content .= $this->doc->section($this->getLanguageService()->getLL('infoTitle'), $this->getLanguageService()->getLL('infoText'), 0, 1);
				break;
			case 6:
				// Create new Mapping
				$this->content .= $this->doc->section($this->getLanguageService()->getLL('function6'), '', 0, 1);
				if (GeneralUtility::_GP('sysfolder_id') && GeneralUtility::_GP('saveit')) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section($this->getLanguageService()->getLL('savedTitle'), $this->getLanguageService()->getLL('savedText'), 1, 1);
				} elseif (GeneralUtility::_GP('sysfolder_id')) {
					$this->editMappingTable();
				} else {
					$this->selectUserFolder();
				}
				break;
			case 2:
				// Edit Mapping
				$this->content .= $this->doc->section($this->getLanguageService()->getLL('function2'), '', 0, 1);
				if (GeneralUtility::_GP('sysfolder_id') && GeneralUtility::_GP('mapping_id') && GeneralUtility::_GP('saveit')) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section($this->getLanguageService()->getLL('savedTitle'), $this->getLanguageService()->getLL('editText'), 1, 1);
					$this->editMappingTable();
				} elseif (GeneralUtility::_GP('sysfolder_id') && GeneralUtility::_GP('mapping_id')) {
					$this->editMappingTable();
				} else {
					$this->selectMappingTable();
				}
				break;
			case 4:
				// Delete Mapping
				$this->content .= $this->doc->section($this->getLanguageService()->getLL('function4'), '', 0, 1);
				if (GeneralUtility::_GP('mapping_id')) {
					$this->deleteMappingTable();
				} else {
					$this->selectMappingTable();
				}
				break;
			case 5:
				// Copy Mapping
				$this->content .= $this->doc->section($this->getLanguageService()->getLL('function5'), '', 0, 1);
				if (GeneralUtility::_GP('sysfolder_id') && GeneralUtility::_GP('saveit')) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section('Saved', 'Done.', 0, 1);
				} elseif (GeneralUtility::_GP('mapping_id')) {
					$this->copyMappingTable();
				} else {
					$this->selectMappingTable();
				}
				break;
		}
	}

	/**
	 * Returns the HTML Code for the form field of the Mapping Table in the BE in $this->content
	 *
	 * @return void
	 */
	function copyMappingTable() {
		$old_mapping_id = (int)GeneralUtility::_GP('mapping_id');
		$mapping_id = 0;
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_properties, 'uid=' . (int)$old_mapping_id);
		$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
		$sysfolder_id = intval($row['sysfolder_id']);

		$this->content .= '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '">' . chr(10);
		$this->content .= '<input type="hidden" name="sysfolder_id" value="' . (int)$sysfolder_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="mapping_id" value="' . (int)$mapping_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="saveit" value="true">' . chr(10);

		# Table Properties
		$this->editTableProperties($mapping_id);

		# User Mapping List
		$this->userlist = $this->mapUserlist($sysfolder_id, $old_mapping_id);

		# Fill the Userlist form
		foreach ($this->userlist as $id => $name) {
			$this->content .= '<input name="fe_uid"' . (int)$id . '" type="hidden" value="' . htmlspecialchars($name) . '" size="30">' . chr(10);
		}
		$this->content .= '<table><tr><td><form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '">' . chr(10);
		$this->content .= '<input type="hidden" name="sysfolder_id" value="' . (int)$sysfolder_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="mapping_id" value="' . (int)$mapping_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="deleteit" value="true">' . chr(10);
		$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('submit')) . '"></form></td><td>' . chr(10);
		$this->content .= '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '" method="POST">' . chr(10);
		$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('cancel')) . '">' . chr(10);
		$this->content .= '</td></tr></table>';
	}

	/**
	 * Return the HTML Code for form in $this->content
	 *
	 * @return void
	 */
	function deleteMappingTable() {
		$mapping_id = intval(GeneralUtility::_GP('mapping_id'));
		if (GeneralUtility::_GP('deleteit')) {
			$this->getDatabaseConnection()->exec_DELETEquery($this->table_properties, 'uid=' . (int)$mapping_id);
			$this->getDatabaseConnection()->exec_DELETEquery($this->table_usermap, 'mapping_id=' . (int)$mapping_id);
			$this->content .= $this->doc->section($this->getLanguageService()->getLL('deleteTitle'), htmlspecialchars($this->getLanguageService()->getLL('deleteText')), 1, 1);
		} else {
			$this->editTableProperties($mapping_id, 1);
			$this->content .= $this->doc->section('Are you Sure?', '', 0, 1);

			$this->content .= '<table><tr><td><form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '">' . chr(10);
			$this->content .= '<input type="hidden" name="mapping_id" value="' . (int)$mapping_id . '">' . chr(10);
			$this->content .= '<input type="hidden" name="deleteit" value="true">' . chr(10);
			$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('submit')) . '"></form></td><td>' . chr(10);
			$this->content .= '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '" method="POST">' . chr(10);
			$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('cancel')) . '">' . chr(10);
			$this->content .= '</form></td></tr></table>';
		}
	}

	/**
	 * Saves the Mapping Table (from GeneralUtility::_GP('offset')
	 * to GeneralUtility::_GP('offset')+$this->extConf['maxUsersPerPage']
	 * Only the actualy shown users will be saved
	 *
	 * @return void
	 */
	function saveMappingTable() {
		$mapping_id = (int)GeneralUtility::_GP('mapping_id');
		$sysfolder_id = (int)GeneralUtility::_GP('sysfolder_id');
		$mapping_tablename = GeneralUtility::_GP('mapping_tablename', $this->table_properties);
		$mapping_defaultmapping = GeneralUtility::_GP('mapping_defaultmapping');
		$offset = (int)GeneralUtility::_GP('offset');
		$maxUsersPerPage = $this->extConf['maxUsersPerPage'];

		$allowall = intval((bool)GeneralUtility::_GP('allowall'));

		// Save Table Properties (name and default mapping)
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_properties, 'uid=' . (int)$mapping_id);
		$numrows = $this->getDatabaseConnection()->sql_num_rows($result);
		if ($numrows == 1) {
			$values = array(
				'mapping_tablename' => $mapping_tablename,
				'mapping_defaultmapping' => $mapping_defaultmapping,
				'allowall' => $allowall
			);
			$this->getDatabaseConnection()->exec_UPDATEquery($this->table_properties, 'uid=' . (int)$mapping_id, $values);
		} else {
			$values = array(
				'sysfolder_id' => $sysfolder_id,
				'mapping_tablename' => $mapping_tablename,
				'mapping_defaultmapping' => $mapping_defaultmapping,
				'allowall' => $allowall
			);
			$this->getDatabaseConnection()->exec_INSERTquery($this->table_properties, $values);
			$mapping_id = $this->getDatabaseConnection()->sql_insert_id();
		}

		// Save User Mappings (from $offset to $offset+$maxUsersPerPage)
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'fe_users', 'pid=' . (int)$sysfolder_id . ' AND deleted =0');
		$menge = $this->getDatabaseConnection()->sql_num_rows($result);
		for ($i = 0; $i < $menge; $i++) {
			$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
			if (($i >= $offset) && ($i < $offset + $maxUsersPerPage)) {
				$result2 = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_usermap, 'mapping_id=' . (int)$mapping_id . ' AND fe_uid=' . (int)$row['uid']);
				$feuid = 'fe_uid' . $row['uid'];
				$username = GeneralUtility::_GP($feuid);
				if ($this->getDatabaseConnection()->sql_num_rows($result2) == 1) {
					// Update DB
					$values = array('mapping_username' => $username,);
					$this->getDatabaseConnection()->exec_UPDATEquery($this->table_usermap, 'mapping_id=' . (int)$mapping_id . ' AND fe_uid=' . (int)$row['uid'], $values);
				} else {
					// Insert in DB
					$values = array(
						'mapping_id' => $mapping_id,
						'fe_uid' => $row['uid'],
						'mapping_username' => $username
					);
					$this->getDatabaseConnection()->exec_INSERTquery($this->table_usermap, $values);
				}
			}
		}
	}

	/**
	 * return the form for editMappingTable in $this->content
	 *
	 * @return void
	 */
	function editMappingTable() {
		$sysfolder_id = (int)GeneralUtility::_GP('sysfolder_id');
		$mapping_id = (int)GeneralUtility::_GP('mapping_id');
		$offset = (int)GeneralUtility::_GP('offset');
		if (!isset($mapping_id)) {
			$mapping_id = 0;
		}
		if (!isset($sysfolder_id)) {
			$this->content .= $this->doc->section('Error: No Sysfolder id', '', 0, 1);
			return;
		}
		$this->content .= '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '">' . chr(10);
		$this->content .= '<input type="hidden" name="sysfolder_id" value="' . (int)$sysfolder_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="mapping_id" value="' . (int)$mapping_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="saveit" value="true">' . chr(10);
		$this->content .= '<input type="hidden" name="offset" value="' . (int)$offset . '">' . chr(10);

		# Table Properties
		$this->editTableProperties($mapping_id);

		# User Mapping List: [uid] => [mappingname]
		$this->userlist = $this->mapUserlist($sysfolder_id, $mapping_id);
		$numberofusers = count($this->userlist);

		# Fill the Userlist form (more pages)
		$maxUsersPerPage = $this->extConf['maxUsersPerPage'];

		$this->content .= '<h3>';
		$this->content .= $this->generate_pagination(BackendUtility::getModuleUrl('tools_txsinglesignonM1', array('sysfolder_id' => $sysfolder_id, 'mapping_id' => $mapping_id)), $numberofusers, $maxUsersPerPage, $offset);
		$this->content .= '</h3>';

		$this->content .= '<table>' . chr(10);

		$tmp_num = 0;
		foreach ($this->userlist as $id => $name) {
			if (($tmp_num >= $offset) && ($tmp_num < $offset + $maxUsersPerPage)) {
				$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'fe_users', 'pid=' . $sysfolder_id . ' AND uid=' . $id . ' AND deleted=\'0\'');
				$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
				$this->content .= '<tr>' . chr(10);
				$this->content .= '<td class="td1">' . htmlspecialchars($this->getLanguageService()->getLL('username')) . '</td><td class="td2">' . htmlspecialchars($row['username']) . '</td>' . chr(10);
				$this->content .= '<td class="td1">' . htmlspecialchars($this->getLanguageService()->getLL('realname')) . '</td><td class="td2">' . htmlspecialchars($row['name']) . '</td>' . chr(10);
				$this->content .= '<td class="td1">' . htmlspecialchars($this->getLanguageService()->getLL('mapname')) . '</td><td class="td2"><input name="fe_uid' . (int)$id . '" type="text" value="' . htmlspecialchars($name) . '" size="30"></td></tr>' . chr(10);
			}
			$tmp_num++;
		}
		$this->content .= '</table>' . chr(10);
		$this->content .= '<table><tr><td><form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '">' . chr(10);
		$this->content .= '<input type="hidden" name="sysfolder_id" value="' . (int)$sysfolder_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="mapping_id" value="' . (int)$mapping_id . '">' . chr(10);
		$this->content .= '<input type="hidden" name="deleteit" value="true">' . chr(10);
		$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('submit')) . '"></form></td><td>' . chr(10);
		$this->content .= '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1')) . '" method="POST">' . chr(10);
		$this->content .= '<input type="submit" value="' . htmlspecialchars($this->getLanguageService()->getLL('cancel')) . '">' . chr(10);
		$this->content .= '</form></td></tr></table>';
	}

	/**
	 * Pagination routine, generates
	 * page number sequence for the Usermapping,
	 * (function copied from the phpBB http://www.phpBB.com)
	 *
	 * @return string  Pagination (html code for: Goto page back, 1, 2, 3, ... 5, 6, 7, ..., 12, 13, 14 next)
	 */
	function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE) {
		$total_pages = ceil($num_items / $per_page);
		if ($total_pages == 1) {
			return '';
		}
		$on_page = floor($start_item / $per_page) + 1;
		$page_string = '';
		if ($total_pages > 10) {
			$init_page_max = ($total_pages > 3) ? 3 : $total_pages;
			for ($i = 1; $i < $init_page_max + 1; $i++) {
				$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . htmlspecialchars($base_url . "&offset=" . (($i - 1) * $per_page)) . '">' . $i . '</a>';
				if ($i < $init_page_max) {
					$page_string .= ", ";
				}
			}
			if ($total_pages > 3) {
				if ($on_page > 1 && $on_page < $total_pages) {
					$page_string .= ($on_page > 5) ? ' ... ' : ', ';
					$init_page_min = ($on_page > 4) ? $on_page : 5;
					$init_page_max = ($on_page < $total_pages - 4) ? $on_page : $total_pages - 4;
					for ($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
						$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . htmlspecialchars($base_url . "&offset=" . (($i - 1) * $per_page) ). '">' . $i . '</a>';
						if ($i < $init_page_max + 1) {
							$page_string .= ', ';
						}
					}
					$page_string .= ($on_page < $total_pages - 4) ? ' ... ' : ', ';
				} else {
					$page_string .= ' ... ';
				}
				for ($i = $total_pages - 2; $i < $total_pages + 1; $i++) {
					$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . htmlspecialchars($base_url . "&offset=" . (($i - 1) * $per_page)) . '">' . $i . '</a>';
					if ($i < $total_pages) {
						$page_string .= ", ";
					}
				}
			}
		} else {
			for ($i = 1; $i < $total_pages + 1; $i++) {
				$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . htmlspecialchars($base_url . "&offset=" . (($i - 1) * $per_page)) . '">' . $i . '</a>';
				if ($i < $total_pages) {
					$page_string .= ', ';
				}
			}
		}
		if ($add_prevnext_text) {
			if ($on_page > 1) {
				$page_string = ' <a href="' . htmlspecialchars($base_url . "&offset=" . (($on_page - 2) * $per_page)) . '">' . htmlspecialchars($this->getLanguageService()->getLL('goBack')) . '</a>&nbsp;&nbsp;' . $page_string;
			}
			if ($on_page < $total_pages) {
				$page_string .= '&nbsp;&nbsp;<a href="' . htmlspecialchars($base_url . "&offset=" . ($on_page * $per_page)) . '">' . htmlspecialchars($this->getLanguageService()->getLL('goForward')) . '</a>';
			}
		}
		$page_string = htmlspecialchars($this->getLanguageService()->getLL('gotoPage')) . ' ' . $page_string;
		return $page_string;
	}

	/**
	 * return the form for selectUserFolder() in $this->content
	 * This will print out a clickable list of all Sysfolders
	 *
	 * @return void
	 */
	function selectUserFolder() {
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'pages', 'doktype=\'254\' AND deleted=\'0\'');
		$menge = $this->getDatabaseConnection()->sql_num_rows($result);
		$content1 = '<table>';
		for ($i = 0; $i < $menge; $i++) {
			$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
			$ahref = '<a href="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1', array('sysfolder_id' => $row['uid']))) . '" class="link1">' . chr(10);
			$result1 = $this->getDatabaseConnection()->exec_SELECTquery('*', 'fe_users', 'pid=' . (int)$row['uid'] . ' AND deleted=\'0\'');
			$num = $this->getDatabaseConnection()->sql_num_rows($result1);
			$content1 .= '<tr>' . chr(10);
			$content1 .= '<td class="td1">' . $ahref . htmlspecialchars($this->getLanguageService()->getLL('foldername')) . '</a></td><td class="td2">' . $ahref . htmlspecialchars($row['title']) . '</a></td>' . chr(10);
			$content1 .= '<td class="td1">' . $ahref . htmlspecialchars($this->getLanguageService()->getLL('uid')) . '</a></td><td class="td2">' . $ahref . (int)$row['uid'] . '</a></td>' . chr(10);
			$content1 .= '<td class="td1">' . $ahref . htmlspecialchars($this->getLanguageService()->getLL('nrUsers')) . '</a></td><td class="td2">' . $ahref . (int)$num . '</a></td></tr>' . chr(10);
		}
		$content1 .= '</table>';
		$this->getDatabaseConnection()->sql_free_result($result);
		$this->content .= $this->doc->section($this->getLanguageService()->getLL('selectSysfolder'), $content1, 1, 1);
	}

	/**
	 * return the form for selectMappingTable() in $this->content
	 *
	 * @return void
	 */
	function selectMappingTable() {
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_properties, '1');
		$menge = $this->getDatabaseConnection()->sql_num_rows($result);
		$content1 = '<table>';
		for ($i = 0; $i < $menge; $i++) {
			$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
			$ahref = '<a href="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txsinglesignonM1', array('sysfolder_id' => $row['sysfolder_id'], 'mapping_id' => $row['uid']))) . '" class="link1">' . chr(10);
			$content1 .= '<tr class="box">' . chr(10);
			$content1 .= '<td class="td1">' . $ahref . $this->getLanguageService()->getLL('mappingTable') . '</a></td><td class="td2">' . $ahref . htmlspecialchars($row['mapping_tablename']) . '</a></td>' . chr(10);
			$content1 .= '<td class="td1">' . $ahref . $this->getLanguageService()->getLL('sysfolderid') . '</a></td><td class="td2">' . $ahref . (int)$row['sysfolder_id'] . '</a></td>' . chr(10);
			$content1 .= '</tr>';
		}
		$content1 .= '</table>';
		$this->getDatabaseConnection()->sql_free_result($result);
		$this->content .= $this->doc->section($this->getLanguageService()->getLL('selectMappingtable'), $content1, 1, 1);
	}

	/**
	 * Return a mapping table properties (a form to edit) in $this->content for the given
	 * $mapping_id.
	 *
	 * @param integer $mapping_id : the UID for the mapping table you want to edit.
	 * @param integer $show : if this is set to 1 the function only display the forms (readonly)
	 * @return void
	 */
	function editTableProperties($mapping_id = 0, $show = 0) {
		$editable = $show ? ' READONLY' : '';
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_properties, 'uid=' . $mapping_id);
		$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);

		$content = '<table><tr><td>' . $this->getLanguageService()->getLL('enterTableName') . '</td><td>' . chr(10);
		$content .= '<input name="mapping_tablename" type="text"' . htmlspecialchars($editable) . ' value="' . htmlspecialchars($row['mapping_tablename']) . '"></td></tr>' . chr(10);
		$content .= '<tr><td>' . $this->getLanguageService()->getLL('allow') . '</td><td><input type="checkbox" name="allowall" ' . htmlspecialchars($editable);
		if ($row['allowall'] == 1) {
			$content .= ' CHECKED';
		}
		$content .= '></td></tr>';
		$content .= '<tr><td>' . $this->getLanguageService()->getLL('defaultmapping') . '</td><td>' . chr(10);
		$content .= '<input name="mapping_defaultmapping" type="text"' . htmlspecialchars($editable) . ' value="' . htmlspecialchars($row['mapping_defaultmapping']) . '"></td></tr>' . chr(10);
		$content .= '</table>';

		$mapping_id_print = $mapping_id;
		if ($mapping_id_print == 0) {
			$mapping_id_print = 'new';
		}
		if ($show == 0) {
			$title = $this->getLanguageService()->getLL('editTitle');
		} else {
			$title = $this->getLanguageService()->getLL('viewTableName');
		}
		$this->content .= $this->doc->section($title . " (ID: $mapping_id_print)", $content, 1, 1);
	}

	/**
	 * returns a user list for the given parameters
	 *
	 * @param integer $sysfolder_id : UID of the sysfolder where the fe_users are stored
	 * @param integer $mapping_id : if given the UID of the mapping table
	 * @return array  ([uid] => [mapped_username], )
	 */
	function mapUserlist($sysfolder_id, $mapping_id = '0') {
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'fe_users', 'pid=' . (int)$sysfolder_id . ' AND deleted=\'0\'');
		$menge = $this->getDatabaseConnection()->sql_num_rows($result);
		$userarray = array();
		for ($i = 0; $i < $menge; $i++) {
			$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);
			$result2 = $this->getDatabaseConnection()->exec_SELECTquery('*', $this->table_usermap, 'mapping_id=' . (int)$mapping_id . ' AND fe_uid=' . (int)$row['uid']);
			$row2 = $this->getDatabaseConnection()->sql_fetch_assoc($result2);
			$userarray[$row['uid']] = $row2['mapping_username'];
		}
		return $userarray;
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUserAuthentication() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
