<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2005  (Dietrich Heise <heise@naw.de>)
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
	/**
	* Module 'mapping' for the 'usermapping' extension.
	*
	* @author  Dietrich Heise <heise@naw.de>
	*/
	 
	 
	 
	// DEFAULT initialization of a module [BEGIN]
	unset($MCONF);
	require ('conf.php');
	require ($BACK_PATH.'init.php');
	require ($BACK_PATH.'template.php');
	
	$LANG->includeLLFile('EXT:naw_single_signon/mod1/locallang.xml');
	//include ('locallang.php');
	require_once (PATH_t3lib.'class.t3lib_scbase.php');
	$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]
	 
	/**
	* The Main Class of this extenstion to Display the BE
	*/
	class tx_nawsinglesignon_module1 extends t3lib_SCbase {
		var $pageinfo;
		var $table_properties = 'tx_nawsinglesignon_properties';
		var $table_usermap = 'tx_nawsinglesignon_usermap';
		/**
		* Init function for the Backend
		*
		* @return void
		*/
		function init() {
			global $AB, $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $HTTP_GET_VARS, $HTTP_POST_VARS, $CLIENT, $TYPO3_CONF_VARS;
			parent::init();
		}
		 
		/**
		* Adds items to the->MOD_MENU array. Used for the function menu selector.
		*
		* @return void
		*/
		function menuConfig() {
			global $LANG;
			$this->MOD_MENU = Array (
			'function' => Array (
			'1' => $LANG->getLL('function1'), //Info
			'6' => $LANG->getLL('function6'), //Create a new Mapping
			'2' => $LANG->getLL('function2'), //Edit a mapping Table
			'4' => $LANG->getLL('function4'), //Delete a mapping Table
			'5' => $LANG->getLL('function5'), //Copy a mapping Table
			)
			);
			parent::menuConfig();
		}
		 
		/**
		* Main function of the module. Write the content to $this->content
		*
		* @return void
		*/
		function main() {
			global $AB, $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $HTTP_GET_VARS, $HTTP_POST_VARS, $CLIENT, $TYPO3_CONF_VARS;
			 
			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 :
			 0;
			 
			if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {
				// Draw the header.
				$this->doc = t3lib_div::makeInstance('mediumDoc');
				$this->doc->backPath = $BACK_PATH;
				// Insert the Banner
				$this->doc->form = '<a href="http://www.single-signon.com" target="_blank" title="www.single-signon.com"><span class="banner"></span></a><img src="/clear.gif" width="1" height="34" alt=""><form action="" method="POST">';
				// JavaScript
				$this->doc->JScode = '
					<link rel="stylesheet" type="text/css" href="single-signon.css" />
					<script type="text/javascript" language="javascript">
					script_ended = 0; function jumpToUrl(URL) { document.location = URL; }
					</script>
					';
				$this->doc->postCode = '
					<script type="text/javascript" language="javascript">
					script_ended = 1;
					if (top.theMenu) top.theMenu.recentuid = '.intval($this->id).';
					</script>
					';
				$this->content .= $this->doc->startPage($LANG->getLL('title'));
				$this->content .= $this->doc->header($LANG->getLL('title'));
				$this->content .= $this->style;
				$this->content .= $this->doc->spacer(5);
				$this->content .= $this->doc->section('', $this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
				 
				// Render content:
				$this->moduleContent();
				 
				$this->content .= $this->doc->spacer(10);
			} else {
				// If no access or if ID == zero
				$this->doc = t3lib_div::makeInstance('mediumDoc');
				$this->doc->backPath = $BACK_PATH;
				$this->content .= $this->doc->startPage($LANG->getLL('title'));
				$this->content .= $this->doc->header($LANG->getLL('title'));
				$this->content .= $this->doc->spacer(5);
				$this->content .= $this->doc->spacer(10);
			}
		}
		 
		/**
		* Prints out the module HTML
		*
		* @return string  Print out the HTML Code.
		*/
		function printContent() {
			global $SOBE;
			$this->content .= $this->doc->middle();
			$this->content .= $this->doc->endPage();
			echo $this->content;
		}
		 
		/**
		* Generates the module content
		*
		* @return void
		*/
		function moduleContent() {
			global $LANG;
			switch((string)$this->MOD_SETTINGS['function']) {
				case 1:
				// Print Info Page
				$this->content .= $this->doc->section($LANG->getLL('infoTitle'), $LANG->getLL('infoText'), 0, 1);
				break;
				case 6:
				// Create new Mapping
				$this->content .= $this->doc->section($LANG->getLL('function6'), '', 0, 1);
				if (t3lib_div::GPvar('sysfolder_id') && t3lib_div::GPvar('saveit') ) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section($LANG->getLL('savedTitle'), $LANG->getLL('savedText'), 1, 1);
				} elseif (t3lib_div::GPvar('sysfolder_id') ) {
					$this->editMappingTable();
				} else {
					$this->selectUserFolder();
				}
				break;
				case 2:
				// Edit Mapping
				$this->content .= $this->doc->section($LANG->getLL('function2'), '', 0, 1);
				if (t3lib_div::GPvar('sysfolder_id') && t3lib_div::GPvar('mapping_id') && t3lib_div::GPvar('saveit') ) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section($LANG->getLL('savedTitle'), $LANG->getLL('editText'), 1, 1);
					$this->editMappingTable();
				} elseif (t3lib_div::GPvar('sysfolder_id') && t3lib_div::GPvar('mapping_id') ) {
					$this->editMappingTable();
				} else {
					$this->selectMappingTable();
				}
				break;
				case 4:
				// Delete Mapping
				$this->content .= $this->doc->section($LANG->getLL('function4'), '', 0, 1);
				if (t3lib_div::GPvar('mapping_id')) {
					$this->deleteMappingTable();
				} else {
					$this->selectMappingTable();
				}
				break;
				case 5:
				// Copy Mapping
				$this->content .= $this->doc->section($LANG->getLL('function5'), '', 0, 1);
				if (t3lib_div::GPvar('sysfolder_id') && t3lib_div::GPvar('saveit')) {
					$this->saveMappingTable();
					$this->content .= $this->doc->section('Saved', 'Done.', 0, 1);
				} elseif (t3lib_div::GPvar('mapping_id')) {
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
			global $LANG;
			$old_mapping_id = t3lib_div::GPvar('mapping_id');
			$mapping_id = 0;
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_properties, 'uid='.$old_mapping_id);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			$sysfolder_id = $row['sysfolder_id'];

			$this->content .= '<form action="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'">'.chr(10);
			$this->content .= '<input type="hidden" name="sysfolder_id" value="'.$sysfolder_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="mapping_id" value="'.$mapping_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="saveit" value="true">'.chr(10);

			# Table Properties
			$this->EditTableProperties($mapping_id);

			# User Mapping List
			$this->userlist = $this->mapUserlist($sysfolder_id, $old_mapping_id);

			# Fill the Userlist form
			foreach ($this->userlist as $id => $name) {
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', 'pid='.$sysfolder_id.' AND uid='.$id.' AND deleted=\'0\'');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				$this->content .= '<input name="fe_uid"'.$id.'" type="hidden" value="'.$name.'" size="30">'.chr(10);
			}
			$this->content .= '<table><tr><td><form action="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'">'.chr(10);
			$this->content .= '<input type="hidden" name="sysfolder_id" value="'.$sysfolder_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="mapping_id" value="'.$mapping_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="deleteit" value="true">'.chr(10);
			$this->content .= '<input type="submit" value="'.$LANG->getLL('submit').'"></form></td><td>'.chr(10);
      		$this->content .= '<form action="index.php?SET[function]=1" method="POST">'.chr(10);
			$this->content .= '<input type="submit" value="'.$LANG->getLL('cancel').'">'.chr(10);
			$this->content .= '</td></tr></table>';
		}
		 
		/**
		* Return the HTML Code for form in $this->content
		*
		* @return void
		*/
		function deleteMappingTable() {
			global $LANG;
			$mapping_id = t3lib_div::GPvar('mapping_id');
			if (t3lib_div::GPvar('deleteit')) {
				$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->table_properties, 'uid='.$mapping_id);
				$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->table_usermap, 'mapping_id='.$mapping_id);
				$this->content .= $this->doc->section($LANG->getLL('deleteTitle'), $LANG->getLL('deleteText'), 1, 1);
				 
			} else {
				$this->content .= $this->EditTableProperties($mapping_id, 1);
				$this->content .= $this->doc->section('Are you Sure?', '', 0, 1);
				
				$this->content .= '<table><tr><td><form action="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'">'.chr(10);
				$this->content .= '<input type="hidden" name="sysfolder_id" value="'.$sysfolder_id.'">'.chr(10);
				$this->content .= '<input type="hidden" name="mapping_id" value="'.$mapping_id.'">'.chr(10);
				$this->content .= '<input type="hidden" name="deleteit" value="true">'.chr(10);
				$this->content .= '<input type="submit" value="'.$LANG->getLL('submit').'"></form></td><td>'.chr(10);
				$this->content .= '<form action="index.php?SET[function]=1" method="POST">'.chr(10);
				$this->content .= '<input type="submit" value="'.$LANG->getLL('cancel').'">'.chr(10);
				$this->content .= '</form></td></tr></table>';
			}
		}
		 
		/**
		* Saves the Mapping Table (from t3lib_div::GPvar('offset')
		* to t3lib_div::GPvar('offset')+$this->extConf['maxUsersPerPage']
		* Only the actualy shown users will be saved
		*
		* @return void
		*/
		function saveMappingTable() {
			$mapping_id = t3lib_div::GPvar('mapping_id');
			$sysfolder_id = t3lib_div::GPvar('sysfolder_id');
			$mapping_tablename = t3lib_div::GPvar('mapping_tablename');
			$mapping_defaultmapping = t3lib_div::GPvar('mapping_defaultmapping');
			$offset = t3lib_div::GPvar('offset');
			$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);
			$maxUsersPerPage = $this->extConf['maxUsersPerPage'];
			 
			$allowall = t3lib_div::GPvar('allowall') ? 1 : 0;
			 
			// Save Table Properties (name and default mapping)
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_properties, 'uid='.$mapping_id);
			$numrows = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			if ($numrows == 1 ) {
				$values = Array(
					'mapping_tablename' => $mapping_tablename,
					'mapping_defaultmapping' => $mapping_defaultmapping,
					'allowall' => $allowall,
				);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table_properties,'uid='.$mapping_id,$values);
			} else {
				$values = Array(
					'sysfolder_id' => $sysfolder_id,
					'mapping_tablename' => $mapping_tablename,
					'mapping_defaultmapping' => $mapping_defaultmapping,
					'allowall' => $allowall,
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table_properties,$values);
				$mapping_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
			}
			 
			// Save User Mappings (from $offset to $offset+$maxUsersPerPage)
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', 'pid='.$sysfolder_id.' AND deleted =0');
			$menge = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			for($i = 0; $i < $menge; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				if (($i >= $offset) && ($i < $offset+$maxUsersPerPage)) {
					$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_usermap, 'mapping_id='.$mapping_id.' AND fe_uid='.$row['uid']);
					$feuid = 'fe_uid'.$row['uid'];
					$username = t3lib_div::GPvar($feuid);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($result2) == 1) {
						// Update DB
						$values = Array(
							'mapping_username' => $username,
						);
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table_usermap,'mapping_id='.$mapping_id.' AND fe_uid='.$row['uid'],$values);
					} else {
						// Insert in DB
						$values = Array(
							'mapping_id' => $mapping_id,
							'fe_uid' => $row['uid'],
							'mapping_username' => $username,
						);
						$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table_usermap,$values);
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
			global $LANG;
			$sysfolder_id = t3lib_div::GPvar('sysfolder_id');
			$mapping_id = t3lib_div::GPvar('mapping_id');
			$offset = t3lib_div::GPvar('offset');
			if (!isset($mapping_id)) {
				$mapping_id = 0;
			}
			if (!isset($sysfolder_id)) {
				$this->content .= $this->doc->section('Error: No Sysfolder id', '', 0, 1);
				return;
			}
			$this->content .= '<form action="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'">'.chr(10);
			$this->content .= '<input type="hidden" name="sysfolder_id" value="'.$sysfolder_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="mapping_id" value="'.$mapping_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="saveit" value="true">'.chr(10);
			$this->content .= '<input type="hidden" name="offset" value="'.$offset.'">'.chr(10);

			# Table Properties
			$this->EditTableProperties($mapping_id);

			# User Mapping List: [uid] => [mappingname]
			$this->userlist = $this->mapUserlist($sysfolder_id, $mapping_id);
			$numberofusers = count($this->userlist);

			# Fill the Userlist form (more pages)
			$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);
			$maxUsersPerPage = $this->extConf['maxUsersPerPage'];
			$numPages = (int) $numberofusers/$maxUsersPerPage;

            $this->content .= '<h3>';
            $this->content .= $this->generate_pagination("index.php?SET[function]=".(string)$this->MOD_SETTINGS['function'].'&amp;sysfolder_id='.$sysfolder_id.'&amp;mapping_id='.$mapping_id,$numberofusers,$maxUsersPerPage,$offset);
            $this->content .= '</h3>';

			$this->content .= '<table>'.chr(10);

			$tmp_num = 0;
			foreach ($this->userlist as $id => $name) {
				if (($tmp_num >= $offset) && ($tmp_num < $offset+$maxUsersPerPage)) {
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', 'pid='.$sysfolder_id.' AND uid='.$id.' AND deleted=\'0\'');
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$this->content .= '<tr>'.chr(10);
					$this->content .= '<td class="td1">'.$LANG->getLL('username').'</td><td class="td2">'.$row['username'].'</td>'.chr(10);
					$this->content .= '<td class="td1">'.$LANG->getLL('realname').'</td><td class="td2">'.$row['name'].'</td>'.chr(10);
					$this->content .= '<td class="td1">'.$LANG->getLL('mapname').'</td><td class="td2"><input name="fe_uid'.$id.'" type="text" value="'.$name.'" size="30"></td></tr>'.chr(10);
				}
				$tmp_num++;
			}
			$this->content .= '</table>'.chr(10);
			$this->content .= '<table><tr><td><form action="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'">'.chr(10);
			$this->content .= '<input type="hidden" name="sysfolder_id" value="'.$sysfolder_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="mapping_id" value="'.$mapping_id.'">'.chr(10);
			$this->content .= '<input type="hidden" name="deleteit" value="true">'.chr(10);
			$this->content .= '<input type="submit" value="'.$LANG->getLL('submit').'"></form></td><td>'.chr(10);
			$this->content .= '<form action="index.php?SET[function]=1" method="POST">'.chr(10);
			$this->content .= '<input type="submit" value="'.$LANG->getLL('cancel').'">'.chr(10);
			$this->content .= '</form></td></tr></table>';
		}

		/**
		* Pagination routine, generates
        * page number sequence for the Usermapping,
        * (function copied from the phpBB http://www.phpBB.com)
		*
		* @return string  Pagination (html code for: Goto page back, 1, 2, 3, ... 5, 6, 7, ..., 12, 13, 14 next)
		*/
        function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE){
        	global $LANG;
          	$total_pages = ceil($num_items/$per_page);
            if ( $total_pages == 1 ){
               return '';
            }
            $on_page = floor($start_item / $per_page) + 1;
            $page_string = '';
            if ( $total_pages > 10 ){
                $init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;
               	for($i = 1; $i < $init_page_max + 1; $i++){
                    $page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;offset=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
                    if ( $i <  $init_page_max ){
                        $page_string .= ", ";
                    }
                }
                if ( $total_pages > 3 ){
                   if ( $on_page > 1  && $on_page < $total_pages ){
                       $page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';
                       $init_page_min = ( $on_page > 4 ) ? $on_page : 5;
                       $init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;
                       for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++){
                           $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;offset=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
                           if ( $i <  $init_page_max + 1 ){
                               $page_string .= ', ';
                           }
                       }
                       $page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
                   } else {
                       $page_string .= ' ... ';
			       }
                   for($i = $total_pages - 2; $i < $total_pages + 1; $i++){
                       $page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>'  : '<a href="' . $base_url . "&amp;offset=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
				       if( $i <  $total_pages ){
                           $page_string .= ", ";
                       }
                   }
		        }
	        } else	{
		        for($i = 1; $i < $total_pages + 1; $i++){
                    $page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;offset=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
			        if ( $i <  $total_pages ){
                        $page_string .= ', ';
			        }
		        }
          	}
            if ( $add_prevnext_text ){
                if ( $on_page > 1 ){
			        $page_string = ' <a href="' . $base_url . "&amp;offset=" . ( ( $on_page - 2 ) * $per_page ) . '">' . $LANG->getLL('goBack') . '</a>&nbsp;&nbsp;' . $page_string;
		        }
                if ( $on_page < $total_pages ){
			        $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;offset=" . ( $on_page * $per_page ) . '">' . $LANG->getLL('goForward') . '</a>';
		        }
            }
            $page_string = $LANG->getLL('gotoPage') . ' ' . $page_string;
            return $page_string;
        }

		/**
		* return the form for selectUserFolder() in $this->content
		* This will print out a clickable list of all Sysfolders
		*
		* @return void
		*/
		function selectUserFolder() {
			global $LANG;
			
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','pages', 'doktype=\'254\' AND deleted=\'0\'');
			$menge = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			$content1 .= '<table>';
			for($i = 0; $i < $menge; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				$ahref = '<a href="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'&amp;sysfolder_id='.$row['uid'].'" class="link1">'.chr(10);
				$result1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', 'pid='.$row['uid'].' AND deleted=\'0\'');
				$num = $GLOBALS['TYPO3_DB']->sql_num_rows($result1);
				$content1 .= '<tr>'.chr(10);
				$content1 .= '<td class="td1">'.$ahref.$LANG->getLL('foldername').'</a></td><td class="td2">'.$ahref.$row['title'].'</a></td>'.chr(10);
				$content1 .= '<td class="td1">'.$ahref.$LANG->getLL('uid').'</a></td><td class="td2">'.$ahref.$row['uid'].'</a></td>'.chr(10);
				$content1 .= '<td class="td1">'.$ahref.$LANG->getLL('nrUsers').'</a></td><td class="td2">'.$ahref.$num.'</a></td></tr>'.chr(10);
			}
			$content1 .= '</table>';
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			$this->content .= $this->doc->section($LANG->getLL('selectSysfolder'), $content1, 1, 1);
		}
		 
		/**
		* return the form for selectMappingTable() in $this->content
		*
		* @return void
		*/
		function selectMappingTable() {
			global $LANG;
			
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_properties,'1');
			$menge = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			$content1 .= '<table>';
			for($i = 0; $i < $menge; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				$ahref = '<a href="index.php?SET[function]='.(string)$this->MOD_SETTINGS['function'].'&amp;sysfolder_id='.$row['sysfolder_id'].'&amp;mapping_id='.$row['uid'].'" class="link1">'.chr(10);
				$content1 .= '<tr class="box">'.chr(10);
				$content1 .= '<td class="td1">'.$ahref.$LANG->getLL('mappingTable').'</a></td><td class="td2">'.$ahref.$row['mapping_tablename'].'</a></td>'.chr(10);
				$content1 .= '<td class="td1">'.$ahref.$LANG->getLL('sysfolderid').'</a></td><td class="td2">'.$ahref.$row['sysfolder_id'].'</a></td>'.chr(10);
				$content1 .= '</tr>';
			}
			$content1 .= '</table>';
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			$this->content .= $this->doc->section($LANG->getLL('selectMappingtable'), $content1, 1, 1);
		}
		 
		/**
		* return a mapping table properties (a form to edit) in $this->content for the given
		* $mapping_id.
		*
		* @param integer $mapping_id: the UID for the mapping table you want to edit.
		* @param integer $show: if this is set to 1 the function only display the forms (readonly)
		* @return void
		*/
		function EditTableProperties($mapping_id = '0', $show = '0') {
			global $LANG;
			$editable = $show ? ' READONLY' : '';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_properties, 'uid='.$mapping_id);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			 
			$content = '<table><tr><td>'. $LANG->getLL('enterTableName') .'</td><td>'.chr(10);
			$content .= '<input name="mapping_tablename" type="text"'.$editable.' value="'.$row['mapping_tablename'].'"></td></tr>'.chr(10);
			$content .= '<tr><td>'.$LANG->getLL('allow').'</td><td><input type="checkbox" name="allowall"'.$editable;
			if ($row['allowall'] == 1) {
				$content .= ' CHECKED';
			}
			$content .= '></td></tr>';
			$content .= '<tr><td>'. $LANG->getLL('defaultmapping') .'</td><td>'.chr(10);
			$content .= '<input name="mapping_defaultmapping" type="text"'.$editable.' value="'.$row['mapping_defaultmapping'].'"></td></tr>'.chr(10);
			$content .= '</table>';
			 
			$mapping_id_print = $mapping_id;
			if ($mapping_id_print == 0) {
				$mapping_id_print = 'new';
			}
			if ($show == 0) {
				$title = $LANG->getLL('editTitle');
			} else {
				$title = $LANG->getLL('viewTableName');
			}
			$this->content .= $this->doc->section($title." (ID: $mapping_id_print)", $content, 1, 1);
		}
		 
		/**
		* returns a user list for the given parameters
		*
		* @param integer  $sysfolder_id: UID of the sysfolder where the fe_users are stored
		* @param integer  $mapping_id: if given the UID of the mapping table
		* @return array   ([uid] => [mapped_username], )
		*/
		function mapUserlist($sysfolder_id, $mapping_id = '0') {
			global $LANG;
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', 'pid='.$sysfolder_id.' AND deleted=\'0\'');
			$menge = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			$userarray = array();
			for($i = 0; $i < $menge; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
				$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->table_usermap, 'mapping_id='.$mapping_id.' AND fe_uid='.$row['uid']);
				$row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2);
				$userarray += array($row['uid'] => $row2['mapping_username']);
			}
			return $userarray;
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/mod1/index.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/mod1/index.php']);
	}
	 
	// Make instance:
	$SOBE = t3lib_div::makeInstance('tx_nawsinglesignon_module1');
	$SOBE->init();
	 
	// Include files?
	reset($SOBE->include_once);
	while (list(, $INC_FILE) = each($SOBE->include_once)) {
		include_once($INC_FILE);
	}
	 
	$SOBE->main();
	$SOBE->printContent();
	 
?>
