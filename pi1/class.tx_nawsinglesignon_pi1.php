<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Dietrich Heise (typo3-ext@naw.info)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
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
* Plugin 'Single Sign-On' for the 'naw_single_signon' extension.
*
* @author Dietrich Heise <typo3-ext@naw.info>
*/

require_once (PATH_tslib.'class.tslib_pibase.php');

/**
* The Main Class of this extenstion to display Content in the Frontend
*/
class tx_nawsinglesignon_pi1 extends tslib_pibase {
	var $sso_version = '2.0';
	var $prefixId = 'tx_nawsinglesignon_pi1';
	// Same as class name
	var $scriptRelPath = 'pi1/class.tx_nawsinglesignon_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'naw_single_signon'; // The extension key.
	/**
	* create a link or redirect for a third party application (tpa)
	*
	* @param string  $content: Here the content will given
	* @param array  $conf: the conf array
	* @return string  $this->pi_wrapInBaseClass($content)
	*/
	function main($content, $conf) {
		// debug switch
		//$debugflag=true;

		$GLOBALS['TSFE']->set_no_cache();
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);

		$this->pi_initPIflexForm();

		// Force SSL?
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'forcessl','sDEF3') && !($_SERVER['HTTPS'] == 'on')) {
			// no SSL page but required!
			return $this->pi_wrapInBaseClass($this->pi_getLL('no_ssl'));
		}

		// Calculate Link validtime
		$this->valid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'linklifetime','sDEF3') + time();

		$user = ($this->getMappedUser($GLOBALS['TSFE']->fe_user->user['uid']));
		if (!$user) {
			return $this->pi_wrapInBaseClass($this->pi_getLL('no_usermapping'));
		}

		if (!$this->getMappedUser($GLOBALS['TSFE']->fe_user->user['uid'])) {
			return $this->pi_wrapInBaseClass($this->pi_getLL('no_user'));
		}



		// Create Signing Data
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'flag_create','sDEF3')){
			$create_modify = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'flag_create','sDEF3');
		}else{
			$create_modify ='0';
		}

		$flags=base64_encode('create_modify='.$create_modify);
		$tablefields=$GLOBALS['TYPO3_DB']->admin_get_fields('fe_users');
		$userdata_tmp='';
		$userdata_splitchar=''; // set blank for first entry

		$tmp_enable=explode(',',$this->extConf['enable_fields']);
		$tmp2_enable=explode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'enable_fields','sDEF3'));
		$fields_enable=array_merge($tmp_enable,$tmp2_enable);
		foreach ($tablefields as $i){
			if ($GLOBALS['TSFE']->fe_user->user[$i['Field']] AND in_array($i['Field'],$fields_enable)){
				if ($i['Field'] == 'usergroup'){
					$groups=explode(',',$GLOBALS['TSFE']->fe_user->user[$i['Field']]);
					$groupsdata_splitchar='';
					$userdata_tmp.=$userdata_splitchar.$i['Field'].'=';
					foreach ($groups as $j){
						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_groups', 'uid='.$j);
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
							$userdata_tmp.=$groupsdata_splitchar.$row['title'] ;
						}
						$groupsdata_splitchar=',';
					}
				}else{
					$userdata_tmp.=$userdata_splitchar.$i['Field'].'='.$GLOBALS['TSFE']->fe_user->user[$i['Field']];
				}
				$userdata_splitchar='|'; //splitchar after first entry...
			}
		}
		$userdata=base64_encode($userdata_tmp);

		//print_r($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF'));
		$this->data = 'version='.$this->sso_version.'&user='.$user.'&tpa_id='.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF').'&expires='.$this->valid.'&action=logon&flags='.$flags.'&userdata='.$userdata;
		//$this->data = 'user='.$user.'&amp;tpa_id='.$this->cObj->data['tx_nawsinglesignon_tpaid'].'&amp;expires='.$this->valid;
		if ($debugflag)
			print_r($this->data);

		if ($this->extConf['externalOpenssl']) {
			$keyfile = $this->extConf['SSLPrivateKeyFile'];
			$external_command = "echo -n \"$this->data\" |/usr/bin/openssl dgst -sha1 -sign $keyfile";
			$this->signature = shell_exec("$external_command");
			if ($debugflag) {
				print ('<br>calling OpenSSL via command line');
				print ('<br>Command executed: <br>'.$external_command.'<br>');
			}

			// Windows workaround
			// this is because under windows the signature / shell_exec result is cut in some cases
			// need to investigate OR at least beautify this workaround :-) /Ekki
			while (strlen($this->signature) != 256) {
				if ($debugflag)
					print ('<br>key is not 256 bytes. Repeating...');
				$this->signature = shell_exec($external_command);
			}

		} else {

			if ($debugflag)
				print ('<br>using compiled-in OpenSSL');
			if (function_exists('openssl_sign')) {
				// OPENSSL Sign (PHP - function)
				$this->fp = @ fopen($this->extConf['SSLPrivateKeyFile'], 'r');
				if (!$this->fp) {
					return $this->pi_wrapInBaseClass($this->pi_getLL('no_ssl_key_found'));
				}
				$this->priv_key = fread($this->fp, 8192);
				fclose($this->fp);
				$this->pkeyid = openssl_get_privatekey($this->priv_key, $this->extConf['SSLPassphrase']);
				// calculate the signature
				openssl_sign($this->data, $this->signature, $this->pkeyid);
				// remove sign from memory
				openssl_free_key($this->pkeyid);
				// END OPENSSL Sign
			} else {
				return $this->pi_wrapInBaseClass($this->pi_getLL('no_openssl_inPHP'));
			}
		}

		# debug mode: save binary signature to file
		if ($debugflag) {
			$tmp_signature_file = '/tmp/sigsso_debug.signature';
			$tmp_file = @ fopen($tmp_signature_file, "w");
			fwrite($tmp_file, $this->signature);
			fclose($tmp_file);
			print ('<br>Stored binary signature into '.$tmp_signature_file);
			print ('<br>bin2hex of binary signature: '.bin2hex($this->signature));
		}
		# Code the signature in hex format
		$this->sign = bin2hex($this->signature);

		# Generate the URL
		$this->URL = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'targeturl','sDEF').'?'.$this->data.'&signature='.$this->sign;
		//$this->URL = $this->cObj->data['tx_nawsinglesignon_targeturl'].'?'.$this->data.'&amp;signature='.$this->sign;

		// Insert Link/Redirect/Popup
                if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'frametargetcustom','sDEF2')) {
			$this->target = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'frametargetcustom','sDEF2');
                } else {
                	$this->target = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'frametarget','sDEF2');
                }
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'contenttype','sDEF2') == 2) {
			// Display Link in Content (2)
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'linkdescription','sDEF')) {
				// if no linkdescription is set use the tpa_id
				$this->LinkText = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'linkdescription','sDEF');
			} else {
				$this->LinkText = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF');
			}
			$content .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'html_before','sDEF2');
			$content .= '<a href="'.$this->URL.'" target='.$this->target.'>'.$this->LinkText.'</a>';
			$content .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'html_after','sDEF2');
		}
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'contenttype','sDEF2') == 1) {
			// Open here (HTTP redirect) (works well without frames) (1)
			header('Location: '.$this->URL);
			exit;
		}
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'contenttype','sDEF2') == 3) {
			// New Window (JavaScript) AND Link in Content (3)
			$jscode = '<script type="text/javascript">
								<!--
								'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF').' = window.open("'.$this->URL.'");
								//--></script>';
			$GLOBALS['TSFE']->additionalHeaderData += Array ('Window_onload_'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF') => $jscode);
			$content .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'html_before','sDEF2');
			$content .= '<a href="'.$this->URL.'" target='.$this->target.'>'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'linkdescription','sDEF').'</a>';
			$content .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'html_after','sDEF2');
		}
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'contenttype','sDEF2') == 0) {
			// Open in new window (requires JavaScript) (0)
			$jscode = '<script type="text/javascript">
								<!--
								'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF').' = window.open("'.$this->URL.'");
								//--></script>';
			$GLOBALS['TSFE']->additionalHeaderData += Array ('Window_onload_'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'tpaid','sDEF') => $jscode);
		}
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'contenttype','sDEF2') == 4) {
			// Output URL as string only
			$content .= $this->URL;
			return $content;
		}

		#print_r('<pre>');
		#print_r($this->cObj->data['tx_nawsinglesignon_usermapping']);
		#print_r('</pre>');

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	* return the mapped username for $uid
	*
	* @param integer  $uid: the uid of the current fe_user
	* @return string  mapped username
	*/
	function getMappedUser($uid) {
		if (!$uid) return;
		$mapping_id = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'usermapping','sDEF3');

		// Default Table (mapping as it is)
		if ($mapping_id == 0) {
			return $GLOBALS['TSFE']->fe_user->user['username'];
		}

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_nawsinglesignon_properties', 'deleted=0 AND uid='.$mapping_id);
		//$query = 'SELECT * FROM tx_nawsinglesignon_properties WHERE uid='.$mapping_id;
		//$res = mysql_query($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		// If allowall map undef-users to fe_usernames, else deny
		$allowall = $row['allowall'];
		$sysfolder_id = $row['sysfolder_id'];
		$mapping_defaultmapping = $row['mapping_defaultmapping'];
		//$query = 'SELECT * FROM tx_nawsinglesignon_usermap WHERE mapping_id='.$mapping_id.' AND fe_uid='.$uid;
		//$res = mysql_query($query);
		//$row = mysql_fetch_array($res);

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_nawsinglesignon_usermap', 'mapping_id='.$mapping_id.' AND fe_uid='.$uid);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		if ($GLOBALS['TSFE']->fe_user->user['pid'] != $sysfolder_id) {
			#          debug( $GLOBALS['TSFE']->fe_user->user['pid'].' != '.$sysfolder_id);
			return;
		}

		if (($row['mapping_username'] == '') && ($allowall == 1) && $mapping_defaultmapping) {
			return $mapping_defaultmapping;
		}elseif (($row['mapping_username'] == '') && ($allowall == 1)){
			return $GLOBALS['TSFE']->fe_user->user['username'];
		}

		return $row['mapping_username'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/pi1/class.tx_nawsinglesignon_pi1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/pi1/class.tx_nawsinglesignon_pi1.php']);
}
?>
