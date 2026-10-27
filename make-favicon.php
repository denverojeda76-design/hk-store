<?php

$source = __DIR__ . '/public/images/logo.png';
$output = __DIR__ . '/public/favicon.ico';

if (!extension_loaded('gd')) {
    exit("ERROR: GD extension is not enabled.\n");
}

if (!file_exists($source)) {
    exit("ERROR: H&K logo not found: {$source}\n");
}

$image = imagecreatefrompng($source);

if (!$image) {
    exit("ERROR: Could not read logo.png.\n");
}

$size = 64;
$icon = imagecreatetruecolor($size, $size);

imagealphablending($icon, false);
imagesavealpha($icon, true);

$transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
imagefill($icon, 0, 0, $transparent);

imagealphablending($icon, true);
imagecopyresampled(
    $icon,
    $image,
    0, 0, 0, 0,
    $size, $size,
    imagesx($image), imagesy($image)
);

$temp = __DIR__ . '/public/favicon-check.png';

if (!imagepng($icon, $temp)) {
    exit("ERROR: Could not create temporary icon image.\n");
}

imagedestroy($image);
imagedestroy($icon);

echo "SUCCESS: Created temporary 64x64 PNG icon at {$temp}\n";
echo "The existing favicon.ico has not been changed.\n";
