<?php

$rec = false;
$imagefilename="sprite";
$stylefilename="style";
$padding = 0;

// on recupere toutes les options
foreach
(
    $argv as $key => $arg
)
{
    //echo "$stylefilename $imagefilename";
    //var_dump($argv);
    if
    (
        $key>0
    )
    {
        if
        (
            $arg== "-r" || $arg== "--recursive"
        )
        {
            $rec = true;
            unset($arg);
        }

    }
}

$files = array_merge($argv);

// on recupere tous les fichiers des parametres
foreach($argv as $key => $arg)
{

    //var_dump($argv);
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
    $gdImage = my_merge_image_and_css($files,$stylefilename,$imagefilename,$padding);
}
else
{
    echo "Veuillez renseigner au moins deux images!";
}


// fonction qui recupere tous les fichiers png et jpg des dossiers et sous-dossiers indiques
function listFilesWithRec( $from)//.
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

function listFilesWithoutRec( $from)//.
{  
    $files = array();
    //$dirs = array($from);
    if( is_dir($from) )
    {
    
    
     if(  ( $dh = opendir($from) ) !== null  )
     {
   
        
         while ( ( $file = readdir($dh) ) !== false  )
         {
            if( $file == '.' || $file == '..'|| is_dir($file))
            {
                continue;
            }

            if(is_file($file))
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
function my_merge_image_and_css($files,$stylefilename,$imagefilename)
{

    $imgs = array();
    $mxwidth = 0;
    foreach (
        $files as $key => $file
    )
    {
        $img = imagecreatefrompng($file);
        
        array_push($imgs,$img);

        $himg = imagesy ($img);

        $mxwidth += imagesx($img);

        $mxheight = ($mxheight>(imagesy ($img)))?$mxheight:(imagesy ($img));

    }
    

    //var_dump($imgs);

    $position = 0;
    $i = 0;
    $ii=0;
    $heightmx = 0;
    $widthmx = 0;
    var_dump($imgs);
    foreach (
        $imgs as $key => $img
    )
    {
        list($width , $height , $type) = getimagesize($files[$i]);

        $heightmx+=$height;
        $widthmx+=$width;

        echo "$widthmx \n$heightmx";
        $i++;
    }

    $fp = fopen( $stylefilename.".css",'w+');
    fwrite($fp,'.'.$stylefilename ." { width: ".$widthmx.'px; height: '.$mxheight.'px; background-image: url(./'.$imagefilename.'.png); text-align:center; position:relative; }'."\n");
   
    $position = 0;
    $i = 0;
    $image = imagecreatetruecolor($mxwidth,$mxheight);
    imagecolortransparent ($image, imagecolorallocate ($image, 0, 0, 0));


    var_dump($imgs);
    foreach (
        $imgs as $key => $img
    )
    {
        list($width , $height , $type) = getimagesize($files[$i]);
        ++$ii;

        fwrite($fp,'.'.$stylefilename.($ii)." { left:".$position."px; width:".$width."px;}"."\n");	
		
        $imgmerged = imagecopymerge($image, $img, $position,0,0,0, $widthmx, $heightmx, 100);
        $position+=$width;
        $i++;
    }
    fclose($fp);

    $bgpng = imagepng($image,$imagefilename.".png");

    return $imgmerged ;
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

    __________________ETAPE0___________________

    chmod($first_img_path,0755);
    chmod($second_img_path,0755);
    
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