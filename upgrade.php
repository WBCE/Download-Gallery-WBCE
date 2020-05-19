<?php
/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

require_once WB_PATH.'/framework/functions.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// Remove old search entries
$database->query("DELETE FROM `".TABLE_PREFIX."search` WHERE `name` = 'module' AND `value` = '$dlgmodname'");
$database->query("DELETE FROM `".TABLE_PREFIX."search` WHERE `extra` = '$dlgmodname'");

// Add new search entries
// Module query info
$field_info = array();
$field_info['page_id'] = 'page_id';
$field_info['title'] = 'page_title';
$field_info['link'] = 'link';
$field_info['description'] = 'description';
$field_info['modified_when'] = 'modified_when';
$field_info['modified_by'] = 'modified_by';
$field_info = serialize($field_info);

$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`,`value`,`extra`) VALUES ('module', '$dlgmodname', '$field_info')");

// Search query start
$query_start_code = "SELECT [TP]pages.page_id
						  , [TP]pages.page_title
						  , [TP]pages.link
						  , [TP]pages.description
						  , [TP]pages.modified_when
						  , [TP]pages.modified_by 
					 FROM [TP]".$tablename."_files,[TP]".$tablename."_groups, [TP]pages
					 WHERE 
					";
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`,`value`,`extra`) VALUES ('query_start', '$query_start_code', '$dlgmodname')");

// Search query body
$query_body_code = " [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.title [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.description [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_groups.page_id AND [TP]".$tablename."_groups.title [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.filename [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
				   ";	
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`,`value`,`extra`) VALUES ('query_body', '$query_body_code', '$dlgmodname')");

// Search query end
$query_end_code = "";	
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`,`value`,`extra`) VALUES ('query_end', '$query_end_code', '$dlgmodname')");

// Insert blank row (there needs to be at least on row for the search to work)
$query_0=$database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_files` WHERE `section_id`='0' AND `page_id`='0'");
	if($query_0->numRows() == 0) {
		$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_files` (`section_id`,`page_id`) VALUES ('0', '0')");
	}
	
$query_0=$database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_settings` WHERE `section_id`='0' AND `page_id`='0'");
	if($query_0->numRows() == 0) {
		$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_settings` (`section_id`,`page_id`) VALUES ('0', '0')");
	}

$query_0=$database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_groups` WHERE `section_id`='0' AND `page_id`='0'");
	if($query_0->numRows() == 0) {
		$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_groups` (`section_id`,`page_id`) VALUES ('0', '0')");
	}

$query_0=$database->query("ALTER TABLE `".TABLE_PREFIX.$tablename."_settings` ADD COLUMN `position` INT(11) NOT NULL DEFAULT 0 AFTER `ordering`");
$query_0=$database->query("ALTER TABLE `".TABLE_PREFIX.$tablename."_settings` DROP COLUMN `extposition`");
$query_0=$database->query("ALTER TABLE `".TABLE_PREFIX.$tablename."_settings` ADD COLUMN `use_dir` ENUM('Y','N') NOT NULL DEFAULT 'Y' AFTER `ordering`");
$query_0=$database->query("ALTER TABLE `".TABLE_PREFIX.$tablename."_settings` ADD COLUMN `offer_download` ENUM('Y','N') NOT NULL DEFAULT 'Y' AFTER `use_dir`");

// update .htaccess file in /media/download_gallery folder 
include_once WB_PATH.'/modules/'.$dlgmodname.'/functions.php';
make_dl_dir();
