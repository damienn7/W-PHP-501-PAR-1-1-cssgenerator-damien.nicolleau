<?php

// this program will delete all of the css files from the current directory

foreach($argv as $arg)
{
    if($arg[0]==".")
    {
        $extension = $arg;
        echo $extension;
    }

    if(is_dir($arg))
    {
        $from = $arg;
        echo $from;
    }


}
/*
foreach (glob("*".$extension) as $filename) {
    unlink($filename);
}*/

deletefiles($from,$extension);
//var_dump($filesdeleted);
function deletefiles($from,$ext)
{   

    $files = array();
    if( is_dir($from) )
    {
     if(  ( $dh = opendir($from) ) !== null  )
     {
         while (( $file = readdir($dh)) !== false  )
         {
            echo "test1";
            if( $file == '.' || $file == '..'|| is_dir($file))
            {
                continue;
            }
            else
            {

                echo "test2";
                $extension = substr($file,-(strlen($ext)),strlen($file));
                    echo $extension;
                if($extension==$ext)
                {
                    echo "check";
                    unlink($from."/" . $file);
                    $files[] = $from."/".$file;
                }
                
            }
         }

         closedir($dh);
     }
    }


    return $files;
}