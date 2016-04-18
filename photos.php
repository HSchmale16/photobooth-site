<html>
<head>
    <title>Party Photo Index</title>
    <link rel="stylesheet" href="/extern/bootstrap.min.css">
</head>
<body>
    <?php
    /**
     * Created by PhpStorm.
     * User: hschmale
     * Date: 4/17/16
     * Time: 3:33 PM
     */
    define(IMAGE_DIR, __DIR__.'/api/images');

    $dir = new DirectoryIterator(IMAGE_DIR);
    $i = 0;
    foreach ($dir as $fileinfo) {
        if($i % 2 == 0){
            echo "<div class='row'>";
        }
        if ((!$fileinfo->isDot()) and $fileinfo->isFile()
            and $fileinfo->getExtension() == 'jpg'
        ) {
            $imgId = basename($fileinfo->getFilename(), '.jpg');
            echo '<div class="col-lg-6">';
            echo "<img class='img-responsive' src='/api/images/$imgId.jpg'>";
            echo "</div>\n";
        }
        $i++;
        if($i % 2 == 0){
            echo "</div>\n";
        }
    }

    ?>
</body>
</html>