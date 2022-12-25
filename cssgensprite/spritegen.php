<?php
include("./deletecsspngfiles.php");

echo "\n\033[36m                         ####################                         \n                         SPRITE/CSS GENERATOR                         \n                         ####################                         \n\n";

$rec = false;
$imagefilename = "sprite";
$stylefilename = "style";
$padding = 0;
$files = array();
$countrecursive = 0;
$countimage = 0;
$countstyle = 0;
$countpadding = 0;
$error = false;
$verify = "";
// on recupere toutes les options

//var_dump($argv);
foreach ($argv as $key => $arg) {
    if
    (
        $key > 0
    ) {
        $arg = rtrim(ltrim($arg));
        if
        (
            $arg == "-r" || $arg == "--recursive"
        ) {
            $rec = true;
            $countrecursive += 1;
        }

        if
        (
            $arg == "-i"
        ) {
            $arrim = explode(".", $argv[$key + 1]);
            if
            (
                isset($arrim[0])
            ) {
                $imagefilename = $arrim[0];
                $countimage += 1;
            }
        }

        if
        (
            $arg == "-s"
        ) {
            $arrst = explode(".", $argv[$key + 1]);
            if
            (
                isset($arrst[0])
            ) {
                $stylefilename = $arrst[0];
                $countstyle += 1;
            }
        }

        if
        (
            $arg == "-p"
        ) {
            if
            (
                isset($argv[$key + 1]) && is_numeric($argv[$key + 1])
            ) {
                $padding = intval($argv[$key + 1]);
                $countpadding += 1;
            }
        }

        $arrim = explode("-output-image=", $arg);
        if
        (
            isset($arrim[1]) && $arrim[0] == "-"
        ) {
            $arrname = explode(".", $arrim[1]);
            if
            (
                isset($arrname[0])
            ) {
                $imagefilename = $arrname[0];
                $countimage += 1;
            }
        }

        $arrst = explode("-output-style=", $arg);
        if
        (
            isset($arrst[1]) && $arrst[0] == "-"
        ) {
            $arrname = explode(".", $arrst[1]);
            if
            (
                isset($arrname[0])
            ) {
                $stylefilename = $arrname[0];
                $countstyle += 1;
            }
        }


        if(
            isset($arg[1])||$arg[0]=="-"
            ) {

            if (isset($arg[1])==NULL)
            {
                $error=true;
            }

            if(isset($arg[15]))
            {
                $verify=substr($arg,0,15);
                //echo $verify;
            if($verify!="--output-image="||$verify!="--output-style="||$verify!="--output-style=")
            {
                $error = true;
                
            }
        }
        }
    }
}

if ($countimage > 1 || $countimage > 1 || $countstyle > 1 || $countrecursive > 1||$error==true) {
    echo "\033[31m            [ERROR] Veuillez renseigner une seule option a la fois\n\n                    ou saisir une option valide!\n\n";

    if($error==true)
    {
        echo "\033[36m            [Liste des options disponibles]\n\n              --output-image=FILENAME [-i]  --output-style=FILENAME [-s]\n\n              --padding=PADDING [-p]  --recursive [-r]\n";
    }
} else {

    $argv = array_merge($argv);

    deletecsspngfiles("./", $stylefilename, $imagefilename);

    // on recupere tous les fichiers des parametres
    foreach ($argv as $key => $arg) {
        if ($key > 0) {
            $extension = substr($arg, -4, strlen($arg));
            if (is_dir($arg)) {
                if ($rec == true) {
                    $files = listFilesWithRec("./" . $arg);
                    showPercent("En cours de recherche de fichiers png...", "Recherche de fichiers png terminee!");
                } else {
                    $files = listFilesWithoutRec("./" . $arg);
                    showPercent("En cours de recherche de fichiers png...", "Recherche de fichiers png terminee!");
                }
            }

            if (is_file($arg) && $extension == ".png") {
                array_push($files, $arg);
            }

        }
    }

    if (isset($files)) {
        // on trie les fichiers en recuperant les fichiers png pour l instant
        foreach ($files as $key => $file) {
            $extension = substr($file, -4, strlen($file));
            if ($extension !== ".png") {
                $id = array_search($file, $files);
                unset($files[$id]);
            }
        }


        //regenere les cles du tableau files 
        $files = array_merge($files);

        //echo "$stylefilename $imagefilename";
        if (isset($files[1])) {
            my_merge_image_and_css($files, $stylefilename, $imagefilename, $padding);
            showPercent("En cours de creation du sprite et du fichier css...", "Creation terminee!");
            echo "\n\033[96m\033[6m            [Png genere avec succes]    $imagefilename.png\n";
            echo "\033[96m            [Css genere avec succes]    $stylefilename.css\n\n";
        } else {
            echo "\033[31m            [ERROR] Veuillez renseigner un nom de dossier avec au moins deux images ou deux images\n            separees d un espace en arguments!\n";
        }

    } else {
        echo "\033[31m            [ERROR] Veuillez renseigner au moins deux images ou au moins\n            un dossier d images en argument!\n";
    }

}

