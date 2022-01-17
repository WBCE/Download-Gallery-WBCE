<?php
/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

$mod_dl_gallery = '
    CREATE TABLE IF NOT EXISTS `%s%s_files` (
    	`file_id` INT(11) NOT NULL AUTO_INCREMENT,
    	`section_id` INT(11) NOT NULL DEFAULT \'0\',
    	`page_id` INT(11) NOT NULL DEFAULT \'0\',
    	`group_id` INT(11) NOT NULL DEFAULT \'0\',
    	`active` INT(11) NOT NULL DEFAULT \'0\',
    	`position` INT(11) NOT NULL DEFAULT \'0\',
    	`title` VARCHAR(255) NOT NULL DEFAULT \'\',
    	`link` TEXT NULL,
    	`filename` VARCHAR(250) NOT NULL DEFAULT \'\',
    	`extension` VARCHAR(250) NOT NULL DEFAULT \'\',
    	`description` TEXT NULL,
    	`modified_when` INT(11) NOT NULL DEFAULT \'0\',
    	`modified_by` INT(11) NOT NULL DEFAULT \'0\',
    	`dlcount` INT(11) NOT NULL DEFAULT \'0\',
    	`size` INT(11) NOT NULL DEFAULT \'0\',
    	`released` INT(11) NOT NULL DEFAULT \'0\',
    	PRIMARY KEY (`file_id`)
    );';
$database->query(sprintf($mod_dl_gallery,TABLE_PREFIX,$tablename));

$mod_dl_gallery = '
    CREATE TABLE IF NOT EXISTS `%s%s_settings` (
    	`section_id` INT(11) NOT NULL DEFAULT \'0\',
    	`page_id` INT(11) NOT NULL DEFAULT \'0\',
    	`files_per_page` INT(11) NOT NULL DEFAULT \'0\',
    	`file_size_roundup` INT(11) NOT NULL DEFAULT \'0\',
    	`file_size_decimals` INT(11) NOT NULL DEFAULT \'0\',
    	`ordering` TINYINT(3) NOT NULL DEFAULT \'0\',
    	`extordering` TINYINT(3) NOT NULL DEFAULT \'0\',
    	`search_filter` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',
    	`tpldir` VARCHAR(50) NOT NULL DEFAULT \'tableview\',
    	`tplcss` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',
        `use_dir` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',
        `offer_download` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',
    	PRIMARY KEY (`section_id`)
    );';
$database->query(sprintf($mod_dl_gallery,TABLE_PREFIX,$tablename));

$mod_dl_gallery = '
    CREATE TABLE IF NOT EXISTS `%s%s_groups` (
    	`group_id` INT(11) NOT NULL AUTO_INCREMENT,
    	`section_id` INT(11) NOT NULL DEFAULT \'0\',
    	`page_id` INT(11) NOT NULL DEFAULT \'0\',
    	`position` INT(11) NOT NULL DEFAULT \'0\',
    	`active` INT(11) NOT NULL DEFAULT \'0\',
    	`title` VARCHAR(255) NOT NULL DEFAULT \'\',
    	PRIMARY KEY (`group_id`)
);';
$database->query(sprintf($mod_dl_gallery,TABLE_PREFIX,$tablename));

$mod_dl_gallery = '
    CREATE TABLE IF NOT EXISTS `%s%s_file_ext` (
    	`fileext_id` INT(11) NOT NULL AUTO_INCREMENT,
    	`section_id` INT(11) NOT NULL DEFAULT \'0\',
    	`page_id` INT(11) NOT NULL DEFAULT \'0\',
    	`file_type` VARCHAR(250) NOT NULL DEFAULT \'\',
    	`file_image` VARCHAR(250) NOT NULL DEFAULT \'\',
    	`extensions` TEXT NOT NULL,
    	PRIMARY KEY (`fileext_id`)
    );';
$database->query(sprintf($mod_dl_gallery,TABLE_PREFIX,$tablename));

// Insert info into the search table
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
			FROM [TP]".$tablename."_files, [TP]".$tablename."_groups,[TP]pages
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
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_files` (`section_id`,`page_id`) VALUES ('0', '0')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_settings` (`section_id`,`page_id`) VALUES ('0', '0')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_groups` (`section_id`,`page_id`) VALUES ('0', '0')");

//Add folder for the files
require_once WB_PATH.'/framework/functions.php';
include_once WB_PATH.'/modules/'.$dlgmodname.'/functions.php';
make_dl_dir();