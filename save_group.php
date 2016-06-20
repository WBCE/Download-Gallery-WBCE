<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

require_once '../../config.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/'.$dlgmodname.'/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php';
	}
}

// STEP 0:	initialize some variables
$page_id    = ( ( isset($_POST['page_id'])    && is_numeric($_POST['page_id']) )    ? (int) $_POST['page_id']    : NULL );
$section_id = ( ( isset($_POST['section_id']) && is_numeric($_POST['section_id']) ) ? (int) $_POST['section_id'] : NULL );
$group_id   = ( ( isset($_POST['group_id'])   && is_numeric($_POST['group_id']) )   ? (int) $_POST['group_id']   : NULL );

if(!$page_id || !$section_id || !$group_id) {
	header("Location: ".ADMIN_URL."/pages/index.php");
}

if(!isset($_POST['active']) OR !is_numeric($_POST['active'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$active = (int) $_POST['active'];
}

// Include WB admin wrapper script
require_once WB_PATH.'/modules/admin.php';

// update last modified date for the page
$update_when_modified = true; 

// Validate all fields
if($admin->get_post('title') == '') {
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], WB_URL.'/modules/'.$dlgmodname.'/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
} else {
	$title = $admin->add_slashes(strip_tags($admin->get_post('title')));
}

// Update row
$database->query("UPDATE `".TABLE_PREFIX.$tablename."_groups` SET `title` = '$title', `active` = '$active' WHERE `group_id` = '$group_id' AND `page_id` = '$page_id'");

// Check for DB error
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/'.$dlgmodname.'/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();
