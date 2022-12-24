<?php
include("./deletecsspngfiles.php");


$rec = false;
$imagefilename="sprite";
$stylefilename="style";
$padding = 0;
$files = array();
// on recupere toutes les options

//var_dump($argv);
foreach
(
    $argv as $key => $arg
)
{
    if
    (
        $key>0
    )
    {
        $arg = rtrim(ltrim($arg));
        if
        (
            $arg== "-r" || $arg== "--recursive"
        )
        {
            $rec = true;
        }

        if
        (
            $arg=="-i"
        )
        {
            $arrim = explode("." , $argv[$key+1]);
            if
            (
                isset($arrim[0])
            )
            {   
                $imagefilename=$arrim[0];
            }
        }

        if
        (
            $arg=="-s"
        )
        {
            $arrst = explode("." , $argv[$key+1]);
            if
            (
                isset($arrst[0])
            )
            {   
                $stylefilename=$arrst[0];
            }
        }

        if
        (
            $arg=="-p"
        )
        {
            if
            (
                isset($argv[$key+1])&&is_numeric($argv[$key+1])
            )
            {   
                $padding=intval($argv[$key+1]);
            }
        }

        $arrim = explode("-output-image=" , $arg);
        if
        (
            isset($arrim[1])&&$arrim[0]=="-"
        )
        {
            $arrname=explode(".",$arrim[1]);
            if
            (
                isset($arrname[0])
            )
            {
                $imagefilename = $arrname[0];
            }
        }

        $arrst = explode("-output-style=" , $arg);
        if
        (
            isset($arrst[1])&&$arrst[0]=="-"
        )
        {
            $arrname=explode(".",$arrst[1]);
            if
            (
                isset($arrname[0])
            )
            {
                $stylefilename = $arrname[0];
            }
        }

    }
}

$argv = array_merge($argv);

deletecsspngfiles("./",$stylefilename,$imagefilename);

// on recupere tous les fichiers des parametres
foreach($argv as $key => $arg)
{
    if($key>0)
    {
        $extension = substr($arg,-4,strlen($arg));
        if(is_dir($arg))
        {
            if($rec==true)
            {
                $files = listFilesWithRec("./".$arg);
            }
            else
            {
                $files = listFilesWithoutRec("./".$arg);
            }
        }

        if(is_file($arg)&&$extension == ".png")
        {
            array_push($files , $arg);
        }
        
    }
}

if(isset($files))
{
// on trie les fichiers en recuperant les fichiers png pour l instant
foreach($files as $key => $file)
{   
    $extension = substr($file,-4,strlen($file));
    if($extension !== ".png")
    {
        $id = array_search($file , $files);
        unset($files[$id]);
    }
}


//regenere les cles du tableau files 
$files = array_merge($files);

//echo "$stylefilename $imagefilename";
if(isset($files[1]))
{
    my_merge_image_and_css($files,$stylefilename,$imagefilename,$padding);
}
else
{
    echo "Veuillez renseigner un nom de dossier avec au moins deux images ou deux images separees d un espace en arguments!\n";
}

}
else
{
    echo "Veuillez renseigner au moins deux images ou au moins un dossier d imagesen argument!\n";
}


