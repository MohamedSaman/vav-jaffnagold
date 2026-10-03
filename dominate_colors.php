<?php
// dominate_colors.php
$imagePath = 'public/images/jg.png';
if (!file_exists($imagePath)) {
    echo "Image not found at $imagePath\n";
    exit(1);
}

$image = imagecreatefrompng($imagePath);
if (!$image) {
    echo "Failed to load image\n";
    exit(1);
}

$width = imagesx($image);
$height = imagesy($image);

$colors = [];
for ($x = 0; $x < $width; $x += 10) {
    for ($y = 0; $y < $height; $y += 10) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
        if ($r > 200 && $g < 100 && $b < 100) {
            $reds[$hex] = ($reds[$hex] ?? 0) + 1;
        }
        if ($b > 150 && $r < 150 && $g < 150) {
            $blues[$hex] = ($blues[$hex] ?? 0) + 1;
        }
    }
}

arsort($reds);
arsort($blues);
echo "Reds:\n";
print_r(array_slice($reds, 0, 5));
echo "Blues:\n";
print_r(array_slice($blues, 0, 5));