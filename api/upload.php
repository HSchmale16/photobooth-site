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

    $src = imagecreatefrompng($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

function saveImage($dataUrl)
{
    global $photoname, $thumbname;
    $dataUrl = str_replace('data:image/png;base64,', '', $dataUrl);
    $dataUrl = str_replace(' ', '+', $dataUrl);
    $data = base64_decode($dataUrl);
    $fileid = uniqid();
    $photoname = IMAGE_DIR . $fileid . '.png';
    file_put_contents($photoname, $data);
    $thumbname = IMAGE_DIR . 'thumb/' . $fileid . '.jpg';
    //imagejpeg(resizeImage($photoname, 120, 90), $thumbname);
    return $fileid;
}

$fileid = saveImage($_POST['image']);
$_POST['image'] = IMAGE_DIR . $fileid . '.png';

// Access the database and save image metadata there
$serv = "localhost";
$dbusr = "user";
$passwd = "password";

try {
    $conn = new PDO("mysql:host=$serv;dbname=photobooth", $dbusr, $passwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /*

     $sql = "INSERT INTO UserLogins(username, machineMac)VALUES(:usr, :mac);";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usr', $usr);
    $stmt->bindParam(':mac', $mac);
    $stmt->execute();
    echo "Success";
    */
} catch (PDOException $e) {
    echo "Fail: " . $e->getMessage();
}