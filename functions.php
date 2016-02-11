<?php
/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

// General Functions (used in multiple files)

// get file type images
function dlg_ext2img($section_id)
{
    global $database;
    $data      = array();
    $query_ext = $database->query("SELECT `file_image`,`extensions` FROM `".TABLE_PREFIX."mod_download_gallery_file_ext` WHERE `section_id` = '$section_id'");
    if($query_ext->numRows() > 0) {
        while($ext = $query_ext->fetchRow(MYSQL_ASSOC)) {
            $suffixes = explode(',', $ext['extensions']);
            foreach(array_values($suffixes) as $suffix)
            {
                $data[$suffix] = $ext['file_image'];
            }
        }
    }
    return $data;
}

// get groups
function dlg_getgroups($section_id)
{
    global $database;
    $data = array('groups'=>array(),'gr2name'=>array());
    $query_groups = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_groups` WHERE `section_id` = '$section_id' ORDER BY `position` ASC");
    if($query_groups->numRows() > 0) {
        while($group = $query_groups->fetchRow(MYSQL_ASSOC)) {
            $data['groups'][] = $group;
            $data['gr2name'][$group['group_id']] = $group['title'];
        }
    }
    return array( $data['groups'], $data['gr2name'] );
}

// get settings
function dlg_getsettings($section_id)
{
    global $page_id, $database;
    $query_content = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_settings` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
    $row = $query_content->fetchRow(MYSQL_ASSOC);
    return $row;
}

// resolve upload error number
function get_upload_error($error)
{
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
            $response = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $response = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response = 'The uploaded file was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $response = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response = 'Failed to write file to disk. Introduced in PHP 5.1.0.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response = 'File upload stopped by extension. Introduced in PHP 5.2.0.';
            break;
        default:
            $response = 'Unknown upload error';
            break;
    }
    return $response;
}

// array multisort
function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

// convert file size to formatted size
function human_file_size($size) {
   $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
}

// convert file size; more settings
function hfs($size, $roundup, $decimals) {
   $filesizename = array(" Bytes", " kB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   if (($roundup > 0) && ($decimals == 0)) {
		$addition=.45;
	} else {
		$addition = 0 ;
   }

   if ($size == 0) {
		$retstring = "0 kB";
	} else {
		$retstring = round($size/pow(1024, ($i = floor(log($size, 1024))))+$addition, $decimals) . $filesizename[$i];
   }
	
   // In DE Komma statt Punkt:
   if (LANGUAGE == "DE") {
      //echo "<!-- DEBUG DE -->\n";
      return str_replace('.', ',', $retstring);
   } else {
      //echo "<!-- DEBUG another language -->\n";
      return $retstring;
   }
}

// create download dir and .htaccess file
function make_dl_dir() {
   make_dir(WB_PATH.MEDIA_DIRECTORY.'/download_gallery/');

   // add .htaccess file to /media/download_gallery folder if not already exist
   if (!file_exists(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess')
	  or (filesize(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess') < 90))
   {
	  // create a .htaccess file to prevent execution of PHP, HMTL files
	  $content = <<< EOT
<Files .htaccess>
	order allow,deny
	deny from all
</Files>

<Files ~ "\.(php|pl)$">  
ForceType text/plain
</Files>

Options -Indexes -ExecCGI
EOT;

	  $handle = fopen(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess', 'w');
	  fwrite($handle, $content);
	  fclose($handle);
	  change_mode(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess', 'file');
   };
}

?>