function showPercent($moment, $terminated)
{
    $chaine = " ########################## ";

    $arr = str_split($chaine);

    echo "\033[32m \n            [$moment]\n\n           ";

    foreach ($arr as $value) {
        echo "\033[96m$value";
        usleep("30000");
    }
    echo "100%\n\n";
    usleep("50000");

    //$output=shell_exec("bash ./progress.bash");
    //echo "\033[0m  $output\n";

    echo "\033[32m            [$terminated]\n\n";
}

// fonction qui recupere tous les fichiers png et jpg des dossiers et sous-dossiers indiques
function listFilesWithRec($from)
{
    $files = array();
    $dirs = array($from);
    while (NULL !== ($dir = array_pop($dirs))) {
        //array_pop() => supprime et recupere le dernier element d´un tableau
        if ($dh = opendir($dir)) {
            while (false !== ($file = readdir($dh))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $dirs[] = $path;
                } else {
                    $files[] = $path;
                }
            }
            closedir($dh);
        }
    }
    return $files;
}

function listFilesWithoutRec($from)
{
    $files = array();
    if (is_dir($from)) {
        if (($dh = opendir($from)) !== null) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || is_dir($file)) {
                    continue;
                } else {
                    $files[] = $from . "/" . $file;
                }
            }

            closedir($dh);
        }
    }

    return $files;
}

