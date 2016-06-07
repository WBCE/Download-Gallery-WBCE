<?php

/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

require '../../config.php';

// Get id
$file_id = '';
$fname = '';
if(!isset($_GET['file_id']) OR !is_numeric($_GET['file_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$file_id = (int) $_GET['file_id'];
}

$update_when_modified = true; // Tells script to update when this page was last updated
require WB_PATH.'/modules/admin.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// STEP 1:	Get post details
$query_details = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_files` WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
if($query_details->numRows() > 0) {
	$get_details = $query_details->fetchRow();
} else {
	$admin->print_error($TEXT['NOT_FOUND'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// get the file information
$fname = $get_details['filename'];
$ext   = $get_details['extension'];

//check for multiple evtries using the same file name
$query_duplicates = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_files` WHERE `filename` = '$fname' AND `extension`='$ext'");
$dups=$query_duplicates->numRows();

//only delete the file if there is 1 database entry (not used on multiple sections)
if($dups==1){
	// STEP 2:	Delete any files if they exists
	$file = WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/' . $fname;
	if(file_exists($file) AND is_writable($file)) {
		unlink($file);
	}
}

// STEP 3:	Delete post
$database->query("DELETE FROM `".TABLE_PREFIX.$tablename."_files` WHERE `file_id` = '$file_id' LIMIT 1");

// STEP 4:	Clean up ordering
require(WB_PATH.'/framework/class.order.php');
$order = new order(TABLE_PREFIX.$tablename.'_files', 'position', 'file_id', 'section_id');
$order->clean($section_id);   // ??????

// STEP 5:	Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/modify_post.php?page_id='.$page_id.'&file_id='.$file_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();