// fonction qui recupere tous les fichiers png et jpg des dossiers et sous-dossiers indiques
function listFilesWithRec( $from)
{  
    $files = array();
    $dirs = array($from);
    while( NULL !== ($dir = array_pop($dirs)))
    {
        //array_pop() => supprime et recupere le dernier element d´un tableau
        if( $dh = opendir($dir))
        {
            while( false !== ($file = readdir($dh)))
            {
                if( $file == '.' || $file == '..')
                {
                    continue;
                }

                $path = $dir . '/' . $file;
                if( is_dir($path))
                {
                    $dirs[] = $path;
                }
                else
                {
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
    if( is_dir($from) )
    {
     if(  ( $dh = opendir($from) ) !== null  )
     {
         while (( $file = readdir($dh)) !== false  )
         {
            if( $file == '.' || $file == '..'|| is_dir($file))
            {
                continue;
            }
            else
            {
                $files[] = $from."/".$file;
            }
         }

         closedir($dh);
     }
    }

    return $files;
}

// fonction qui concatene deux images
function my_merge_image_and_css($files,$stylefilename,$imagefilename,$padding)
{
    $doublepadding = $padding * 2;
    $imgs = array();
    $mxwidth = 0;
    $mxheight = 0;
    $position = 0;
    $i = 0;
    $ii=0;
    $widthmx = 0;
    $countimg = 0;
    

    foreach (
        $files as $file
    )
    {
        $img = imagecreatefrompng($file);
        
        array_push($imgs,$img);

        $mxwidth += imagesx($img);

        $mxheight = ($mxheight>(imagesy ($img)))?$mxheight:(imagesy ($img));

    }

    foreach (
        $imgs as $img
    )
    {
        list($width , $height) = getimagesize($files[$i]);
        $countimg++;
        $widthmx+=$width;
        $i++;
    }

    $extension = substr($stylefilename, -4, strlen($stylefilename));
    if($extension==".css")
    {
        $tab = explode(".",$stylefilename);
        $stylefilename = $tab[0];
    }

    $extension = substr($imagefilename, -4, strlen($imagefilename));
    if($extension==".png")
    {
        $tab = explode(".",$imagefilename);
        $imagefilename = $tab[0];
    }




    $fp = fopen( $stylefilename.".css",'w+');
    

    fwrite($fp,'.'.$stylefilename ." \n{\n\twidth: ".($mxwidth+($doublepadding*$countimg))."px;\n\theight: ".($mxheight+$doublepadding)."px;\n\tbackground-image: url(./".$imagefilename.".png);\n\ttext-align:center;\n\tposition:relative;\n\tdisplay:flex;\n\tflex-direction:row;\n}\n\n");
   
    fwrite($fp,".main\n{\n\tdisplay:flex;\n\tjustify-content:center;\n\tmargin-top:10%;\n}\n\n");


    $position = 0;
    $i = 0;
    $countloop =0;

    $image = imagecreatetruecolor(($mxwidth+($doublepadding*$countimg)),$mxheight+$doublepadding);
    imagecolortransparent ($image, imagecolorallocate ($image, 0, 0, 0));
    
    foreach (
        $imgs as $img
    )
    {
        list($width , $height) = getimagesize($files[$i]);
        //pre-incrementation
        ++$ii;

        fwrite($fp,'.'.$stylefilename.($ii)."\n{\n\tleft:".$position+$padding."px;\n\twidth:".$width+$doublepadding."px;\n\theight:".$mxheight+$doublepadding."px;\n}\n\n"."\n");

        $random = rand(1,3);

        switch ($random) {
            case 1:
                fwrite($fp,'.'.$stylefilename.($ii).":hover\n{\n\tbackground-color: red;\n\topacity:0.7;\n}\n\n");
                break;
            case 2:
                fwrite($fp,'.'.$stylefilename.($ii).":hover\n{\n\tbackground-color: green;\n\topacity:0.7;\n}\n\n");
                break;
            default:
            fwrite($fp,'.'.$stylefilename.($ii).":hover\n{\n\tbackground-color: yellow;\n\topacity:0.7;\n}\n\n");
                break;
        }

        if($position==0)
        {
            fwrite($fp,'.'.$stylefilename.($ii)."position\n{\n\theight:".$height."px;\n\twidth:".$width."px;\n\tpadding:".$padding."px;\n\tbackground: url(./".$imagefilename.".png) ".$position."px -".(($mxheight - $height) / 2)."px no-repeat;\n}\n\n");

        } else 
        {
            fwrite($fp, '.' . $stylefilename . ($ii) . "position\n{\n\theight:" . $height . "px;\n\twidth:" . $width . "px;\n\tpadding:" . $padding . "px;\n\tbackground: url(./" . $imagefilename . ".png) -" . $position+$padding . "px -" . (($mxheight - $height) / 2) . "px no-repeat;\n}\n\n");
        }

        //echo "position plus padding :".$position + $doublepadding."\nposition sans padding: $position\n";

        //echo "padding: $padding, padding calcule: " . ((($mxheight + $doublepadding) - $height) / 2) . "\n";

        //echo "largeur max + padding: " . ($mxwidth + ($doublepadding * $countimg)) . " largeur max sans padding: $mxwidth\n";

        if ($countloop == 0) {
            imagecopymerge($image, $img, $position + $padding, ((($mxheight + $doublepadding) - $height) / 2), 0, 0, $width, $height, 100);
            $position+=$width+$padding;
        }
        else
        {
            imagecopymerge($image, $img, $position + $doublepadding, ((($mxheight + $doublepadding) - $height) / 2), 0, 0, $width, $height, 100);
            $position+=$width+$doublepadding;
        }

        $countloop++;
        $i++;
    }
    fclose($fp);
    
    imagepng($image,$imagefilename.".png");
    
}



/*

imagecopymerge(
    GdImage $dst_image,
    GdImage $src_image,
    int $dst_x,
    int $dst_y,
    int $src_x,
    int $src_y,
    int $src_width,
    int $src_height,
    int $pct
): bool

*/

/*

    __________________ETAPE-0___________________
    
    // echo "$second_img_path\n";

    $img1 = imagecreatefrompng($first_img_path);
    $img2 = imagecreatefrompng($second_img_path);


    list($width , $height , $type) = getimagesize($first_img_path);
    list($width2 , $height2 , $type2) = getimagesize($second_img_path);
    array_push($arr,[$width , $height , $type]);

    //var_dump($arr);
    $wimg1= imagesx ($img1);
    $himg1 = imagesy ($img1);

    $wimg2 = imagesx ($img2);
    $himg2 = imagesy ($img2);
   
    $mxheight = ($himg1>$himg2)?$himg1:$himg2;
    $image = imagecreatetruecolor(($wimg1+$wimg2),$mxheight);

    imagecolortransparent ($image, imagecolorallocate ($image, 0, 0, 0));

    $imgmerged = imagecopymerge($image, $img1, 0,0,0,0, ($width+$width2), ($height+$height2), 100);

    imagecolortransparent ($image, imagecolorallocate ($image, 0, 0, 0));

    $imgmerged = imagecopymerge($image, $img2, ($width+1),0,0,0, ($width+$width2), ($height+$height2), 100);
    
    $bgpng = imagepng($image,"sprite.png");



    _____________OPTIONS______________

            echo "$stylefilename $imagefilename";
        $csscheck = stristr($arg, '--output-style=', true);
        if
        (
            $csscheck !== "--output-style="
        )
        {
            
            $name = stristr($csscheck, '.css');
            if
            (
                $name !== NULL
            )
            {
                
                $stylefilename = $name.'.css';
                unset($arg);
            }
        }
        echo "$stylefilename $imagefilename";
        if
        (
            $arg=="-s"
        )
        {
            $name = stristr($argv[$key+1], '.css');
            if
            (
                $name !== NULL
            )
            {
                $stylefilename=stristr($argv[$key+1], '.css')."css";
                unset($arg);
            }
        }
        echo "$stylefilename $imagefilename";
        $pngcheck = stristr($arg, '--output-image=', true);
        if
        (
            $pngcheck !== "--output-image"
        )
        {
            $name = stristr($pngcheck, '.png');
            if($name !== ".png")
            {
                
                $imagefilename = $name.'.png';
                unset($arg);
            }
        }
        echo "$stylefilename $imagefilename";
        if
        (
            $arg=="-i"
        )
        {
                $name = stristr($argv[$key+1], '.png');
                if
                (
                    $name !== '.png'
                )
                {
                    $imagefilename=stristr($argv[$key+1], '.png')."png";
                    unset($arg);
                }
            
        }
        echo "$stylefilename $imagefilename";
*/