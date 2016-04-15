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
        $this->open('./upload.sqlite');
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
        die($db->lastErrorMsg());
    }
    $sql = <<< EOF
    insert into Upload(name, email, photoPath, thumbPath, templatePath, notes) VALUES
    ('$name', '$email', '$photo', '$thumb', '$templ', '$notes');
EOF;

    $db->exec($sql);

    $db->close();
}

$fileid = saveImage($_POST['image']);
$imageName = IMAGE_DIR . $fileid . '.jpg';
$thumbName = THUMB_DIR . $fileid . '.jpg';
$latexFile = LATEX_DIR.$fileid.'.tex';
$pdfFile = LATEX_DIR.$fileid.'.pdf';

//addUploadToDatabase($_POST['name'], $_POST['email'], $imageName, null,
//    $latexFile, $_POST['notes']);

// Generate the latex template.
$TemplateKeys = array(
    'groupImage' => $imageName
);
$templ = new Text_Template('assets/template.tex', '<$', '$>');
$templ->setVar($TemplateKeys);
$templ->renderTo($latexFile);

// Latex uses relative pathes and must run in the directory as the generated materials.
chdir(LATEX_DIR);
// Now generate the pdf
exec("latexmk -pdf $latexFile && latexmk -c $latexFile");

// email the user
$mail = new PHPMailer;
$mail->setFrom('photobooth@henryschmale.org');
foreach(explode(',', $_POST['email']) as $address) {
    echo "$address\n";
    $mail->addAddress($address);
}
$mail->addAttachment($pdfFile);
$mail->addAttachment($imageName);

$mail->Subject = 'Newspaper Photobooth Email';
$mail->Body = <<< END_OF_STRING
Here is the generated newspaper from the photobooth at the party today.
It also includes the picture that was taken, as a jpeg. Thank you for
using the photobooth created by Henry Schmale.

https://github.com/HSchmale16/photobooth-site
END_OF_STRING;

if(!$mail->send()){
    echo "Msg Not Sent\n";
    die($mail->ErrorInfo);
}else{
    echo "Sent Email";
}

// send the email to be printed
$printMail = new PHPMailer;
$printMail->addAddress('eoa7594ait86@hpeprint.com');
$printMail->Subject = 'Newpaper Photobooth Email';
$printMail->addAttachment($pdfFile);
if($printMail->send()){
    die($printMail->ErrorInfo);
}