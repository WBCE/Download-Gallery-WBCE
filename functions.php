<?php

/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

global $dlgmodname, $tablename;
$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// General Functions (used in multiple files)

function dlg_download($id,$section_id)
{
    global $database, $dlgmodname, $tablename;

    // find file in DB
    $q = $database->query(sprintf(
        'SELECT * FROM `%s%s_files` WHERE `section_id`=%d AND `file_id`="%d"',
        TABLE_PREFIX,$tablename,$section_id,$id
    ));
    $r = $q->fetchRow();

    if($r)
    {
        $count = $r['dlcount']+1;
	    $database->query(sprintf(
            'UPDATE `%s%s_files` SET `dlcount`=%d WHERE `section_id`=%d AND `file_id`="%d"',
            TABLE_PREFIX,$tablename,$count,$section_id,$id
        ));

        if(!substr_compare($r['link'], WB_URL,0)) {
            // remote
            header('Location: '.$r['link']);
            return; // should never be reached, but just in case...
        }
        else {
            // local
            $path  = WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/'.$r['filename'];

            // open file
            $fh = @fopen( $path, 'rb' );
            if ( ! $fh ) {
        		return false;
        	}

            if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
        	{
        	        header('Content-Type: "application/octet-stream"');
        	        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        	        header('Expires: 0');
        	        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        	        header("Content-Transfer-Encoding: binary");
        	        header('Pragma: public');
        	        header("Content-Length: ".filesize($path));
        	}
        	else
        	{
        	        header('Content-Type: "application/octet-stream"');
        	        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        	        header("Content-Transfer-Encoding: binary");
        	        header('Expires: 0');
        	        header('Pragma: no-cache');
        	        header("Content-Length: ".filesize($path));
        	}
        	fpassthru($fh);
        	fclose($fh);
        }
    }
    return;
}   // end function dlg_download()

// get file type images
function dlg_ext2img($section_id)
{
    global $database, $dlgmodname, $tablename;
    $data       = array();
    $query_ext  = $database->query("SELECT `file_image`,`extensions` FROM `".TABLE_PREFIX.$tablename."_file_ext` WHERE `section_id` = '$section_id'");
    if($query_ext->numRows() > 0) {
        while($ext = $query_ext->fetchRow()) {
            $suffixes = explode(',', $ext['extensions']);
            foreach(array_values($suffixes) as $suffix)
            {
                $data[$suffix] = $ext['file_image'];
            }
        }
    }
    return $data;
}

function dlg_getdlsum($section_id)
{
    global $database, $dlgmodname, $tablename;
    $query = "SELECT SUM(`dlcount`) AS `dlsum` "
           . "FROM `".TABLE_PREFIX.$tablename."_files` AS t1 "
           ;
    $q = $database->query($query);
    if($q->numRows()) {
        $result = $q->fetchRow();
        return $result['dlsum'];
    }
    return 0;
}

/**
 * get the number of active files; this also omits files that are in
 * inactive groups
 *
 * @access public
 * @param  integer  $section_id
 * @return
 **/
function dlg_getfilescount($section_id)
{
    global $database, $dlgmodname, $tablename;
    $query = "SELECT COUNT(`file_id`) AS `count` "
           . "FROM `".TABLE_PREFIX.$tablename."_files` AS t1 "
           . "LEFT OUTER JOIN `".TABLE_PREFIX.$tablename."_groups` AS t2 "
    	   . "ON t1.`group_id`=t2.`group_id` "
           . "WHERE t1.`section_id`=$section_id "
           . "AND t1.`active`=1 "
           ." AND t1.`title` != ''"
           ." AND ( `t1`.`group_id`=0 OR `t2`.`active`=1 )"
           ;
    $q = $database->query($query);
    if($q->numRows()) {
        $result = $q->fetchRow();
        return $result['count'];
    }
    return 0;
}   // end function dlg_getfilescount()

