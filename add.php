<?php

/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

include_once dirname(__FILE__).'/functions.php';

// STEP 0:	initialize some variables
$page_id    = (int) $page_id;
$section_id = (int) $section_id;
$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

$query = "INSERT INTO `%s%s_settings` "
       . "(`section_id`,`page_id`) "
       . "VALUES ('$section_id','$page_id')"
       ;

$database->query(sprintf($query,TABLE_PREFIX,$tablename));

$image_array = "jpg,jpeg,jpe,jfif,gif,bmp,dib,png,tif,tiff,wmf,emf,psp";
$movie_array = "mov,wma";
$music_array = "mp3,mid,rmi,midi,wav,snd,au,aif,aiff,ra,ram,rm,ogg";
$docs_array  = "doc,dot,docx,dotx";
$pres_array  = "ppa,pps,ppt,pptx";
$excel_array = "xla,xlb,xlc,xld,xlk,xll,xlm,xls,xlsx,xlt,xlv,xlw,xlxml";
$compr_array = "arj,cab,lzh,tar,tz,zip,gz,7z";
$pdf_array   = "pdf";
$txt_array   = "txt,bat,ini,log";

$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','images','image.gif','$image_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','movies','movie.gif','$movie_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','music','music.gif','$music_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','documents','document.gif','$docs_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','presentations','presentation.gif','$pres_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','spreadsheets','spreadsheet.gif','$excel_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','compressions','compression.gif','$compr_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','pdf','pdf.gif','$pdf_array')");
$database->query("INSERT INTO `".TABLE_PREFIX.$tablename."_file_ext` (`section_id`,`page_id`,`file_type`,`file_image`,`extensions`) VALUES ('$section_id','$page_id','text','text.gif','$txt_array')");
