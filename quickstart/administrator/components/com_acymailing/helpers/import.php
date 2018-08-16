<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acyimportHelper{

	var $importUserInLists = array();
	var $totalInserted = 0;
	var $totalTry = 0;
	var $totalValid = 0;
	var $allSubid = array();
	var $db;
	var $dispatcher;
	var $forceconfirm = false;
	var $charsetConvert;
	var $generatename = true;
	var $overwrite = false;
	var $importblocked = false;
	var $removeSep = 0;
	var $dispresults = true;

	var $tablename = '';
	var $equFields = array();
	var $dbwhere = array(); //handle where on import via filter to only import new users for example

	var $subscribedUsers = array();

	public function __construct(){
		acymailing_increasePerf();
		acymailing_importPlugin('acymailing');
		
		global $acymailingCmsUserVars;
		$this->cmsUserVars = $acymailingCmsUserVars;
	}

	private function getImportedLists(){
		$lists = acymailing_getVar('array', 'importlists', array());

		$newListName = acymailing_getVar('string', 'createlist');
		if(empty($newListName)) return $lists;

		$newList = new stdClass();
		$newList->name = $newListName;
		$newList->published = 1;
		$colors = array('#3366ff', '#7240A4', '#7A157D', '#157D69', '#ECE649');
		$newList->color = $colors[rand(0, count($colors) - 1)];

		$listClass = acymailing_get('class.list');
		$listid = $listClass->save($newList);

		if(!empty($listid)) $lists[$listid] = 1;

		return $lists;
	}

	function database($onlyimport = false){

		$this->forceconfirm = acymailing_getVar('int', 'import_confirmed_database');

		$table = empty($this->tablename) ? trim(acymailing_getVar('string', 'tablename')) : $this->tablename;

		if(empty($table)){
			$listTables = acymailing_getTableList();
			acymailing_enqueueMessage(acymailing_translation_sprintf('SPECIFYTABLE', implode(' | ', $listTables)), 'notice');
			return false;
		}

		if(empty($this->tablename)){
			$newConfig = new stdClass();
			$newConfig->import_db_table = trim(acymailing_getVar('string', 'tablename'));
			$newConfig->import_db_fields = serialize(acymailing_getVar('array', 'fields', array()));

			$config = acymailing_config();
			$config->save($newConfig);
		}

		$fields = acymailing_getColumns($table);
		if(empty($fields)){
			$listTables = acymailing_getTableList();
			acymailing_enqueueMessage(acymailing_translation_sprintf('SPECIFYTABLE', implode(' | ', $listTables)), 'notice');
			return false;
		}

		$fields = array_keys($fields);
		$equivalentFields = empty($this->equFields) ? acymailing_getVar('array', 'fields', array()) : $this->equFields;

		if(empty($equivalentFields['email'])){
			acymailing_enqueueMessage(acymailing_translation('SPECIFYFIELDEMAIL'), 'notice');
			return false;
		}

		$select = array();
		foreach($equivalentFields as $acyField => $tableField){
			$tableField = trim($tableField);
			if(empty($tableField)) continue;
			if(!in_array($tableField, $fields)){
				acymailing_enqueueMessage(acymailing_translation_sprintf('SPECIFYFIELD', $tableField, implode(' | ', $fields)), 'notice');
				return false;
			}
			$select['`'.$acyField.'`'] = '`'.$tableField.'`';
		}

		if(empty($select['`created`'])){
			$select['`created`'] = time();
		}
		if($this->forceconfirm && empty($select['`confirmed`'])){
			$select['`confirmed`'] = 1;
		}

		$query = 'INSERT IGNORE INTO `#__acymailing_subscriber` ('.implode(' , ', array_keys($select)).') SELECT '.implode(' , ', $select).' FROM '.$table.' WHERE '.$select['`email`'].' LIKE \'%@%\'';
		if(!empty($this->dbwhere)) $query .= ' AND ( '.implode(' ) AND (', $this->dbwhere).' )';

		$affectedRows = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $affectedRows));

		if($onlyimport) return true;

		$query = 'SELECT b.subid FROM '.$table.' as a JOIN '.acymailing_table('subscriber').' as b on a.'.$select['`email`'].' = b.`email`';
		$this->allSubid = acymailing_loadResultArray($query);

		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function textarea(){
		$content = acymailing_getVar('string', 'textareaentries');
		$path = $this->_createUploadFolder();
		$filename = uniqid('import_').'.csv';

		acymailing_writeFile($path.$filename, $content);
		acymailing_setVar('filename', $filename);

		return true;
	}

	private function _createUploadFolder(){
		$folderPath = acymailing_cleanPath(ACYMAILING_ROOT.trim(html_entity_decode(str_replace('/', DS, ACYMAILING_MEDIA_FOLDER).DS.'import'))).DS;
		if(!is_dir($folderPath)){
			acymailing_createDir($folderPath, true, true);
		}

		if(!is_writable($folderPath)){
			@chmod($folderPath, '0755');
			if(!is_writable($folderPath)){
				acymailing_enqueueMessage(acymailing_translation_sprintf('WRITABLE_FOLDER', $folderPath), 'notice');
			}
		}
		return $folderPath;
	}

	function file(){
		$importFile = acymailing_getVar('array', 'importfile', array(), 'files');

		if(empty($importFile['name'])){
			acymailing_enqueueMessage(acymailing_translation('BROWSE_FILE'), 'notice');
			return false;
		}

		$extension = strtolower(acymailing_fileGetExt($importFile['name']));
		if(in_array($extension, array('xls', 'xlsx'))){
			acymailing_display('Excel files are not supported.<br />Please convert your file into CSV :<ol><li>Open your file with Excel</li><li>Select File => Save as...</li><li>For the type, select "CSV (separator: semi-colon) (*.csv)"</li></ol>', 'error');
			return false;
		}

		$fileError = $_FILES['importfile']['error'];
		if($fileError > 0){
			switch($fileError){
				case 1:
					acymailing_display('The uploaded file exceeds the upload_max_filesize directive in php configuration.', 'error');
					return false;
				case 2:
					acymailing_display('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'error');
					return false;
				case 3:
					acymailing_display('The uploaded file was only partially uploaded.', 'error');
					return false;
				case 4:
					acymailing_display('No file was uploaded.', 'error');
					return false;
				default:
					acymailing_display('Error uploading the file on the server, unknown error '.$fileError, 'error');
					return false;
			}
		}

		$config = acymailing_config();

		$uploadPath = $this->_createUploadFolder();

		$attachment = new stdClass();
		$attachment->filename = uniqid('import_').'.csv';
		acymailing_setVar('filename', $attachment->filename);

		$attachment->size = $importFile['size'];

		if(!preg_match('#^('.str_replace(array(',', '.'), array('|', '\.'), $config->get('allowedfiles')).')$#Ui', $extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui', $importFile['name'])){
			acymailing_enqueueMessage(acymailing_translation_sprintf('ACCEPTED_TYPE', htmlspecialchars($extension, ENT_COMPAT, 'UTF-8'), $config->get('allowedfiles')), 'notice');
			return false;
		}

		if(!acymailing_uploadFile($importFile['tmp_name'], $uploadPath.$attachment->filename)){
			if(!move_uploaded_file($importFile['tmp_name'], $uploadPath.$attachment->filename)){
				acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_UPLOAD', '<b><i>'.htmlspecialchars($importFile['tmp_name'], ENT_COMPAT, 'UTF-8').'</i></b>', '<b><i>'.htmlspecialchars($uploadPath.$attachment->filename, ENT_COMPAT, 'UTF-8').'</i></b>'), 'error');
			}
		}
		return true;
	}

	function finalizeImport(){
		$config = acymailing_config();

		$this->forceconfirm = acymailing_getVar('int', 'import_confirmed');
		$this->generatename = acymailing_getVar('int', 'generatename');
		$this->importblocked = acymailing_getVar('int', 'importblocked');
		$this->overwrite = acymailing_getVar('int', 'overwriteexisting');

		$newConfig = new stdClass();
		$paramTmp = array();
		if($this->forceconfirm == 1) $paramTmp[] = 'import_confirmed';
		if($this->generatename == 1) $paramTmp[] = 'generatename';
		if($this->importblocked == 1) $paramTmp[] = 'importblocked';
		if($this->overwrite == 1) $paramTmp[] = 'overwriteexisting';

		$importParams = 'import_params';
		$newConfig->$importParams = implode(',', $paramTmp);
		$config->save($newConfig);

		$filename = strtolower(acymailing_getVar('cmd', 'filename'));
		$extension = '.'.acymailing_fileGetExt($filename);
		$filename = str_replace(array('.', ' '), '_', substr($filename, 0, strpos($filename, $extension))).$extension;
		$uploadPath = ACYMAILING_MEDIA.'import'.DS.$filename;

		if(!file_exists($uploadPath)){
			acymailing_enqueueMessage('Uploaded file not found: '.$uploadPath, 'error');
			return;
		}

		$importColumns = acymailing_getVar('string', 'import_columns');
		if(empty($importColumns)){
			acymailing_enqueueMessage('Columns not found', 'error');
			return false;
		}
		$columns = explode(',', $importColumns);
		$acyColumns = acymailing_getColumns('#__acymailing_subscriber');
		foreach($columns as $oneColumn){
			if($oneColumn == 1 || $oneColumn == 'listids' || $oneColumn == 'listname' || isset($acyColumns[$oneColumn])) continue; // Ignored or existing column
			$checkColumn = preg_replace('#[^A-Za-z0-9_]#Uis', '', $oneColumn);
			if(empty($checkColumn)){
				acymailing_enqueueMessage('Invalid field name: '.$oneColumn, 'error');
				return false;
			}
			$oneColumn = $checkColumn;

			if(!acymailing_level(3)){ // Make sure we can't create a custom field
				acymailing_enqueueMessage(acymailing_translation('EXTRA_FIELDS').' '.acymailing_translation('ONLY_FROM_ENTERPRISE'), 'error');
				return false;
			}

			if(empty($ordering)){
				$ordering = acymailing_loadResult('SELECT MAX(ordering) FROM #__acymailing_fields');
			}
			$ordering++;
			acymailing_query('ALTER TABLE `#__acymailing_subscriber` ADD `'.acymailing_secureField(strtolower($oneColumn)).'` TEXT NOT NULL DEFAULT ""');
			$query = "INSERT INTO `#__acymailing_fields` (`fieldname`, `namekey`, `type`, `value`, `published`, `ordering`, `options`, `core`, `required`, `backend`, `frontcomp`, `default`, `listing`, `frontlisting`, `frontform`) VALUES
			(".acymailing_escapeDB($oneColumn).", ".acymailing_escapeDB(strtolower($oneColumn)).", 'text', '', 1, ".intval($ordering).", '', 0, 0, 1, 0, '',0,0,1);";
			acymailing_query($query);
		}

		$contentFile = file_get_contents($uploadPath);

		if(acymailing_getVar('cmd', 'charsetconvert', '') != ''){
			$encodingHelper = acymailing_get('helper.encoding');
			$contentFile = $encodingHelper->change($contentFile, acymailing_getVar('cmd', 'charsetconvert'), 'UTF-8');
		}

		$cutContent = str_replace(array("\r\n", "\r"), "\n", $contentFile);
		$allLines = explode("\n", $cutContent);

		$listSeparators = array("\t", ';', ',');
		$separator = ',';
		foreach($listSeparators as $sep){
			if(strpos($allLines[0], $sep) !== false){
				$separator = $sep;
				break;
			}
		}
		$importColumns = str_replace(',', $separator, $importColumns);

		if(strpos($allLines[0], '@')){
			$contentFile = $importColumns."\n".$contentFile;
		}else{
			$allLines[0] = $importColumns;
			$contentFile = implode("\n", $allLines);
		}

		$this->_handleContent($contentFile);
		$this->_displaySubscribedResult();

		unlink($uploadPath);
		$this->_cleanImportFolder();
	}

	public function _cleanImportFolder(){
		
		$files = acymailing_getFiles(ACYMAILING_MEDIA.'import', '.', false, true, array());
		foreach($files as $oneFile){
			if(acymailing_fileGetExt($oneFile) != 'csv') continue;
			if(filectime($oneFile) < time() - 86400) unlink($oneFile);
		}
	}

	public function _handleContent(&$contentFile){
		$success = true;

		$contentFile = str_replace(array("\r\n", "\r"), "\n", $contentFile);
		$importLines = explode("\n", $contentFile);

		$i = 0;
		$this->header = '';
		$this->allSubid = array();
		while(empty($this->header) && $i < 10){
			$this->header = trim($importLines[$i]);
			$i++;
		}

		if(strpos($this->header, '@') && !strpos($this->header, ',') && !strpos($this->header, ';') && !strpos($this->header, "\t")){
			$this->header = 'email';
			$i--;
		}

		if(!$this->_autoDetectHeader()){
			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_HEADER', htmlspecialchars($this->header, ENT_COMPAT, 'UTF-8')), 'error');
			acymailing_enqueueMessage(acymailing_translation('IMPORT_EMAIL'), 'error');
			acymailing_enqueueMessage(acymailing_translation('IMPORT_EXAMPLE'), 'error');
			return false;
		}

		$numberColumns = count($this->columns);

		$userHelper = acymailing_get('helper.user');

		$encodingHelper = acymailing_get('helper.encoding');

		$importUsers = array();

		$errorLines = array();

		$countUsersBeforeImport = acymailing_loadResult('SELECT COUNT(subid) FROM `#__acymailing_subscriber`');

		$listClass = acymailing_get('class.list');
		$allLists = $listClass->getLists('name');

		while(isset($importLines[$i])){
			if(strpos($importLines[$i], '"') !== false){
				$data = array();
				$j = $i + 1;
				$position = -1;

				while($j < ($i + 30)){

					$quoteOpened = substr($importLines[$i], $position + 1, 1) == '"';

					if($quoteOpened){
						$nextQuotePosition = strpos($importLines[$i], '"', $position + 2);
						while($nextQuotePosition !== false && $nextQuotePosition + 1 != strlen($importLines[$i]) && substr($importLines[$i], $nextQuotePosition + 1, 1) != $this->separator){
							$nextQuotePosition = strpos($importLines[$i], '"', $nextQuotePosition + 1);
						}
						if($nextQuotePosition === false){
							if(!isset($importLines[$j])) break;

							$importLines[$i] .= "\n".$importLines[$j];
							$importLines[$i] = rtrim($importLines[$i], $this->separator);
							unset($importLines[$j]);
							$j++;
							continue;
						}else{

							if(strlen($importLines[$i]) - 1 == $nextQuotePosition){
								$data[] = substr($importLines[$i], $position + 1);
								break;
							}
							$data[] = substr($importLines[$i], $position + 1, $nextQuotePosition + 1 - ($position + 1));
							$position = $nextQuotePosition + 1;
						}
					}else{
						$nextSeparatorPosition = strpos($importLines[$i], $this->separator, $position + 1);
						if($nextSeparatorPosition === false){
							$data[] = substr($importLines[$i], $position + 1);
							break;
						}else{ // If found the next separator, add the value in $data and change the position
							$data[] = substr($importLines[$i], $position + 1, $nextSeparatorPosition - ($position + 1));
							$position = $nextSeparatorPosition;
						}
					}
				}

				$importLines = array_merge($importLines);
			}else{
				$data = explode($this->separator, rtrim(trim($importLines[$i]), $this->separator));
			}

			if(!empty($this->removeSep)){
				for($b = $numberColumns + $this->removeSep - 1; $b >= $numberColumns; $b--){
					if(isset($data[$b]) AND (strlen($data[$b]) == 0 || $data[$b] == ' ')){
						unset($data[$b]);
					}
				}
			}

			$i++;
			if(empty($importLines[$i - 1])) continue;

			$this->totalTry++;
			if(count($data) > $numberColumns){
				$copy = $data;
				foreach($copy as $oneelem => $oneval){
					if(!empty($oneval[0]) AND $oneval[0] == '"' AND $oneval[strlen($oneval) - 1] != '"' AND isset($copy[$oneelem + 1]) AND $copy[$oneelem + 1][strlen($copy[$oneelem + 1]) - 1] == '"'){
						$data[$oneelem] = $copy[$oneelem].$this->separator.$copy[$oneelem + 1];
						unset($data[$oneelem + 1]);
					}
				}
				$data = array_values($data);
			}

			if(count($data) < $numberColumns){
				for($a = count($data); $a < $numberColumns; $a++){
					$data[$a] = '';
				}
			}

			if(count($data) != $numberColumns){
				$success = false;
				static $errorcount = 0;
				if(empty($errorcount)){
					acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_ARGUMENTS', $numberColumns), 'error');
				}
				$errorcount++;
				if($errorcount < 20){
					acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_ERRORLINE', '<b><i>'.htmlspecialchars($importLines[$i - 1], ENT_COMPAT, 'UTF-8').'</i></b>'), 'notice');
				}elseif($errorcount == 20){
					acymailing_enqueueMessage('...', 'notice');
				}

				if($this->totalTry == 1) return false;
				if(empty($errorLines)) $errorLines[] = $importLines[0];
				$errorLines[] = $importLines[$i - 1];
				continue;
			}

			$newUser = new stdClass();

			$emailKey = array_search('email', $this->columns);
			$newUser->email = trim(strip_tags($data[$emailKey]), '\'" ');
			if(!empty($newUser->email)) $newUser->email = acymailing_punycode($newUser->email);
			$newUser->email = trim(str_replace(array(' ', "\t"), '', $encodingHelper->change($newUser->email, 'UTF-8', 'ISO-8859-1')));
			if(!$userHelper->validEmail($newUser->email)){
				$success = false;
				static $errorcountfail = 0;
				$errorcountfail++;
				if($errorcountfail < 10){
					acymailing_enqueueMessage(acymailing_translation_sprintf('NOT_VALID_EMAIL', '<b><i>'.htmlspecialchars($newUser->email, ENT_COMPAT | ENT_IGNORE, 'UTF-8').'</i></b>').' | '.($i - 1).' : '.$importLines[$i - 1], 'notice');
				}elseif($errorcountfail == 10){
					acymailing_enqueueMessage('...', 'notice');
				}
				if(empty($errorLines)) $errorLines[] = $importLines[0];
				$errorLines[] = $importLines[$i - 1];
				continue;
			}

			foreach($data as $num => $value){
				if($num == $emailKey) continue;

				$field = $this->columns[$num];

				if($field == 1) continue;

				if($field == 'listids'){
					$liststosub = explode('-', trim($value, '\'" 	'));
					foreach($liststosub as $onelistid){
						$this->importUserInLists[intval(trim($onelistid))][] = acymailing_escapeDB($newUser->email);
					}
					continue;
				}

				if($field == 'listname'){
					$liststosub = explode('-', trim($value, '\'" 	'));
					foreach($liststosub as $onelistName){
						if(empty($onelistName)) continue;
						$onelistName = trim($onelistName);
						if(empty($allLists[$onelistName])){
							$newList = new stdClass();
							$newList->name = $onelistName;
							$newList->published = 1;
							$colors = array('#3366ff', '#7240A4', '#7A157D', '#157D69', '#ECE649');
							$newList->color = $colors[rand(0, count($colors) - 1)];
							$listid = $listClass->save($newList);
							$newList->listid = $listid;
							$allLists[$onelistName] = $newList;
						}
						$this->importUserInLists[intval($allLists[$onelistName]->listid)][] = acymailing_escapeDB($newUser->email);
					}
					continue;
				}

				if($value == 'null'){
					$newUser->$field = '';
				}else{
					$newUser->$field = trim(strip_tags($value), '\'" 	');
				}
			}

			unset($newUser->subid);
			unset($newUser->userid);

			$importUsers[] = $newUser;
			$this->totalValid++;

			if($this->totalValid % 50 == 0){
				$this->_insertUsers($importUsers);
				$importUsers = array();
			}
		}

		if(!empty($errorLines)){
			$filename = strtolower(acymailing_getVar('cmd', 'filename', ''));
			if(!empty($filename)){
				$extension = '.'.acymailing_fileGetExt($filename);
				$filename = str_replace(array('.', ' '), '_', substr($filename, 0, strpos($filename, $extension))).$extension;
				$errorFile = implode("\n", $errorLines);
				acymailing_writeFile(ACYMAILING_MEDIA.'import'.DS.'error_'.$filename, $errorFile);
				acymailing_enqueueMessage('<a target="_blank" href="'.acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'data&task=downloadimport').'&filename=error_'.preg_replace('#\.[^.]*$#', '', $filename).'" >'.acymailing_translation('ACY_DOWNLOAD_IMPORT_ERRORS').'</a>', 'notice');
			}
		}
		$this->_insertUsers($importUsers);

		$countUsersAfterImport = acymailing_loadResult('SELECT COUNT(subid) FROM `#__acymailing_subscriber`');
		$this->totalInserted = $countUsersAfterImport - $countUsersBeforeImport;

		if($this->dispresults){
			acymailing_enqueueMessage(acymailing_translation_sprintf('ACY_IMPORT_REPORT', $this->totalTry, $this->totalInserted, $this->totalTry - $this->totalValid, $this->totalValid - $this->totalInserted));
		}

		$this->_subscribeUsers();
		return $success;
	}

	function _subscribeUsers(){

		if(empty($this->allSubid)) return true;

		$subdate = time();

		$listClass = acymailing_get('class.list');

		if(empty($this->importUserInLists)){
			$lists = $this->getImportedLists();

			if(acymailing_level(3)){
				$campaignClass = acymailing_get('helper.campaign');
				$listCampaign = $listClass->getCampaigns(array_keys($lists));
			}else{
				$listCampaign = array();
			}

			foreach($lists as $listid => $val){
				if(empty($val)) continue;

				if($val == -1){
					$dateColumn = 'unsubdate';
					$status = -1;
				}else{
					$dateColumn = 'subdate';
					$status = 1;
				}

				$nbsubscribed = 0;
				$listid = (int)$listid;
				$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,'.$dateColumn.',status) VALUES ';
				$b = 0;
				$currentSubids = array();
				foreach($this->allSubid as $subid){
					$currentSubids[] = $subid;
					$b++;

					if($b > 200){
						$query = rtrim($query, ',');
						if($val == -1){
							$query .= ' ON DUPLICATE KEY UPDATE status = -1';
							$nbsubscribed = -acymailing_loadResult('SELECT COUNT(*) FROM #__acymailing_listsub WHERE listid = '.$listid.' AND status != -1 AND subid IN ('.implode(',', $currentSubids).')');
						}
						$affected = acymailing_query($query);
						$nbsubscribed += intval($affected);
						$b = 0;
						$currentSubids = array();
						$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,'.$dateColumn.',status) VALUES ';
					}

					$query .= "($listid,$subid,$subdate,$status),";
				}
				$query = rtrim($query, ',');
				if($val == -1){
					$query .= ' ON DUPLICATE KEY UPDATE status = -1';
					if(!empty($currentSubids)){
						$nbsubscribed = -acymailing_loadResult('SELECT COUNT(*) FROM #__acymailing_listsub WHERE listid = '.$listid.' AND status != -1 AND subid IN ('.implode(',', $currentSubids).')');
					}
				}
				$affected = acymailing_query($query);
				$nbsubscribed += intval($affected);

				if(isset($this->subscribedUsers[$listid])){
					$this->subscribedUsers[$listid]->nbusers += $nbsubscribed;
				}else{
					$myList = $listClass->get($listid);
					$myList->status = $val;
					$this->subscribedUsers[$listid] = $myList;
					$this->subscribedUsers[$listid]->nbusers = $nbsubscribed;
				}

				if(in_array($val, array(2, -1)) && !empty($listCampaign[$listid])){
					$function = $val == 2 ? 'autoSubCampaign' : 'unsubCampaign';
					foreach($listCampaign[$listid] as $campaignId){
						$campaignClass->$function($this->allSubid, $campaignId);
					}
				}
			}
		}else{
			foreach($this->importUserInLists as $listid => $arrayEmails){
				if(empty($listid)) continue;

				$listid = (int)$listid;
				$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) ';
				$query .= "SELECT $listid,`subid`,$subdate,1 FROM ".acymailing_table('subscriber')." WHERE `email` IN (";
				$query .= implode(',', $arrayEmails).')';
				$nbsubscribed = acymailing_query($query);
				$nbsubscribed = intval($nbsubscribed);

				if(isset($this->subscribedUsers[$listid])){
					$this->subscribedUsers[$listid]->nbusers += $nbsubscribed;
				}else{
					$myList = $listClass->get($listid);
					$this->subscribedUsers[$listid] = $myList;
					$this->subscribedUsers[$listid]->nbusers = $nbsubscribed;
				}
			}
		}

		return true;
	}

	function _displaySubscribedResult(){
		foreach($this->subscribedUsers as $myList){
			if(empty($myList->status) || $myList->status != -1){
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $myList->nbusers, '<b><i>'.$myList->name.'</i></b>'));
			}else{
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_UNSUBSCRIBE_CONFIRMATION', $myList->nbusers, '<b><i>'.$myList->name.'</i></b>'));
			}
		}
	}

	function _insertUsers($users){
		if(empty($users)) return true;

		$importedCols = array_keys(get_object_vars($users[0]));
		if($this->forceconfirm) $importedCols[] = 'confirmed';
		if($this->importblocked) $importedCols[] = 'enabled';

		foreach($users as $a => $oneUser){
			$this->_checkData($users[$a]);
		}

		$columns = reset($users);
		$colNames = array_keys(get_object_vars($columns));

		acymailing_trigger('onAcyBeforeUserImport', array(&$users));

		$query = 'INSERT'.($this->overwrite ? '' : ' IGNORE').' INTO '.acymailing_table('subscriber').' (`'.implode('`,`', $colNames).'`) VALUES (';
		$values = array();
		$allemails = array();
		foreach($users as $a => $oneUser){
			$value = array();
			acymailing_trigger('onAcyBeforeUserImport', array(&$oneUser));
			foreach($oneUser as $map => $oneValue){
				if($map == 'enabled' && !empty($this->importblocked) && $this->importblocked == true){
					$value[] = 0;
				}elseif($map != 'subid'){
					$value[] = acymailing_escapeDB($oneValue);
				}else{
					$value[] = $oneValue;
				}
				if($map == 'email'){
					$allemails[] = acymailing_escapeDB($oneValue);
				}
			}
			$values[] = implode(',', $value);
		}
		$query .= implode('),(', $values).')';
		if($this->overwrite){
			$query .= ' ON DUPLICATE KEY UPDATE ';
			foreach($importedCols as &$oneColumn){
				$oneColumn = '`'.$oneColumn.'`=VALUES(`'.$oneColumn.'`)';
			}
			$query .= implode(',', $importedCols);
		}

		acymailing_query($query);

		acymailing_trigger('onAcyAfterUserImport', array(&$users));

		$this->allSubid = array_merge($this->allSubid, acymailing_loadResultArray('SELECT subid FROM '.acymailing_table('subscriber').' WHERE email IN ('.implode(',', $allemails).')'));

		return true;
	}


	function _checkData(&$user){
		if(empty($user->created)){
			$user->created = time();
		}elseif(!is_numeric($user->created)) $user->created = strtotime($user->created);

		if(!isset($user->accept) || strlen($user->accept) == 0) $user->accept = 1;
		if(!isset($user->enabled) || strlen($user->enabled) == 0) $user->enabled = 1;
		if(!isset($user->html) || strlen($user->html) == 0) $user->html = 1;
		if(empty($user->source)) $user->source = 'import';

		if(!empty($user->confirmed_date) && !is_numeric($user->confirmed_date)) $user->confirmed_date = strtotime($user->confirmed_date);
		if(!empty($user->lastclick_date) && !is_numeric($user->lastclick_date)) $user->lastclick_date = strtotime($user->lastclick_date);
		if(!empty($user->lastopen_date) && !is_numeric($user->lastopen_date)) $user->lastopen_date = strtotime($user->lastopen_date);
		if(!empty($user->lastsent_date) && !is_numeric($user->lastsent_date)) $user->lastsent_date = strtotime($user->lastsent_date);


		if(empty($user->name) AND $this->generatename) $user->name = ucwords(trim(str_replace(array('.', '_', '-', 1, 2, 3, 4, 5, 6, 7, 8, 9, 0), ' ', substr($user->email, 0, strpos($user->email, '@')))));

		if((!isset($user->confirmed) || strlen($user->confirmed) == 0) AND $this->forceconfirm) $user->confirmed = 1;

		if(empty($user->key)) $user->key = acymailing_generateKey(14);
	}


	function _autoDetectHeader(){
		$this->separator = ',';

		$this->header = str_replace("\xEF\xBB\xBF", "", $this->header);

		$listSeparators = array("\t", ';', ',');
		foreach($listSeparators as $sep){
			if(strpos($this->header, $sep) !== false){
				$this->separator = $sep;
				break;
			}
		}


		$this->columns = explode($this->separator, $this->header);

		for($i = count($this->columns) - 1; $i >= 0; $i--){
			if(strlen($this->columns[$i]) == 0){
				unset($this->columns[$i]);
				$this->removeSep++;
			}
		}

		$columns = acymailing_getColumns('#__acymailing_subscriber');
		foreach($columns as $i => $oneColumn){
			$columns[strtolower($i)] = $oneColumn;
		}

		foreach($this->columns as $i => $oneColumn){
			$this->columns[$i] = strtolower(trim($oneColumn, '\'" '));
			if(in_array($this->columns[$i], array('listids', 'listname'))) continue;
			if(!isset($columns[$this->columns[$i]]) && $this->columns[$i] != 1){
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_ERROR_FIELD', '<b><i>'.htmlspecialchars($this->columns[$i], ENT_COMPAT, 'UTF-8').'</i></b>', implode(' | ', array_diff(array_keys($columns), array('subid', 'userid', 'key')))), 'error');
				return false;
			}
		}

		if(!in_array('email', $this->columns)) return false;

		return true;
	}

	function joomla(){
		$query = 'UPDATE IGNORE '.acymailing_table($this->cmsUserVars->table, false).' as b, '.acymailing_table('subscriber').' as a SET a.email = b.'.$this->cmsUserVars->email.', a.name = b.'.$this->cmsUserVars->name.', a.enabled = 1 - b.block WHERE a.userid = b.'.$this->cmsUserVars->id.' AND a.userid > 0';
		$nbUpdated = acymailing_query($query);

		$query = 'UPDATE IGNORE '.acymailing_table($this->cmsUserVars->table, false).' as b, '.acymailing_table('subscriber').' as a SET a.userid = b.'.$this->cmsUserVars->id.' WHERE a.email = b.'.$this->cmsUserVars->email;
		$affected = acymailing_query($query);
		$nbUpdated += intval($affected);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_UPDATE', $nbUpdated));

		$query = 'SELECT subid FROM '.acymailing_table('subscriber').' as a LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.userid = b.'.$this->cmsUserVars->id.' WHERE b.'.$this->cmsUserVars->id.' IS NULL AND a.userid > 0';
		$deletedSubid = acymailing_loadResultArray($query);

		$query = 'SELECT subid FROM '.acymailing_table('subscriber').' as a LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.email = b.'.$this->cmsUserVars->email.' WHERE b.'.$this->cmsUserVars->id.' IS NULL AND a.userid > 0';
		$deletedSubid = array_merge(acymailing_loadResultArray($query), $deletedSubid);

		if(!empty($deletedSubid)){
			$userClass = acymailing_get('class.subscriber');
			$deletedUsers = $userClass->delete($deletedSubid);
			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_DELETE', $deletedUsers));
		}

		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`userid`,`created`,`enabled`,`accept`,`html`) SELECT `'.$this->cmsUserVars->email.'`,`'.$this->cmsUserVars->name.'`,1-`'.$this->cmsUserVars->blocked.'`,`'.$this->cmsUserVars->id.'`,UNIX_TIMESTAMP(`'.$this->cmsUserVars->registered.'`),1-`'.$this->cmsUserVars->blocked.'`,1,1 FROM '.acymailing_table($this->cmsUserVars->table, false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$lists = $this->getImportedLists();
		$listsSubscribe = array();
		foreach($lists as $listid => $val){
			if(!empty($val)) $listsSubscribe[] = (int)$listid;
		}

		if(empty($listsSubscribe)) return true;

		if(acymailing_level(3)){
			$listClass = acymailing_get('class.list');
			$campaignClass = acymailing_get('helper.campaign');
			$listCampaign = $listClass->getCampaigns(array_keys($lists));
			foreach($lists as $listid => $val){
				if($val == 2 && !empty($listCampaign[$listid])){
					$query = 'SELECT sub.subid FROM #__acymailing_subscriber sub LEFT JOIN #__acymailing_listsub list ON sub.subid=list.subid AND list.listid='.intval($listid).' WHERE list.subid IS NULL AND sub.userid > 0 ';
					$listSubidNotInList = acymailing_loadResultArray($query);
					if(empty($listSubidNotInList)) continue;
					foreach($listCampaign[$listid] as $campaignId){
						$campaignClass->autoSubCampaign($listSubidNotInList, $campaignId);
					}
				}
			}
		}

		$query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (`listid`,`subid`,`subdate`,`status`) ';
		$query .= 'SELECT a.`listid`, b.`subid` ,'.$time.',1 FROM '.acymailing_table('list').' as a, '.acymailing_table('subscriber').' as b  WHERE a.`listid` IN ('.implode(',', $listsSubscribe).') AND b.`userid` > 0';
		$nbsubscribed = acymailing_query($query);
		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIPTION', $nbsubscribed));

		return true;
	}

	function acajoom(){
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (email,name,confirmed,created,enabled,accept,html) SELECT email,name,confirmed,UNIX_TIMESTAMP(`subscribe_date`),1-blacklist,1,receive_html FROM '.acymailing_table('acajoom_subscribers', false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		if(acymailing_getVar('int', 'acajoom_lists', 0) == 1) $this->_importAcajoomLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('acajoom_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function _importYancLists(){
		$query = 'SELECT `id`, `name`, `description`, `state` as `published` FROM `#__yanc_letters`';
		$yancLists = acymailing_loadObjectList($query, 'id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'yanclist'.implode('\',\'yanclist', array_keys($yancLists)).'\')';
		$joomLists = acymailing_loadObjectList($query, 'alias');

		$listClass = acymailing_get('class.list');
		$time = time();

		foreach($yancLists as $oneList){
			$oneList->alias = 'yanclist'.$oneList->id;
			$oneList->userid = acymailing_currentUserId();

			$yancListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_LIST', '<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM `#__yanc_subscribers` as a ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on a.email = c.email ';
			$querySelect .= 'WHERE a.lid = '.$yancListId.' AND a.state = 1 AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$affected = acymailing_query($queryInsert.$querySelect);

			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $affected, '<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	private function _importccNewsletterNews(){
		$replacements = array();
		$replacements['[unsubscribe link]'] = '{unsubscribe}'.acymailing_translation('UNSUBSCRIBE').'{/unsubscribe}';
		$replacements['[view online link]'] = '{readonline}'.acymailing_translation('VIEW_ONLINE').'{/readonline}';
		$replacements['[sitename]'] = '{config:sitename}';
		$replacements['[name]'] = '{subtag:name}';

		$fields = array();
		$fields['groupid'] = '`groupid`';

		$fields['subject'] = '`name`';
		$fields['body'] = '`body`';
		$fields['published'] = '`enabled`';
		$fields['senddate'] = 'UNIX_TIMESTAMP(`lastsentdate`)';
		$fields['type'] = '"news"';
		$fields['visible'] = '1';
		$fields['html'] = '1';


		$query = 'SELECT ';
		foreach($fields as $as => $select){
			$query .= $select.' as '.$as.',';
		}
		$query = rtrim($query, ',');
		$query .= ' FROM #__ccnewsletter_newsletters WHERE `enabled` >= 0';
		$ccNewsletters = acymailing_loadObjectList($query);

		if(empty($ccNewsletters)) return true;

		$mailClass = acymailing_get('class.mail');
		$lists = array();
		foreach($ccNewsletters as $oneNewsletter){
			$ccList = $oneNewsletter->groupid;
			unset($oneNewsletter->groupid);

			$oneNewsletter->subject = str_replace(array_keys($replacements), $replacements, $oneNewsletter->subject);
			$oneNewsletter->body = str_replace(array_keys($replacements), $replacements, $oneNewsletter->body);
			$acyId = $mailClass->save($oneNewsletter);
			$lists[$acyId] = 'ccnewsletterlist'.$ccList;
		}

		acymailing_enqueueMessage(acymailing_translation_sprintf('NB_IMPORT_NEWSLETTER', '<b>'.count($lists).'</b>'));

		$query = 'SELECT listid, alias FROM #__acymailing_list WHERE alias LIKE "ccnewsletterlist%"';
		$acylists = acymailing_loadObjectList($query, 'alias');

		$equ = array();
		foreach($lists as $mailid => $cclist){
			if(empty($acylists[$cclist])) continue;
			$equ[] = $mailid.','.$acylists[$cclist]->listid;
		}

		if(empty($equ)) return true;
		$query = 'INSERT IGNORE INTO #__acymailing_listmail (`mailid`, `listid`) VALUES ('.implode('),(', $equ).')';
		acymailing_query($query);

		return true;
	}

	private function _importccNewsletterLists(){
		$query = 'SELECT `id`, `group_name` as `name`, `public` as `visible`, `enabled` as `published` FROM '.acymailing_table('ccnewsletter_groups', false).' ORDER BY `ordering` ASC';
		$compLists = acymailing_loadObjectList($query, 'id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'ccnewsletterlist'.implode('\',\'ccnewsletterlist', array_keys($compLists)).'\')';
		$joomLists = acymailing_loadObjectList($query, 'alias');

		$listClass = acymailing_get('class.list');

		foreach($compLists as $oneList){
			$oneList->alias = 'ccnewsletterlist'.$oneList->id;
			$compListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_LIST', '<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',UNIX_TIMESTAMP(b.`sdate`),1 FROM '.acymailing_table('ccnewsletter_g_to_s', false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('ccnewsletter_subscribers', false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.group_id = '.$compListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$affected = acymailing_query($queryInsert.$querySelect);

			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $affected, '<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	private function _importjnewsNews(){
		$replacements = array();
		$replacements['#{tag:unsubscribe}#i'] = '{unsubscribe}'.acymailing_translation('UNSUBSCRIBE').'{/unsubscribe}';
		$replacements['#{tag:subscriptions}#i'] = '{modify}'.acymailing_translation('MODIFY_SUBSCRIPTION').'{/modify}';
		$replacements['#{tag:viewonline[^}]*}#i'] = '{readonline}'.acymailing_translation('VIEW_ONLINE').'{/readonline}';
		$replacements['#{tag:confirm}#i'] = '{confirm}'.acymailing_translation('CONFIRM_SUBSCRIPTION').'{/confirm}';
		$replacements['#{tag:firstname}#i'] = '{subtag:name|part:first}';
		$replacements['#{tag:name}#i'] = '{subtag:name}';
		$replacements['#{tag:email}#i'] = '{subtag:email}';
		$replacements['#{tag:title}#i'] = '{mail:subject}';
		$replacements['#{tag:issuenb}#i'] = '{mail:mailid}';

		$fields = array();
		$fields['id'] = '`id`';
		$fields['subject'] = '`subject`';
		$fields['body'] = '`htmlcontent`';
		$fields['altbody'] = '`textonly`';
		$fields['published'] = '`published`';
		$fields['senddate'] = '`send_date`';
		$fields['created'] = '`createdate`';
		$fields['userid'] = '`author_id`';
		$fields['type'] = '"news"';
		$fields['visible'] = '`visible`';
		$fields['html'] = '`html`';

		$query = 'SELECT ';
		foreach($fields as $as => $select){
			$query .= $select.' as '.$as.',';
		}
		$query = rtrim($query, ',');
		$query .= ' FROM #__jnews_mailings WHERE `mailing_type` = 1';
		$jnewsNewsletters = acymailing_loadObjectList($query);

		if(empty($jnewsNewsletters)) return true;

		$mailClass = acymailing_get('class.mail');
		$mailids = array();
		foreach($jnewsNewsletters as $oneNewsletter){
			$jnewsid = $oneNewsletter->id;
			unset($oneNewsletter->id);

			$oneNewsletter->published = min($oneNewsletter->published, 1);
			$oneNewsletter->subject = preg_replace(array_keys($replacements), $replacements, $oneNewsletter->subject);
			$oneNewsletter->body = preg_replace(array_keys($replacements), $replacements, $oneNewsletter->body);
			$mailids[$jnewsid] = $mailClass->save($oneNewsletter);
		}

		acymailing_enqueueMessage(acymailing_translation_sprintf('NB_IMPORT_NEWSLETTER', '<b>'.count($mailids).'</b>'));

		$query = 'SELECT listid, alias FROM #__acymailing_list WHERE alias LIKE "jnewslist%"';
		$acylists = acymailing_loadObjectList($query, 'alias');

		$query = 'SELECT list_id,mailing_id FROM #__jnews_listmailings WHERE mailing_id IN ('.implode(',', array_keys($mailids)).')';
		$jnewslistmailings = acymailing_loadObjectList($query);

		$equ = array();
		foreach($jnewslistmailings as $jnewsids){
			if(empty($acylists['jnewslist'.$jnewsids->list_id])) continue;
			if(empty($mailids[$jnewsids->mailing_id])) continue;
			$equ[] = $mailids[$jnewsids->mailing_id].','.$acylists['jnewslist'.$jnewsids->list_id]->listid;
		}

		if(empty($equ)) return true;
		$query = 'INSERT IGNORE INTO #__acymailing_listmail (`mailid`, `listid`) VALUES ('.implode('),(', $equ).')';
		acymailing_query($query);

		return true;
	}

	private function _importjnewsLists(){
		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing_table('jnews_lists', false);
		$jnewsLists = acymailing_loadObjectList($query, 'id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'jnewslist'.implode('\',\'jnewslist', array_keys($jnewsLists)).'\')';
		$joomLists = acymailing_loadObjectList($query, 'alias');

		$listClass = acymailing_get('class.list');

		foreach($jnewsLists as $oneList){
			$oneList->alias = 'jnewslist'.$oneList->id;
			$jnewsListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_LIST', '<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',a.subdate,a.unsubdate,1-(2*a.unsubscribe) FROM '.acymailing_table('jnews_listssubscribers', false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('jnews_subscribers', false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$jnewsListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,unsubdate,status) ';

			$affected = acymailing_query($queryInsert.$querySelect);

			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $affected, '<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	private function _importAcajoomLists(){
		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing_table('acajoom_lists', false);
		$acaLists = acymailing_loadObjectList($query, 'id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'acajoomlist'.implode('\',\'acajoomlist', array_keys($acaLists)).'\')';
		$joomLists = acymailing_loadObjectList($query, 'alias');

		$listClass = acymailing_get('class.list');
		$time = time();

		foreach($acaLists as $oneList){
			$oneList->alias = 'acajoomlist'.$oneList->id;
			$acaListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_LIST', '<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM '.acymailing_table('acajoom_queue', false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('acajoom_subscribers', false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$acaListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$affected = acymailing_query($queryInsert.$querySelect);

			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $affected, '<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	function letterman(){
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,UNIX_TIMESTAMP(`subscribe_date`),1,1,1 FROM '.acymailing_table('letterman_subscribers', false);
		$insertedUsers = acymailing_query($query);

		if($insertedUsers == -1){
			$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing_table('letterman_subscribers', false);
			$insertedUsers = acymailing_query($query);
			$query = 'SELECT b.subid FROM '.acymailing_table('letterman_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		}else{
			$query = 'SELECT b.subid FROM '.acymailing_table('letterman_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.subscriber_email = b.email';
		}

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function yanc(){
		$oneSubscriber = acymailing_loadObject('SELECT * FROM #__yanc_subscribers LIMIT 1');
		if(!isset($oneSubscriber->state)){
			acymailing_query("ALTER IGNORE TABLE `#__yanc_subscribers` ADD `state` INT NOT NULL DEFAULT '1'");
		}

		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`, `ip`) SELECT `email`,`name`,`confirmed`,UNIX_TIMESTAMP(`date`),`state`,1,`html`,`ip` FROM '.acymailing_table('yanc_subscribers', false)." WHERE email LIKE '%@%'";
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		if(acymailing_getVar('int', 'yanc_lists', 0) == 1) $this->_importYancLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('yanc_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}


	function vemod(){
		$time = time();
		$query = "INSERT IGNORE INTO ".acymailing_table('subscriber')." (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1,'.$time.',1,1,`mailformat` FROM `#__vemod_news_mailer_users` WHERE `email` LIKE '%@%' ";
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$query = 'SELECT b.subid FROM `#__vemod_news_mailer_users` as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function contact(){
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber')." (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email_to`,`name`,1,'.$time.',1,1,1 FROM `#__contact_details` WHERE email_to LIKE '%@%'";
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$query = 'SELECT b.subid FROM `#__contact_details` as a JOIN '.acymailing_table('subscriber').' as b on a.email_to = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function ccnewsletter(){
		$ccfields = acymailing_getColumns('#__ccnewsletter_subscribers');

		$fields = array();
		$fields['email'] = '`email`';
		$fields['name'] = '`name`';
		$fields['confirmed'] = '`enabled`';
		$fields['created'] = 'UNIX_TIMESTAMP(`sdate`)';
		$fields['enabled'] = '`enabled`';
		$fields['accept'] = 1;
		$fields['html'] = isset($ccfields['plainText']) ? '1-`plainText`' : 1;

		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`'.implode('`,`', array_keys($fields)).'`) SELECT '.implode(',', $fields).' FROM '.acymailing_table('ccnewsletter_subscribers', false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		if(acymailing_getVar('int', 'ccNewsletter_lists', 0) == 1) $this->_importccNewsletterLists();
		if(acymailing_getVar('int', 'ccNewsletter_news', 0) == 1) $this->_importccNewsletterNews();


		$query = 'SELECT b.subid FROM '.acymailing_table('ccnewsletter_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function jnews(){
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,`subscribe_date`, 1-`blacklist`,1,`receive_html` FROM '.acymailing_table('jnews_subscribers', false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		if(acymailing_getVar('int', 'jnews_lists', 0) == 1) $this->_importjnewsLists();
		if(acymailing_getVar('int', 'jnews_news', 0) == 1) $this->_importjnewsNews();

		$query = 'SELECT b.subid FROM '.acymailing_table('jnews_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function nspro(){
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,UNIX_TIMESTAMP(`datetime`), 1,1,1 FROM '.acymailing_table('nspro_subs', false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		if(acymailing_getVar('int', 'nspro_lists', 0) == 1) $this->_importnsproLists();

		$query = 'SELECT b.subid FROM '.acymailing_table('nspro_subs', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	private function _importnsproLists(){

		$query = 'SELECT `id`, `lname` as `name`, 1 as `visible`, `notes` as `description`, `published`, '.intval(acymailing_currentUserId()).' as `userid` FROM '.acymailing_table('nspro_lists', false);
		$nsprolists = acymailing_loadObjectList($query, 'id');

		$query = 'SELECT `listid`, `alias` FROM '.acymailing_table('list').' WHERE `alias` IN (\'nsprolist'.implode('\',\'nsprolist', array_keys($nsprolists)).'\')';
		$joomLists = acymailing_loadObjectList($query, 'alias');

		$listClass = acymailing_get('class.list');

		foreach($nsprolists as $oneList){
			$oneList->alias = 'nsprolist'.$oneList->id;
			$nsproListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_LIST', '<b><i>'.$oneList->name.'</i></b>'));
			}

			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',c.created,1 FROM '.acymailing_table('nspro_subs', false).' as a ';
			$querySelect .= 'JOIN '.acymailing_table('subscriber').' as c on a.email = c.email ';
			$querySelect .= 'WHERE a.mailing_lists LIKE "'.$nsproListId.'" OR a.mailing_lists LIKE "%,'.$nsproListId.',%" OR a.mailing_lists LIKE "'.$nsproListId.',%"  OR a.mailing_lists LIKE "%,'.$nsproListId.'"';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (subid,listid,subdate,status) ';

			$affected = acymailing_query($queryInsert.$querySelect);

			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $affected, '<b><i>'.$oneList->name.'</i></b>'));
		}

		return true;
	}

	function communicator(){
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing_table('communicator_subscribers', false);
		$insertedUsers = acymailing_query($query);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$query = 'SELECT b.subid FROM '.acymailing_table('communicator_subscribers', false).' as a JOIN '.acymailing_table('subscriber').' as b on a.subscriber_email = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();

		return true;
	}

	function civi_import(){
		$this->setciviprefix();
		$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) ';
		$query .= 'SELECT CONVERT(civiemail.email USING utf8),CONVERT(civicontact.`first_name` USING utf8),1,'.time().', 1-`do_not_email`,1 - civicontact.is_opt_out,1 ';
		$query .= 'FROM '.$this->civiprefix.'email as civiemail JOIN '.$this->civiprefix.'contact as civicontact ON civicontact.id = civiemail.contact_id ';
		$query .= 'WHERE civicontact.is_deleted = 0 AND civiemail.is_primary = 1 AND civiemail.email LIKE \'%@%\'';

		return acymailing_query($query);
	}

	function setciviprefix(){
		if(!empty($this->civiprefix)) return;
		$this->civiprefix = 'civicrm_';
		$civifile = ACYMAILING_ROOT.'administrator'.DS.'components'.DS.'com_civicrm'.DS.'civicrm.settings.php';
		if(!defined('CIVICRM_DSN') && file_exists($civifile)) include_once($civifile);
		if(defined('CIVICRM_DSN')){
			$infos = parse_url(CIVICRM_DSN);
			$db = trim($infos['path'], '/');
			if(!empty($db)) $this->civiprefix = '`'.$db.'`.civicrm_';
		}
	}

	function civi(){
		$this->setciviprefix();

		$insertedUsers = $this->civi_import();
		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $insertedUsers));

		$query = 'SELECT b.subid FROM '.$this->civiprefix.'email as a JOIN '.acymailing_table('subscriber').' as b on CONVERT(a.email USING utf8) = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();
	}

	function ldap(){
		$config = acymailing_config();

		acymailing_query("DELETE FROM #__acymailing_config WHERE namekey LIKE 'ldapfield_%'");

		if(!$this->ldap_init()) return false;

		$ldapfields = acymailing_getVar('none', 'ldapfield');
		if(empty($ldapfields)){
			acymailing_enqueueMessage(acymailing_translation('SPECIFYFIELDEMAIL'), 'notice');
			return false;
		}

		$newConfig = new stdClass();

		$this->dispresults = false;
		$newConfig->ldap_import_confirm = $this->forceconfirm = acymailing_getVar('int', 'ldap_import_confirm');
		$newConfig->ldap_generatename = $this->generatename = acymailing_getVar('int', 'ldap_generatename');
		$newConfig->ldap_overwriteexisting = $this->overwrite = acymailing_getVar('int', 'ldap_overwriteexisting');
		$newConfig->ldap_deletenotexists = $this->ldap_deletenotexists = acymailing_getVar('int', 'ldap_deletenotexists');
		if($this->ldap_deletenotexists){
			$subfields = array_keys(acymailing_getColumns('#__acymailing_subscriber'));
			if(!in_array('ldapentry', $subfields)){
				acymailing_query("ALTER TABLE #__acymailing_subscriber ADD COLUMN ldapentry TINYINT UNSIGNED DEFAULT 0");
			}else{
				acymailing_query("UPDATE #__acymailing_subscriber SET ldapentry = 0");
			}

			$this->overwrite = 1;
		}
		$newConfig->ldap_subfield = $this->ldap_subfield = acymailing_getVar('string', 'ldap_subfield');
		if(!empty($this->ldap_subfield)){
			$allValues = acymailing_getVar('none', 'ldap_subcond');
			$allLists = acymailing_getVar('none', 'ldap_sublists');
			$this->ldap_subscribe = array();
			foreach($allValues as $i => $oneValue){
				$oneValue = strtolower(trim($oneValue));
				if(strlen($oneValue) < 1) continue;
				if(isset($this->ldap_subscribe[$oneValue])){
					$this->ldap_subscribe[$oneValue] .= '-'.intval($allLists[$i]);
				}else{
					$this->ldap_subscribe[$oneValue] = intval($allLists[$i]);
				}
				$valcond = 'ldap_subcond_'.$i;
				$vallist = 'ldap_sublists_'.$i;
				$newConfig->$valcond = $allValues[$i];
				$newConfig->$vallist = $allLists[$i];
			}

			acymailing_query("DELETE FROM #__acymailing_config WHERE namekey LIKE 'ldap_subcond%' OR namekey LIKE 'ldap_sublists%'");
		}

		$this->ldap_equivalent = array();
		$this->ldap_selectedFields = array();
		foreach($ldapfields as $oneField => $acyField){
			if(empty($acyField)) continue;
			$configname = 'ldapfield_'.strtolower($oneField);
			$newConfig->$configname = $acyField;
			$this->ldap_equivalent[$acyField] = $oneField;
			$this->ldap_selectedFields[] = $oneField;
		}

		if(!empty($this->ldap_subfield) AND !in_array($this->ldap_subfield, $this->ldap_selectedFields)){
			$this->ldap_selectedFields[] = $this->ldap_subfield;
		}

		$config->save($newConfig);

		if(empty($this->ldap_equivalent['email'])){
			acymailing_enqueueMessage(acymailing_translation('SPECIFYFIELDEMAIL'), 'notice');
			return false;
		}

		$startChars = 'abcdefghijklmnopqrstuvwxyz0123456789_-+&.';

		$nbChars = strlen($startChars);
		$result = true;
		for($i = 0; $i < $nbChars; $i++){
			if(!$this->ldap_import($this->ldap_equivalent['email'].'='.$startChars[$i].'*@*')) $result = false;
		}

		acymailing_enqueueMessage(acymailing_translation_sprintf('ACY_IMPORT_REPORT', $this->totalTry, $this->totalInserted, $this->totalTry - $this->totalValid, $this->totalValid - $this->totalInserted));

		if($this->ldap_deletenotexists){
			$allSubids = acymailing_loadResultArray("SELECT subid FROM #__acymailing_subscriber WHERE ldapentry = 0");
			$subscriberClass = acymailing_get('class.subscriber');
			$nbAffected = $subscriberClass->delete($allSubids);
			acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_DELETE', $nbAffected));
			acymailing_query("ALTER TABLE #__acymailing_subscriber DROP COLUMN ldapentry");
		}

		$this->_displaySubscribedResult();

		return $result;
	}

	function ldap_import($search){
		$searchResult = ldap_search($this->ldap_conn, $this->ldap_basedn, $search, $this->ldap_selectedFields);
		if(!$searchResult){
			acymailing_display('Could not search for elements<br />'.ldap_error($this->ldap_conn), 'warning');
			return false;
		}
		$entries = ldap_get_entries($this->ldap_conn, $searchResult);

		if(empty($entries) || empty($entries['count'])) return true;

		$content = '"'.implode('","', array_keys($this->ldap_equivalent)).'"';
		if($this->ldap_deletenotexists) $content .= ',"ldapentry"';
		if(!empty($this->ldap_subfield)) $content .= ',"listids"';
		$content .= "\n";
		for($i = 0; $i < $entries['count']; $i++){
			foreach($this->ldap_equivalent as $ldapField){
				$fieldVal = isset($entries[$i][$ldapField][0]) ? $entries[$i][$ldapField][0] : '';
				$content .= '"'.$fieldVal.'",';
			}
			if($this->ldap_deletenotexists) $content .= '"1",';
			if(!empty($this->ldap_subfield)){
				static $errorsLists = array();
				if(isset($entries[$i][$this->ldap_subfield][0])){
					$condvalue = strtolower(trim($entries[$i][$this->ldap_subfield][0]));
					if(isset($this->ldap_subscribe[$condvalue])){
						$content .= $this->ldap_subscribe[$condvalue].',';
					}else{
						if(!isset($errorsLists[$condvalue]) AND count($errorsLists) < 5){
							$errorsLists[$condvalue] = true;
							acymailing_enqueueMessage('Could not find a list for the value "'.$condvalue.'" of the field '.$this->ldap_subfield, 'notice');
						}
						$content .= '"",';
					}
				}else{
					$content .= '"",';
				}
			}
			$content = rtrim($content, ',');
			$content .= "\n";
		}
		return $this->_handleContent($content);
	}


	function ldap_init(){
		$config = acymailing_config();
		$newConfig = new stdClass();
		$newConfig->ldap_host = trim(acymailing_getVar('string', 'ldap_host'));
		$newConfig->ldap_port = acymailing_getVar('int', 'ldap_port');
		if(empty($newConfig->ldap_port)) $newConfig->ldap_port = 389;
		$newConfig->ldap_basedn = trim(acymailing_getVar('string', 'ldap_basedn'));
		$this->ldap_basedn = $newConfig->ldap_basedn;
		$newConfig->ldap_username = trim(acymailing_getVar('string', 'ldap_username'));
		$newConfig->ldap_password = trim(acymailing_getVar('string', 'ldap_password'));

		$config->save($newConfig);

		if(empty($newConfig->ldap_host)) return false;

		acymailing_displayErrors();
		$this->ldap_conn = ldap_connect($newConfig->ldap_host, $newConfig->ldap_port);
		if(!$this->ldap_conn){
			acymailing_display('Could not connect to LDAP server : '.$newConfig->ldap_host.':'.$newConfig->ldap_port, 'warning');
			return false;
		}

		ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS, 0);

		if(empty($newConfig->ldap_username)){
			$bindResult = ldap_bind($this->ldap_conn);
		}else{
			$bindResult = ldap_bind($this->ldap_conn, $newConfig->ldap_username, $newConfig->ldap_password);
		}

		if(!$bindResult){
			acymailing_display('Could not bind to the LDAP directory '.$newConfig->ldap_host.':'.$newConfig->ldap_port.' with specified username and password<br />'.ldap_error($this->ldap_conn), 'warning');
			return false;
		}

		acymailing_enqueueMessage('Successfully connected to '.$newConfig->ldap_host.':'.$newConfig->ldap_port, 'success');

		return true;
	}

	function ldap_ajax(){

		if(!$this->ldap_init()) return;

		$config = acymailing_config();

		$searchResult = @ldap_search($this->ldap_conn, trim(acymailing_getVar('string', 'ldap_basedn')), 'mail=*@*', array(), 0, 5);
		if(!$searchResult){
			acymailing_display('Could not search for elements<br />'.ldap_error($this->ldap_conn), 'warning');
			return false;
		}
		$entries = ldap_get_entries($this->ldap_conn, $searchResult);

		$fields = array();
		$dropdown = array();
		$object = new stdClass();
		$object->text = ' - - - ';
		$object->value = 0;
		$dropdown[] = $object;
		foreach($entries as $oneEntry){
			if(!is_array($oneEntry)) continue;
			foreach($oneEntry as $field => $value){
				if(!is_numeric($field)) continue;
				$value = strtolower($value);
				if($value == 'objectclass') continue;
				$fields[$value] = $value;
				$object = new stdClass();
				$object->text = $value;
				$object->value = $value;
				$dropdown[$value] = $object;
			}
		}

		if(empty($fields)){
			acymailing_display('Could not load elements<br />'.ldap_error($this->ldap_conn), 'warning');
			return false;
		}

		$subfields = acymailing_getColumns('#__acymailing_subscriber');

		$acyfields = array();
		$acyfields[] = acymailing_selectOption('', ' - - - ');
		foreach($subfields as $oneField => $typefield){
			if(in_array($oneField, array('subid', 'confirmed', 'enabled', 'key', 'userid', 'accept', 'html', 'created'))) continue;
			$acyfields[] = acymailing_selectOption($oneField, $oneField);
		}

		echo '<div class="onelineblockoptions"><span class="acyblocktitle">'.acymailing_translation('USER_FIELDS').'</span>
<table class="acymailing_table" cellspacing="1">';
		foreach($fields as $oneField){
			echo '<tr><td class="acykey" >'.$oneField.'</td><td>'.acymailing_select($acyfields, 'ldapfield['.$oneField.']', 'size="1"', 'value', 'text', $config->get('ldapfield_'.$oneField)).'</td></tr>';
		}
		echo '</table></div>';

		echo '<div class="onelineblockoptions"><span class="acyblocktitle">'.acymailing_translation('SUBSCRIPTION').'</span>';
		echo 'Subscribe the user based on the values of the field '.acymailing_select($dropdown, 'ldap_subfield', 'size="1"', 'value', 'text', $config->get('ldap_subfield')).':';
		$listClass = acymailing_get('class.list');
		$lists = $listClass->getLists('listid');

		for($i = 0; $i < 5; $i++){
			echo '<br />Subscribe to list '.acymailing_select($lists, 'ldap_sublists['.$i.']', 'class="inputbox" size="1" style="width: 150px;" ', 'listid', 'name', (int)$config->get('ldap_sublists_'.$i)).' if the value is <input style="width: 150px;" type="text" value="'.htmlspecialchars($config->get('ldap_subcond_'.$i), ENT_COMPAT, 'UTF-8').'" name="ldap_subcond['.$i.']" />';
		}
		echo '</div>';

	}

	function zohocrm($action = ''){
		$zohoHelper = acymailing_get('helper.zoho');
		$subscriberClass = acymailing_get('class.subscriber');
		$tableInfos = array_keys(acymailing_getColumns('#__acymailing_subscriber'));
		$config = acymailing_config();
		if(!in_array('zohoid', $tableInfos)){
			$query = 'ALTER TABLE #__acymailing_subscriber ADD COLUMN zohoid VARCHAR(255)';
			acymailing_query($query);
			$query = 'ALTER TABLE `#__acymailing_subscriber` ADD INDEX(`zohoid`)';
			acymailing_query($query);
		}
		if(!in_array('zoholist', $tableInfos)){
			$query = 'ALTER TABLE #__acymailing_subscriber ADD COLUMN zoholist CHAR(1)';
			acymailing_query($query);
		}

		if($action == 'update'){
			$list = $config->get('zoho_list');
			$zohoHelper->authtoken = $authtoken = $config->get('zoho_apikey');
			$zohoHelper->customView = $config->get('zoho_cv');
			$fields = unserialize($config->get('zoho_fields'));
			$confirmedUsers = $config->get('zoho_confirmed');
			$delete = $config->get('zoho_delete');
			$generateName = $config->get('zoho_generate_name', 'fromemail');
			$importnew = $config->get('zoho_importnew', 0);
		}else{
			$list = acymailing_getVar('none', 'zoho_list');
			$fields = acymailing_getVar('none', 'zoho_fields');
			$zohoHelper->authtoken = $authtoken = acymailing_getVar('none', 'zoho_apikey');
			$zohoHelper->customView = acymailing_getVar('none', 'zoho_cv');
			$overwrite = acymailing_getVar('none', 'zoho_overwrite');
			$confirmedUsers = acymailing_getVar('none', 'zoho_confirmed');
			$delete = acymailing_getVar('none', 'zoho_delete');
			$newConfig = new stdClass();
			$newConfig->zoho_fields = serialize($fields);
			$newConfig->zoho_list = $list;
			$newConfig->zoho_apikey = $zohoHelper->authtoken;
			$newConfig->zoho_cv = $zohoHelper->customView;
			$newConfig->zoho_overwrite = $overwrite;
			$newConfig->zoho_confirmed = $confirmedUsers;
			$newConfig->zoho_delete = $delete;
			$newConfig->zoho_generate_name = $generateName = acymailing_getVar('none', 'zoho_generate_name', 'fromemail');
			$newConfig->zoho_importnew = $importnew = acymailing_getVar('none', 'zoho_importnew', 0);
			$newConfig->zoho_importdate = date('Y-m-d H:i:s');
			$config->save($newConfig);
		}

		if($config->get('zoho_overwrite', false)) $this->overwrite = true;
		if(empty($authtoken)){
			acymailing_enqueueMessage('Pleaser enter a valid API key', 'notice');
			return false;
		}

		$this->allSubid = array();
		$indexDec = 200;
		$res = $zohoHelper->sendInfo($list);
		while(!empty($res)){
			$zohoUsers = $zohoHelper->parseXML($res, $list, $fields, $confirmedUsers, $generateName);
			if(empty($zohoUsers) && $zohoHelper->nbUserRead == 0) break;
			$this->_insertUsers($zohoUsers);
			if($zohoHelper->nbUserRead < 200) break; // No further iteration needed
			$zohoUsers = array();
			$zohoHelper->fromIndex = $zohoHelper->fromIndex + $indexDec;
			$zohoHelper->toIndex = $zohoHelper->toIndex + $indexDec;
			if(!empty($zohoHelper->conn)) $zohoHelper->close();
			$res = $zohoHelper->sendInfo($list);
		}
		$this->_subscribeUsers();
		if(acymailing_getVar('int', 'zoho_delete') == '1'){
			$zohoHelper->deleteAddress($this->allSubid, $list);
		}else{
			$query = 'SELECT DISTINCT b.subid FROM #__acymailing_subscriber AS a JOIN #__acymailing_subscriber AS b ON a.zohoid = b.zohoid WHERE a.zohoid IS NOT NULL AND b.subid < a.subid';
			$result = acymailing_loadResultArray($query);
			$subscriberClass->delete($result);
		}
		if(!empty($zohoHelper->conn)) $zohoHelper->close();

		$this->_displaySubscribedResult();
		if(!empty($zohoHelper->error) && acymailing_isDebug()) acymailing_enqueueMessage(acymailing_translation_sprintf($zohoHelper->error), 'notice');
	}

	function sobipro(){
		$config = acymailing_config();

		$sobiproImport = acymailing_getVar('array', 'config', array(), 'POST');
		$newConfig = new stdClass();
		$affectedRows = 0;
		$newConfig->sobipro_import = serialize($sobiproImport);
		$config->save($newConfig);

		foreach($sobiproImport as $oneImport => $oneValue){
			$query = 'SELECT fid, nid FROM #__sobipro_field WHERE fid="'.$oneValue['sobiEmail'].'" OR fid="'.$oneValue['sobiName'].'"';
			$nidResult = acymailing_loadObjectList($query, "fid");
			if(empty($nidResult[$oneValue['sobiEmail']]) OR empty($nidResult[$oneValue['sobiName']])) continue;
			$time = time();
			$query = 'INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT b.baseData AS email, a.baseData AS name, 1 as confirmed, '.$time.' as created, 1 as enabled, 1 as accept, 1 as html FROM #__sobipro_field_data AS a LEFT JOIN #__sobipro_field_data AS b ON a.sid=b.sid WHERE a.`fid` = '.$nidResult[$oneValue["sobiName"]]->fid.' AND b.`fid` = '.$nidResult[$oneValue["sobiEmail"]]->fid.' AND b.baseData LIKE "%@%" AND b.baseData IS NOT NULL AND a.baseData IS NOT NULL ORDER by a.sid ';
			$affected = acymailing_query($query);
			$affectedRows += intval($affected);
		}
		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $affectedRows));
		$query = 'SELECT b.subid FROM `#__sobipro_field_data` as a JOIN '.acymailing_table('subscriber').' as b on a.baseData = b.email';
		$this->allSubid = acymailing_loadResultArray($query);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();
		return true;
	}

	function fbleads(){
		$config = acymailing_config();

		$token = acymailing_getVar('none', 'fbleads_token');
		$adid = acymailing_getVar('none', 'fbleads_adid');
		$formid = acymailing_getVar('none', 'fbleads_formid');
		$mincreated = acymailing_getVar('none', 'fbleads_mincreated');
		$maxcreated = acymailing_getVar('none', 'fbleads_maxcreated');
		$emailfield = acymailing_getVar('none', 'fbleads_email');
		$namefield = acymailing_getVar('none', 'fbleads_name');

		$newConfig = new stdClass();
		$newConfig->fbleads_token = $token;
		$newConfig->fbleads_adid = $adid;
		$newConfig->fbleads_formid = $formid;
		$newConfig->fbleads_mincreated = $mincreated;
		$newConfig->fbleads_maxcreated = $maxcreated;
		$newConfig->fbleads_email = $emailfield;
		$newConfig->fbleads_name = $namefield;

		$config->save($newConfig);

		if(!function_exists('curl_exec')){
			acymailing_enqueueMessage('The curl extension must be enabled on your server to be able to use this import option', 'notice');
			return false;
		}

		if(empty($token)){
			acymailing_enqueueMessage(acymailing_translation('ACY_FBLEADS_ENTER_TOKEN'), 'notice');
			return false;
		}

		if(empty($adid) && empty($formid)){
			acymailing_enqueueMessage(acymailing_translation('ACY_FBLEADS_ENTER_ID'), 'notice');
			return false;
		}

		if(empty($emailfield)){
			acymailing_enqueueMessage('You must at least specify the email field\'s code', 'notice');
			return false;
		}

		$filtering = array();

		if(!empty($mincreated)){
			$mincreated = strtotime($mincreated);
			if(empty($mincreated) || $mincreated == -1){
				acymailing_enqueueMessage(acymailing_translation_sprintf('FIELD_CONTENT_VALID', '"'.acymailing_translation('ACY_FBLEADS_MINCREATED').'"'), 'notice');
			}else{
				$filter = new stdClass();
				$filter->field = "time_created";
				$filter->operator = "GREATER_THAN";
				$filter->value = $mincreated;

				$filtering[] = $filter;
			}
		}

		if(!empty($maxcreated)){
			$maxcreated = strtotime($maxcreated);
			if(empty($maxcreated) || $maxcreated == -1){
				acymailing_enqueueMessage(acymailing_translation_sprintf('FIELD_CONTENT_VALID', '"'.acymailing_translation('ACY_FBLEADS_MAXCREATED').'"'), 'notice');
			}else{
				$filter = new stdClass();
				$filter->field = "time_created";
				$filter->operator = "LESS_THAN";
				$filter->value = $maxcreated;

				$filtering[] = $filter;
			}
		}

		if(empty($formid)) $formid = $adid;
		$url = 'https://graph.facebook.com/v2.10/'.$formid.'/leads?limit=1000000&access_token='.$token;
		if(!empty($filtering)) $url .= '&filtering='.urlencode(json_encode($filtering));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_COOKIESESSION, true);
		$return = curl_exec($curl);

		if(!$return){
			acymailing_enqueueMessage('An unkown error occurred: '.curl_error($curl), 'error');
			curl_close($curl);
			return true;
		}

		curl_close($curl);
		
		$return = json_decode($return, true);
		
		if(!empty($return['error']['message'])){
			acymailing_enqueueMessage($return['error']['message'], 'error');
			return true;
		}

		if(empty($return['data'])) {
			acymailing_enqueueMessage(acymailing_translation('ACY_FBLEADS_NONE'), 'info');
			return true;
		}

		$leads = '';
		$time = time();
		foreach($return['data'] as $oneLead){
			$email = '';
			$name = '';
			foreach($oneLead['field_data'] as $oneField){
				if($oneField['name'] == $emailfield) $email = $oneField['values'][0];
				if(!empty($namefield) && $oneField['name'] == $namefield) $name = $oneField['values'][0];
			}

			$leads .= '('.acymailing_escapeDB($email).(empty($namefield) ? '' : ','.acymailing_escapeDB($name)).','.$time.'),';
			$emails[] = acymailing_escapeDB($email);
		}
		$leads = rtrim($leads, ',');

		$affectedRows = acymailing_query('INSERT IGNORE INTO '.acymailing_table('subscriber').' (`email`'.(empty($namefield) ? '' : ',`name`').',`created`) VALUES '.$leads);

		acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_NEW', $affectedRows));
		$this->allSubid = acymailing_loadResultArray('SELECT subid FROM '.acymailing_table('subscriber').' WHERE `created` = '.$time);
		$this->_subscribeUsers();
		$this->_displaySubscribedResult();
		return true;
	}
}
