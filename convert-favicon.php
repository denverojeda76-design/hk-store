
<?php

$png = __DIR__ . '/public/favicon-64.png';
$ico = __DIR__ . '/public/favicon.ico';

if (!file_exists($png)) {
    exit("ERROR: Run create-favicon.php first.\n");
}

$image = imagecreatefrompng($png);

if ($image === false) {
    exit("ERROR: Cannot open favicon-64.png\n");
}

$width = imagesx($image);
$height = imagesy($image);

if ($width !== 64 || $height !== 64) {
    exit("ERROR: Expected a 64x64 PNG.\n");
}

// Build a Windows ICO file containing a 32-bit RGBA PNG image.
$data = file_get_contents($png);

if ($data === false) {
    exit("ERROR: Cannot read PNG data.\n");
}

$header = pack('vvv', 0, 1, 1);
$entry = pack(
    'CCCCvvVV',
    64, 64, 0, 0,
    1, 32,
    strlen($data),
    22
);

if (file_put_contents($ico, $header . $entry . $data) === false) {
    exit("ERROR: Could not write favicon.ico\n");
}

echo "SUCCESS: Created public/favicon.ico\n";
echo "Size: " . filesize($ico) . " bytes\n";

imagedestroy($image);
