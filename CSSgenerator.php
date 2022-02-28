<?php

$png_files = [];
$new_sprite = "sprite.png";
$style = "style.css";
$recursive = FALSE;

function my_generate_css() {
    
    global $png_files, $new_width, $new_sprite, $width, $height, $style, $path;
    $nb = 0;
    $new_width = 0;
    
    $fopen = fopen($style, 'w+');
    fwrite($fopen, ".sprite { 
    
        background: url($new_sprite);
        background-repeat: no-repeat;

    }");

    foreach($png_files as $png) {
    
        list($width, $height) = getimagesize($png);
        
        $nb++;
        
        fwrite($fopen, "#img$nb{ 
    
            width: $width px;
            height: $height px;
            background-position: $new_width px;

        }");

        $new_width += $width;
        
    }
    
}


function my_scandir($dir) {

    global $png_files, $recursive, $new_sprite;

    $path = realpath($dir);

    $file_path = $path.'/'.$new_sprite;

    // On ouvre le dossier
    if ($handle = opendir($dir)){
         
    // Pour chaque élément du dossier:
        while(FALSE !== ($file = readdir($handle))){

            if ($file == '.' || $file == '..') {

                continue;
            }

            $file_path = $path.'/'.$file;

            if (substr($file, -4) == '.png') {
            
                array_push($png_files, $file_path);
            }
    
            if (is_dir($file_path) && $recursive == TRUE) {

                my_scandir($file_path);

            }

            
        } 

    closedir($handle);
    }
}

function my_merge_image($png_files){

$w = [];
$h = [];

global $new_width, $new_sprite;

//Pour chaque image de notre tableau, on récupère les dimensions de celle-ci et on les ajoute à de nouveaux tableaux.
foreach($png_files as $png) {

    list($width, $height) = getimagesize($png);
    $w[] = $width;
    $h[] = $height;

}

$sprite_width = array_sum($w);
$max_height = max($h);

$bg = imagecreatetruecolor($sprite_width, $max_height);

imagesavealpha($bg, true);
$color = imagecolorallocatealpha($bg, 0, 0, 0, 127);
imagefill($bg, 0, 0, $color);

foreach($png_files as $png) {

    list($width, $height) = getimagesize($png);

    $src = imagecreatefrompng($png);

    imagecopy($bg, $src, $new_width, 0, 0, 0, $width, $height);

    $new_width += $width;
}

imagepng($bg, $new_sprite);
echo 'Sprite created.'.PHP_EOL;

}

function man() {
    echo '
    MANUEL

    CSS_GENERATOR
    
    UserCommands

    NAME
    css_generator - sprite generator for HTML use

    SYNOPSIS
    css_generator [OPTIONS]. . . assets_folder

    DESCRIPTION

    Concatenate all images inside a folder in one sprite and write a style sheet ready to use.
    Mandatory arguments to long options are mandatory for short options too.

    -r, -- recursive
    Look for images into the assets_folder passed as arguement and all of its subdirectories.

    -i, -- output-image=IMAGE
    Name of the generated image. If blank, the default name is « sprite.png ».

    -s, -- output-style=STYLE
    Name of the generated stylesheet. If blank, the default name is « style.css ».'.PHP_EOL;
    exit();
}

//Ici, on teste les arguments.

function arg() {
    global $argc, $argv, $recursive, $new_sprite, $style, $path;
    
    if ($argc < 2) {
        echo "Please enter a folder name. Enter 'man' to print the manual.".PHP_EOL;
        exit();
    }

    if ($argv[1] === 'man') {
        man();
    }
    
    for ($i = 1; $i < $argc - 1; $i++) {

        $arg = $argv[$i];
    
        switch($arg) {
            
            case ("-r"):
            case ("--recursive"):
                $recursive = TRUE;
                echo 'Program is now recursive.'.PHP_EOL;
                break;
            case ("-i"): 
            case ("--output-image"):
                echo "Please enter an output image name: ";
                $value = readline();
                $new_sprite = $value;
                echo "New sprite named $value saved.".PHP_EOL;
                break;
            case ("-s"):
            case ("--output-style"):
                echo "Please enter an output stylesheet name: ";
                $new_style = readline();
                $style = $new_style;
                echo "New stylesheet named $new_style saved.".PHP_EOL;
                array_map('unlink', glob( "$path*.css"));
                break;
        }

    }

    

}

arg();
my_scandir($argv[$argc-1]);
my_merge_image($png_files);
my_generate_css();

?>