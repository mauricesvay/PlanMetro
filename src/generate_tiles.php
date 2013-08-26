<?php
ini_set('memory_limit','1024M');

$folder_pattern = "TileGroup{{i}}";
$counter = 1;
$folder = 0;
$subfolder = str_replace("{{i}}", $folder, $folder_pattern);
if (!is_dir("tiles/". $subfolder)) {
	mkdir("tiles/". $subfolder);
}
$tilesize = 256;
$image = imagecreatefrompng("metro.png");
$width = imagesx($image);
$height = imagesy($image);
$smallest_dimension = min($width, $height);
$steps = ceil(sqrt($smallest_dimension / $tilesize));

$w = $width;
$h = $height;
$background = imagecolorallocate($image, 255, 235, 205);

//Prepare resized
echo "Generating zoom levels\n";
for ($i=1; $i<=$steps; $i++) {
	$w = ceil($width / pow(2,$i-1));
	$h = ceil($height / pow(2,$i-1));
	$out = imagecreatetruecolor($w, $h);
	imagecopyresampled (
		$out, $image,
		0, 0,
		0, 0,
		$w , $h,
		$width , $height
	);
	$filename = "tiles/" . (1 + $steps - $i) . ".png";
	imagepng($out, $filename);

}
echo "$steps zoom levels generated\n";

echo "Generating tiles\n";
for ($i=1; $i<=$steps; $i++) {
	echo "Generating tiles for level $i","\n";
	$filename = "tiles/" . $i . ".png";
	$out = imagecreatefrompng($filename);
	$w = imagesx($out);
	$h = imagesy($out);

	for ($y=0; $y < $h; $y+=$tilesize) {
		for ($x=0; $x < $w; $x+=$tilesize) {
			$copy_width = min($tilesize, ($w-$x));
			$copy_height = min($tilesize, ($h-$y));
			$tile = imagecreatetruecolor($copy_width, $copy_height);
			imagefill($tile, 0, 0, $background);

			imagecopy(
				$tile,
				$out,
				0, 0,
				$x, $y,
				$copy_width, $copy_height
			);

			// JPG
			$tilename = "tiles/" . $subfolder . "/" . $i . "-" . ($x / $tilesize) . "-" . ($y / $tilesize) . ".jpg";
			imagejpeg($tile, $tilename, 95);

			// PNG
			// $tilename = "tiles/" . $subfolder . "/" . $i . "-" . ($x / $tilesize) . "-" . ($y / $tilesize) . ".png";
			// imagepng($tile, $tilename);

			$counter++;
			if ($counter > 255) {
				$folder++;
				$counter = 0;
				$subfolder = str_replace("{{i}}", $folder, $folder_pattern);
				if (!is_dir("tiles/". $subfolder)) {
					mkdir("tiles/". $subfolder);
				}
			}
		}
	}
}