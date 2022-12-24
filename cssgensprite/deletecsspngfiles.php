<?php

// this program will delete all of the css files from the current directory

//deletecsspngfiles("./","realstyle","realpng");
//var_dump($filesdeleted);
function deletecsspngfiles($from,$stylefilename,$imagefilename)
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
                $extension = substr($file,-4,strlen($file));

                if($extension==".css"&&basename($file,".css")==$stylefilename)
                {
                    unlink("./" . $file);
                    $files[] = $from."/".$file;
                }

                if($extension==".png"&&basename($file,".png")==$imagefilename)
                {
                    $files[] = $from."/".$file;
                    unlink("./" . $file);
                }
                
            }
         }

         closedir($dh);
     }
    }


    return $files;
}