<?php
/**
 * Created by PhpStorm.
 * User: hschmale
 * Date: 3/6/16
 * Time: 2:08 PM
 *
 * extracts the image data, and saves a json file relating the image data
 */

define('IMAGE_DIR', dirname(__FILE__) . '/images/');
require('extern/Template.php');
require('extern/class.phpmailer.php');

function resizeImage($file, $w, $h, $crop = FALSE)
{
    list($width, $height) = getimagesize($file);
    $r = $width / $height;

    if ($w / $h > $r) {
        $newwidth = $h * $r;
        $newheight = $h;
    } else {
        $newheight = $w / $r;
        $newwidth = $w;
    }

    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

function saveImage($dataUrl)
{
    global $photoname, $thumbname;
    $dataUrl = str_replace('data:image/jpeg;base64,', '', $dataUrl);
    $dataUrl = str_replace(' ', '+', $dataUrl);
    $data = base64_decode($dataUrl);
    $fileid = uniqid();
    $photoname = IMAGE_DIR . $fileid . '.jpg';
    file_put_contents($photoname, $data);
    $thumbname = IMAGE_DIR . 'thumb/' . $fileid . '.jpg';
    imagejpeg(resizeImage($photoname, 120, 90), $thumbname);
    return $fileid;
}

$fileid = saveImage($_POST['image']);
$_POST['image'] = IMAGE_DIR . $fileid . '.png';


