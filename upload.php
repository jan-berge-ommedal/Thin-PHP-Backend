<?

include "util/all.php";

function resizeImage($file, $targetWidth, $targetHeight)
{
    $contentType = getContentType();
    switch ($contentType) {
        case "image/jpeg":
            $createImageFunction = "imagecreatefromjpeg";
            $saveImageFunction = "imagejpeg";
            $compressionLevel = 100;
            $transparency = false;
            break;
        case "image/png":
            $createImageFunction = "imagecreatefrompng";
            $saveImageFunction = "imagepng";
            $compressionLevel = 9;
            $transparency = true;
            break;
        case "image/gif":
            $createImageFunction = 'imagecreatefromgif';
            $saveImageFunction = 'imagegif';
            $compressionLevel = 100; // ?
            $transparency = true; // ?
            break;
        default:
            throw Exception('Unknown image type.');
    }


    debug("a");

    $imageSize = getimagesize($file);

    debug("b");

    $width = min($imageSize[0], 2560);
    $height = min($imageSize[1], 2560);


    $sourceImage = $createImageFunction($file);
    $destinationImage = imagecreatetruecolor($targetWidth, $targetHeight);

    if($transparency){
        imagealphablending($destinationImage, false);
        imagesavealpha($destinationImage, true);
        $transparent = imagecolorallocatealpha($destinationImage, 255, 255, 255, 127);
        imagefilledrectangle($destinationImage, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    imagecopyresampled(
        $destinationImage,
        $sourceImage,
        0, 0, 0, 0, // dst + src x/y
        $targetWidth,
        $targetHeight,
        $width,
        $height
    );

    $saveImageFunction($destinationImage, $file, $compressionLevel);
    debug("resized image from {$width} x {$height} to {$targetWidth} x {$targetHeight}");
}


function getData()
{
    $tmpName = $_FILES['file']['tmp_name'];
    assertNotEmpty($tmpName, "missing file");

    $imageWidth = getParameter("imageWidth");
    $imageHeight = getParameter("imageHeight");
    $contentType = getContentType();

    debug($contentType);
    if (($contentType == "image/jpeg" || $contentType == "image/x-png" ||  $contentType == "image/png" ||  $contentType == "image/gif")
        && !empty($imageWidth)
        && !empty($imageHeight)
    ) {
        resizeImage($tmpName, $imageWidth, $imageHeight);
    }

    $fp = fopen($tmpName, 'r');
    $length = filesize($tmpName);
    debug("File size: {$length}");
    $content = fread($fp, $length);
    fclose($fp);
    return $content;
}

function getContentType()
{
    return $_FILES['file']['type'];
}

function getName()
{
    return $_FILES['file']['name'];
}

$performPost = function () {
    validateUser();
    $id = getParameter(PARAMETER_ID, PARAMETER_REQUIRED);
    $dataType = getParameter(PARAMETER_DATA_TYPE, PARAMETER_REQUIRED);
    $contentType = getContentType();
    $data = getData();
    $name = getName();

    $insert = withStatement("SELECT id FROM DATA WHERE id=?", function ($statement) use ($id) {
        $statement->bind_param("s", $id);
        return countRows($statement) == 0;
    });

    if ($insert) {
        withStatement("INSERT INTO DATA(id,dataType,contentType,data, name) VALUES(?,?,?,?,?)", function ($statement) use ($id, $dataType, $contentType, $data, $name) {
            $statement->bind_param("sssss", $id, $dataType, $contentType, $data, $name);
            executeStatement($statement);
        });
    } else {
        withStatement("UPDATE DATA SET data=?,dataType=?,contentType=?,name=? WHERE id=?", function ($statement) use ($id, $dataType, $contentType, $data, $name) {
            $statement->bind_param("sssss", $data, $dataType, $contentType, $name, $id);
            executeStatement($statement);
        });
    }

};


validateUser();
handleRequest(array(
    "POST" => $performPost
));


?>