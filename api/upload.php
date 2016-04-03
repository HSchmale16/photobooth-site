<?php
/**
 * Created by PhpStorm.
 * User: hschmale
 * Date: 3/6/16
 * Time: 2:08 PM
 *
 * extracts the image data, and saves a json file relating the image data
 *
 * The POST data has the following fields:
 * name: Name of the person uploading the picture
 * email: Who to send the data invite email
 * notes: Any details about picture upload user wants to include
 * image: dataURL of the image as jpeg. This should be full size.
 */

define('IMAGE_DIR', dirname(__FILE__) . '/images/');
define('THUMB_DIR', dirname(__FILE__) . '/images/thumb/');
define('LATEX_DIR', dirname(__FILE__) . '/latex/');

require('extern/Template.php');
require('extern/class.phpmailer.php');

class UploadDB extends SQLite3 {
    function __construct()
    {
        $this->open('upload.sqlite');
    }
}

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
    // save the thumbnail
    $thumbname = IMAGE_DIR . 'thumb/' . $fileid . '.jpg';
    imagejpeg(resizeImage($photoname, 120, 90), $thumbname);
    // return name of any files related to this upload minus extension and
    // directory path.
    return $fileid;
}

function addUploadToDatabase($name, $email, $photo, $thumb, $templ, $notes){
    $db = new UploadDB();
    if(!$db){
        echo $db->lastErrorMsg();
    }
    $sql = <<< EOF
    insert into Upload(name, email, photoPath, thumbPath, templatePath, notes) VALUES
    (?, ?, ?, ?, ?, ?);
EOF;

    // insert the data
    $st = $db->prepare($sql);
    $st->bindParam(1, $name, SQLITE3_TEXT);
    $st->bindParam(2, $email, SQLITE3_TEXT);
    $st->bindParam(3, $photo, SQLITE3_TEXT);
    $st->bindParam(4, $thumb, SQLITE3_TEXT);
    $st->bindParam(5, $templ, SQLITE3_TEXT);
    $st->bindParam(6, $notes, SQLITE3_TEXT);
    $st->execute();

    // perform cleanup
    $st->close();
    $db->close();
}

$fileid = saveImage($_POST['image']);
$imageName = IMAGE_DIR . $fileid . '.png';
$thumbName = THUMB_DIR . $fileid . '.jpg';

addUploadToDatabase($_POST['name'], $_POST['email'], $imageName, $thumbName,
    null, $_POST['notes']);
