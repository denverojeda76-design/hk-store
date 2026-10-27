
<?php

$source = __DIR__ . '/public/images/logo.png';
$output = __DIR__ . '/public/favicon.ico';
$backup = __DIR__ . '/public/favicon.ico.empty-backup';

if (!extension_loaded('gd')) {
    exit("ERROR: PHP GD is not enabled.\n");
}

if (!file_exists($source)) {
    exit("ERROR: H&K logo not found: public/images/logo.png\n");
}

if (!file_exists($backup) && file_exists($output) && filesize($output) === 0) {
    if (!copy($output, $backup)) {
        exit("ERROR: Could not create favicon backup.\n");
    }
}

$image = imagecreatefrompng($source);

if ($image === false) {
    exit("ERROR: Could not open the H&K logo.\n");
}

$size = 64;
$resized = imagecreatetruecolor($size, $size);
imagesavealpha($resized, true);

$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
imagefill($resized, 0, 0, $transparent);

$width = imagesx($image);
$height = imagesy($image);
$scale = min($size / $width, $size / $height);

$newWidth = max(1, (int) round($width * $scale));
$newHeight = max(1, (int) round($height * $scale));

$destX = (int) (($size - $newWidth) / 2);
$destY = (int) (($size - $newHeight) / 2);

imagecopyresampled(
    $resized,
    $image,
    $destX,
    $destY,
    0,
    0,
    $newWidth,
    $newHeight,
    $width,
    $height
);

$temp = __DIR__ . '/public/favicon-64.png';

if (!imagepng($resized, $temp)) {
    exit("ERROR: Could not create temporary PNG.\n");
}

imagedestroy($image);
imagedestroy($resized);

echo "Created public/favicon-64.png\n";
echo "Next, we need to convert this PNG into a real ICO file.\n";
