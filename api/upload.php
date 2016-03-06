<?php
/**
 * Created by PhpStorm.
 * User: hschmale
 * Date: 3/6/16
 * Time: 2:08 PM
 */

define('UPLOAD_DIR', dirname(__FILE__).'/images/');

/**
 * @param $dataUrl
 */
function saveImage($dataUrl) {
    $dataUrl = str_replace('data:image/png;base64,', '', $dataUrl);
    $dataUrl = str_replace(' ', '+', $dataUrl);
    $data = base64_decode($dataUrl);
    $file = UPLOAD_DIR.uniqid().'.png';
    $success = file_put_contents($file, $data);
    return $file;
}

saveImage($_POST['image']);

