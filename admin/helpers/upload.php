<?php
/* *
 *	BixieMailing
 *  upload.php.php
 *	Created on 11-3-14 3:39
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;

require_once BIX_PATH_ADMIN_HELPERS . '/uploader/UploadHandler.php';

/**
 * Class BixUpload
 * Extends blueimp Uploadhandler
 */
class BixUpload extends UploadHandler {
	/**
	 * @param null $options
	 * @param bool $initialize
	 * @param null $error_messages
	 */
	function __construct ($options = null, $initialize = true, $error_messages = null) {
//		echo BIX_PATH_UPLOADS;
		$uploadPath = BIX_PATH_UPLOADS . '/' . JFactory::getDate()->format('Y-m') . '/';
		$bixoptions = array(
			'script_url' => $this->get_full_url() . '/',
			'upload_dir' => $uploadPath,
			'upload_url' => $this->get_full_url() . '/download',
			'user_dirs' => false,
			'mkdir_mode' => 0755,
			'param_name' => 'files',
			// Set the following option to 'POST', if your server does not support
			// DELETE requests. This is a parameter sent to the client:
			'delete_type' => 'DELETE',
			'access_control_allow_origin' => '*',
			'access_control_allow_credentials' => false,
			'access_control_allow_methods' => array(
				'OPTIONS',
				'HEAD',
				'GET',
				'POST',
				'PUT',
				'PATCH',
				'DELETE'
			),
			'access_control_allow_headers' => array(
				'Content-Type',
				'Content-Range',
				'Content-Disposition'
			),
			// Enable to provide file downloads via GET requests to the PHP script:
			//     1. Set to 1 to download files via readfile method through PHP
			//     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
			//     3. Set to 3 to send a X-Accel-Redirect header for nginx
			// If set to 2 or 3, adjust the upload_url option to the base path of
			// the redirect parameter, e.g. '/files/'.
			'download_via_php' => false,
			// Read files in chunks to avoid memory limits when download_via_php
			// is enabled, set to 0 to disable chunked reading of files:
			'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
			// Defines which files can be displayed inline when downloaded:
			'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
			// Defines which files (based on their names) are accepted for upload:
			'accept_file_types' => '/.+$/i',
			// The php.ini settings upload_max_filesize and post_max_size
			// take precedence over the following max_file_size setting:
			'max_file_size' => null,
			'min_file_size' => 1,
			// The maximum number of files for the upload directory:
			'max_number_of_files' => null,
			// Defines which files are handled as image files:
			'image_file_types' => '/\.(gif|jpe?g|png)$/i',
			// Image resolution restrictions:
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set the following option to false to enable resumable uploads:
			'discard_aborted_uploads' => true,
			// Set to 0 to use the GD library to scale and orient images,
			// set to 1 to use imagick (if installed, falls back to GD),
			// set to 2 to use the ImageMagick convert binary directly:
			'image_library' => 1,
			// Uncomment the following to define an array of resource limits
			// for imagick:
			/*
			'imagick_resource_limits' => array(
				imagick::RESOURCETYPE_MAP => 32,
				imagick::RESOURCETYPE_MEMORY => 32
			),
			*/
			// Command or path for to the ImageMagick convert binary:
			'convert_bin' => 'convert',
			// Uncomment the following to add parameters in front of each
			// ImageMagick convert call (the limit constraints seem only
			// to have an effect if put in front):
			/*
			'convert_params' => '-limit memory 32MiB -limit map 32MiB',
			*/
			// Command or path for to the ImageMagick identify binary:
			'identify_bin' => 'identify',
			'image_versions' => array(
				// The empty image version key defines options for the original image:
				'' => array(
					// Automatically rotate images based on EXIF meta data:
					'auto_orient' => true
				),
				// Uncomment the following to create medium sized images:
				/*
				'medium' => array(
					'max_width' => 800,
					'max_height' => 600
				),
				*/
				'thumbnail' => array(
					// Uncomment the following to use a defined directory for the thumbnails
					// instead of a subdirectory based on the version identifier.
					// Make sure that this directory doesn't allow execution of files if you
					// don't pose any restrictions on the type of uploaded files, e.g. by
					// copying the .htaccess file from the files directory for Apache:
					//'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
					//'upload_url' => $this->get_full_url().'/thumb/',
					// Uncomment the following to force the max
					// dimensions and e.g. create square thumbnails:
					//'crop' => true,
					'max_width' => 80,
					'max_height' => 80
				)
			)
		);
		if ($options) {
			$bixoptions = $options + $bixoptions;
		}
		parent::__construct($bixoptions, $initialize, $error_messages);
	}

	/**
	 * https://github.com/blueimp/jQuery-File-Upload/wiki/PHP-user-directories
	 * @return string
	 */
	protected function get_user_id () {
		@session_start();
		return session_id();
	}

	/**
	 * @param      $file_name
	 * @param null $version
	 * @param bool $direct
	 * @return string
	 */
	protected function get_download_url ($file_name, $version = null, $direct = false) {
		$fullPath = $this->get_upload_path($file_name, $version);
		return JURI::root() . BixHelper::downloadLink($fullPath);
	}
}