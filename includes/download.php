<?php

if(isset($_GET['file'])) {
	$name = isset($_GET['filename']) ? $_GET['filename'] : '';
	force_download($_GET['file'], $name);
}

function force_download ($file, $name='') {
	
	$file = trim(str_replace( array('../','..\\'), '', $file));
	if(strtolower(substr($file,0,7))=='http://') {
		$file = dirname(__FILE__).'/../../../temp/'.basename($file);
	} else $file = dirname(__FILE__).'/../../../'.dirname($file).'/'.trim(basename($file));

	
	//$file_extension = strtolower(strrchr($file,'.'));
	$file_extension = pathinfo($file, PATHINFO_EXTENSION);
	if ( $file_extension=='php' || !file_exists($file ) ) {
		exit('Invalid');
	};


	if(empty($name)) $name = basename($file);
	$filesize = filesize($file);
	
	// required for IE, otherwise Content-disposition is ignored
	if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
	
	switch( $file_extension ) {
	  case '.pdf': $mimetype='application/pdf'; break;
	  case '.mri': $mimetype='application/octet-stream'; break;
	  case '.exe': $mimetype='application/octet-stream'; break;
	  case '.zip': $mimetype='application/zip'; break;
	  case '.doc': $mimetype='application/msword'; break;
	  case '.xls': $mimetype='application/vnd.ms-excel'; break;
	  case '.ppt': $mimetype='application/vnd.ms-powerpoint'; break;
	  case '.gif': $mimetype='image/gif'; break;
	  case '.png': $mimetype='image/png'; break;
	  case '.jpeg':
	  case '.jpg': $mimetype='image/jpg'; break;
	  default: $mimetype='application/force-download';
	}

	// Start sending headers
	header('Pragma: public'); // required
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private',false); // required for certain browsers
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: ' . $mimetype);
	header('Content-Length: ' . $filesize);
	header('Content-Disposition: attachment; filename="' . $name . '";' );

	// Send data
	readfile($file);
	exit();
}




    