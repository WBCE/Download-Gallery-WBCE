<?php

/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

require '../../config.php';
require_once WB_PATH.'/framework/functions.php';
require WB_PATH.'/framework/class.wb.php';

if(LANGUAGE_LOADED) {
	require_once WB_PATH.'/modules/download_gallery/languages/EN.php' ;
		if(!file_exists(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
	} else {
		require_once WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php';
	}
}

$file = ''; $dlcount = '';
if(!isset($_GET['file']) OR !is_numeric($_GET['file'])) {
	header('Location: ../index.php');
} else {
	$file = (int) $_GET['file'];
}
if(!isset($_GET['id']) OR !is_numeric($_GET['id'])) {
	header('Location: ../index.php');
} else {
	$prove = (int) $_GET['id'];
}
if(!isset($_GET['sid']) OR !is_numeric($_GET['sid'])) {
	header('Location: ../index.php');
} else {
	$sid = (int) $_GET['sid'];
}

$qs = "SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_files` WHERE `file_id` = '$file' AND `modified_when` = '$prove'";
$query_files = $database->query($qs);
if ($query_files->numRows()==1) {
	$fetch_file = $query_files->fetchRow();
} else {
	header('Location: ../index.php');
}

$qs = "SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_settings` WHERE `section_id` = $sid";
$query_files = $database->query($qs);
if ($query_files->numRows()==1) {
	$fetch_set = $query_files->fetchRow();
	$pushmode = $fetch_set['pushmode'];  // offer "File Save" dialog or show in browser?
} else {
	header('Location: ../index.php');
}

$query_page=$database->query("SELECT * FROM `".TABLE_PREFIX."pages` WHERE `page_id`='".$fetch_file['page_id']."'");
$page_info=$query_page->fetchRow();

// check download permissions:
$dl_allowed = false;
if ($page_info['visibility'] == 'public' OR $page_info['visibility'] == "hidden") {
	$dl_allowed = true;
}
	
if (!$dl_allowed) {
	if ((isset($_SESSION['USER_ID']) AND $_SESSION['USER_ID'] != "" AND is_numeric($_SESSION['USER_ID']))
	AND ($page_info['visibility'] == "registered" OR  $page_info['visibility'] == "private")) {
		$groups = explode(",", $page_info['viewing_groups']);
		foreach (split(",", $_SESSION['GROUPS_ID']) as $cur_group_id) {
			if (in_array($cur_group_id, $groups)) {
				$dl_allowed = true;
			}
		}
	}
}

if ($dl_allowed) {	
    // increment download counter:
	$dlcount = $fetch_file['dlcount']+1;
	$queryu="UPDATE `".TABLE_PREFIX."mod_download_gallery_files` SET `dlcount` = '$dlcount' WHERE `file_id` = '$file'";
	$database->query($queryu);

	// deliver the file:
	$orgfile = $fetch_file['link'];
	if ($pushmode == 1) {
		$type = ($fetch_file['extension'] != '') ? $fetch_file['extension'] : "application/octet-stream";
		$filesize = $fetch_file['size'];
		$fn = basename($orgfile);
		$dlfile = str_replace(WB_URL, WB_PATH, $orgfile);
		header("Content-Type: $type");
		header("Content-Disposition: attachment; filename=\"$fn\"");
		header("Content-Length: $filesize");
		readfile($dlfile);
	} else {
		header('Location: '.$orgfile);	
	}
} else {
	echo "No access!";
}