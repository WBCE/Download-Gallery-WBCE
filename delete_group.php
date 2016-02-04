<?php

/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

require '../../config.php';

// Get id
$group_id = '';
if(!isset($_GET['group_id']) OR !is_numeric($_GET['group_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$group_id = (int) $_GET['group_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

//move all fiels in group to no group
$database->query("UPDATE `".TABLE_PREFIX."mod_download_gallery_files` SET `group_id` = '0' WHERE `group_id` = '$group_id' AND `page_id` = '$page_id'");

// Delete row
$database->query("DELETE FROM `".TABLE_PREFIX."mod_download_gallery_groups` WHERE `group_id` = '$group_id' AND `page_id` = '$page_id'");

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();