/**
 * get file extensions
 * @access public
 * @param  integer  $fileext_id
 * @param  integer  $section_id
 * @return array
 **/
function dlg_getfileext($fileext_id,$section_id)
{
    global $database, $page_id, $dlgmodname, $tablename;
    $query_fileext = $database->query(
        "SELECT * FROM `".TABLE_PREFIX.$tablename."_file_ext`
         WHERE `fileext_id` = '$fileext_id'
         AND `section_id`   = '$section_id'
         AND `page_id`      = '$page_id'"
    );
    $extdetails = $query_fileext->fetchRow();
    return $extdetails;
}   // end function dlg_getfileext()

/**
 * get groups
 * @access public
 * @param  integer  $section_id
 * @param  boolean  $active_only - false for backend, true for frontend
 **/
function dlg_getgroups($section_id,$active_only=true)
{
    global $database, $dlgmodname, $tablename;
    $data = array('groups'=>array(),'gr2name'=>array());
    $query_groups = $database->query(
        "SELECT * FROM `".TABLE_PREFIX.$tablename."_groups` WHERE `section_id` = '$section_id'"
      . ( $active_only ? " AND active ='1'" : '' )
      . " ORDER BY `position` ASC"
    );
    if($query_groups->numRows() > 0) {
        while($group = $query_groups->fetchRow()) {
            $data['groups'][] = $group;
            $data['gr2name'][$group['group_id']] = $group['title'];
        }
    }
    return array( $data['groups'], $data['gr2name'] );
}

// get settings
function dlg_getsettings($section_id)
{
    global $page_id, $database, $dlgmodname, $tablename;
    $query_content = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_settings` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
    $row = $query_content->fetchRow();
    return $row;
}

// get template subdirs
function dlg_gettpldirs()
{
    $tplbase = realpath(dirname(__FILE__).'/templates/default/frontend');
    $dirlist = array();
    $dh      = opendir($tplbase);
    while (false !== ($filename = readdir($dh))) {
        if(substr($filename,0,1)!='.' && is_dir($tplbase.'/'.$filename) && $filename != 'fonts') {
            $dirlist[] = $filename;
        }
    }
    return $dirlist;
}

// resolve upload error number
function dlg_get_upload_error($error)
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
function dlg_array_orderby()
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

/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return The size of the file referenced by $url, or -1 if the size
 * could not be determined.
 *
 * Note: This will fail behind a proxy!
 */
function dlg_curl_get_file_size($url) {
    if(!function_exists('curl_init')) { return -1; }

    // Assume failure.
    $result = -1;
    $curl   = curl_init( $url );

    // Issue a HEAD request and follow any redirects.
    curl_setopt( $curl, CURLOPT_NOBODY, true );
    curl_setopt( $curl, CURLOPT_HEADER, true );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

    $data = curl_exec( $curl );
    curl_close( $curl );

    if( $data ) {
        $content_length = "unknown";
        $status = "unknown";
        if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
            $status = (int)$matches[1];
        }
        if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
            $content_length = (int)$matches[1];
        }
        // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        if( $status == 200 || ($status > 300 && $status <= 308) ) {
            $result = $content_length;
        }
    }
    return $result;
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
    global $dlgmodname;
    make_dir(WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/');

    // add .htaccess file to /media/download_gallery folder if not already exist
    if(
           !file_exists(WB_PATH . MEDIA_DIRECTORY . '/'.$dlgmodname.'/.htaccess')
	    || (filesize(WB_PATH . MEDIA_DIRECTORY . '/'.$dlgmodname.'/.htaccess') < 90)
    ) {
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

        $handle = fopen(WB_PATH . MEDIA_DIRECTORY . '/'.$dlgmodname.'/.htaccess', 'w');
        fwrite($handle, $content);
        fclose($handle);
        change_mode(WB_PATH . MEDIA_DIRECTORY . '/'.$dlgmodname.'/.htaccess', 'file');
    };
}