// fonction qui concatene deux images
function my_merge_image_and_css($files, $stylefilename, $imagefilename, $padding)
{
    $doublepadding = $padding * 2;
    $imgs = array();
    $mxwidth = 0;
    $mxheight = 0;
    $position = 0;
    $i = 0;
    $ii = 0;
    $widthmx = 0;
    $countimg = 0;


    foreach ($files as $file) {
        $img = imagecreatefrompng($file);

        array_push($imgs, $img);

        $mxwidth += imagesx($img);

        $mxheight = ($mxheight > (imagesy($img))) ? $mxheight : (imagesy($img));

    }

    foreach ($imgs as $img) {
        list($width, $height) = getimagesize($files[$i]);
        $countimg++;
        $widthmx += $width;
        $i++;
    }

    $extension = substr($stylefilename, -4, strlen($stylefilename));
    if ($extension == ".css") {
        $tab = explode(".", $stylefilename);
        $stylefilename = $tab[0];
    }

    $extension = substr($imagefilename, -4, strlen($imagefilename));
    if ($extension == ".png") {
        $tab = explode(".", $imagefilename);
        $imagefilename = $tab[0];
    }




    $fp = fopen($stylefilename . ".css", 'w+');

    $fq = fopen($stylefilename."_index" . ".htm", 'w+');

    fwrite($fq,"
    <!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"./".$stylefilename.".css\">
    <title>".$stylefilename."_index" ."</title>
</head>
<body>
<div class=\"main\">
    <div class=\"".$stylefilename."\"/>
    ");

    fwrite($fp, '.' . $stylefilename . " \n{\n\twidth: " . ($mxwidth + ($doublepadding * $countimg)) . "px;\n\theight: " . ($mxheight + $doublepadding) . "px;\n\tbackground-image: url(./" . $imagefilename . ".png);\n\ttext-align:center;\n\tposition:relative;\n\tdisplay:flex;\n\tflex-direction:row;\n}\n\n");

    fwrite($fp, ".main\n{\n\tdisplay:flex;\n\tjustify-content:center;\n\tmargin-top:10%;\n}\n\n");


    $position = 0;
    $i = 0;
    $countloop = 0;
    $chaine = "";


    $image = imagecreatetruecolor(($mxwidth + ($doublepadding * $countimg)), $mxheight + $doublepadding);
    imagecolortransparent($image, imagecolorallocate($image, 0, 0, 0));

    foreach ($imgs as $img) {
        list($width, $height) = getimagesize($files[$i]);
        //pre-incrementation
        ++$ii;

        fwrite($fp, '.' . $stylefilename . ($ii) . "\n{\n\tleft:" . $position + $padding . "px;\n\twidth:" . $width + $doublepadding . "px;\n\theight:" . $mxheight + $doublepadding . "px;\n}\n\n" . "\n");

        fwrite($fq,"
        <div class=\"". $stylefilename . ($ii)."\"></div>
        ");

        $backspace = chr(8);
        if($countloop==$countimg-1)
        {
            fwrite($fq, "
    </div>\n");
        }

        $random = rand(1, 3);

        switch ($random) {
            case 1:
                fwrite($fp, '.' . $stylefilename . ($ii) . ":hover\n{\n\tbackground-color: red;\n\topacity:0.7;\n}\n\n");
                break;
            case 2:
                fwrite($fp, '.' . $stylefilename . ($ii) . ":hover\n{\n\tbackground-color: green;\n\topacity:0.7;\n}\n\n");
                break;
            default:
                fwrite($fp, '.' . $stylefilename . ($ii) . ":hover\n{\n\tbackground-color: yellow;\n\topacity:0.7;\n}\n\n");
                break;
        }

        if ($position == 0) {
            fwrite($fp, '.' . $stylefilename . ($ii) . "position\n{\n\theight:" . $height . "px;\n\twidth:" . $width . "px;\n\tpadding:" . $padding . "px;\n\tbackground: url(./" . $imagefilename . ".png) " . $position . "px -" . (($mxheight - $height) / 2) . "px no-repeat;\n}\n\n");

        } else {
            fwrite($fp, '.' . $stylefilename . ($ii) . "position\n{\n\theight:" . $height . "px;\n\twidth:" . $width . "px;\n\tpadding:" . $padding . "px;\n\tbackground: url(./" . $imagefilename . ".png) -" . $position + $padding . "px -" . (($mxheight - $height) / 2) . "px no-repeat;\n}\n\n");
        }


        $chaine .= "
    <div class=\"" . $stylefilename . ($ii) . "position\"></div>\n";

        //echo "position plus padding :".$position + $doublepadding."\nposition sans padding: $position\n";

        //echo "padding: $padding, padding calcule: " . ((($mxheight + $doublepadding) - $height) / 2) . "\n";

        //echo "largeur max + padding: " . ($mxwidth + ($doublepadding * $countimg)) . " largeur max sans padding: $mxwidth\n";

        if ($countloop == 0) {
            imagecopymerge($image, $img, $position + $padding, ((($mxheight + $doublepadding) - $height) / 2), 0, 0, $width, $height, 100);
            $position += $width + $padding;
        } else {
            imagecopymerge($image, $img, $position + $doublepadding, ((($mxheight + $doublepadding) - $height) / 2), 0, 0, $width, $height, 100);
            $position += $width + $doublepadding;
        }

        $countloop++;
        $i++;
    }

    
    fwrite($fq,"
       $chaine
    ");

    fwrite($fq,"
</div>
</body>
</html>
    ");
    fclose($fq);
    fclose($fp);

    imagepng($image, $imagefilename . ".png");

}