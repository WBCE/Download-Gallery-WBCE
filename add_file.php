<?php
/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

require '../../config.php';
require WB_PATH.'/modules/admin.php';				// Include WB admin wrapper script
require WB_PATH.'/framework/class.order.php';		// Include the ordering class

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));

// STEP 0:	initialize some variables
$page_id    = (int) $page_id;
$section_id = (int) $section_id;
$file_id    = '';
$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// Get new order
$order = new order(TABLE_PREFIX.$tablename.'_files', 'position', 'file_id', 'section_id');
$position = $order->get_new($section_id);

// Insert new row into database
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_files` (`section_id`,`page_id`,`position`,`active`) VALUES ('$section_id','$page_id','$position','1')");

// Get the id
$file_id = $database->get_one("SELECT LAST_INSERT_ID()");

if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
}

// Print admin footer
$admin->print_footer();