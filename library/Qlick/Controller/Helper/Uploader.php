<?php

/**
 * 
 * Загрузка файлов
 * Примеры
 * http://www.mywebmymail.com/?q=content/easyphpthumbnail-class
 *
 */
class Qlick_Controller_Helper_Uploader extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Реализует загрузку изображений
     * @return string название загруженного файла
     */
    protected function upload($uploads_dir) {
        if (!is_dir($uploads_dir))
            throw new Exception("Папка '$uploads_dir' не существует");
        if (empty($_FILES))
            throw new Exception("Файл не получен");
        $tmp_name = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        $ext = strtolower(substr(strrchr($name, '.'), 1));
        $name = time() . md5($name) . '.' . $ext;
        $new = $uploads_dir . "/$name";
        switch (strtolower($ext)) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                move_uploaded_file($tmp_name, $new);
                break;
            default:
                //throw new Exception('Неизвестный тип файла: ' . $ext);
                return NULL;
                break;
        }
        return $name;
    }

    /**
     * Реализует загрузку прочих файлов
     * @return string название загруженного файла
     */
    public function fileupload($uploads_dir) {
        if (!is_dir($uploads_dir))
            throw new Exception("Папка '$uploads_dir' не существует");
        if (empty($_FILES))
            throw new Exception("Файл не получен");
        $tmp_name = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        $ext = strtolower(substr(strrchr($name, '.'), 1));
        $name = time() . md5($name) . '.' . $ext;
        $new = $uploads_dir . "/$name";
        switch (strtolower($ext)) {
            case 'flv':
            case 'mp4':
            case 'mp3':
            case 'xls':
                move_uploaded_file($tmp_name, $new);
                break;
            default:
                //throw new Exception('Неизвестный тип файла: ' . $ext);
                return NULL;
                break;
        }
        return $name;
    }
    
    /**
     * Реализует загрузку прочих файлов
     * @return string название загруженного файла
     */
    public function mp3upload($uploads_dir) {
        
        if (!is_dir($uploads_dir))
            throw new Exception("Папка '$uploads_dir' не существует");
        if (empty($_FILES))
            throw new Exception("Файл не получен");
        
        $tmp_name = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        $ext = strtolower(substr(strrchr($name, '.'), 1));
        //$name = time() . md5($name) . '.' . $ext;
        //$name = $name . '.' . $ext;
        $new = $uploads_dir . "/" . $name;
        
        switch (strtolower($ext)) {
            case 'mp3':
                move_uploaded_file($tmp_name, $new);
                break;
            default:
                //throw new Exception('Неизвестный тип файла: ' . $ext);
                return NULL;
                break;
        }
        return $name;
    }    

    /**
     * Уменьшает изображения до $size
     * @param string $file путь к файлу
     * @param int $size OPTIONAL размер нового файла
     * @return bool
     * @see http://www.phpclasses.org/package/5168-PHP-Manipulate-images-and-generate-thumbnails.html
     */
    public function resize($file, $size=200, $width = true, $sharp = false, $optimization = 0, $watermark = false) {
        if (!is_file($file)) {
            return false;
        }
        $info = pathinfo($file);
        $thumb = new Qlick_EasyPhpThumbnail();
        if ($width) {
            $thumb->Thumbwidth = $size;
            if ($watermark) $thumb->Watermarkposition = '97% 96%';            
        } else {
            $thumb->Thumbheight = $size;
            if ($watermark) $thumb->Watermarkposition = '95% 97%';
        }
        
        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        $thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        
        $thumb->Thumbsaveas = 'png';
        
        if ($sharp) $thumb->Sharpen = true;
        
        if ($optimization > 0) $thumb->Quality = $optimization;
        
        if ($watermark) {
            $thumb->Watermarkpng = Zend_Registry::get('upload_path') . '/watermark.png';        
            $thumb->Watermarktransparency = 90;
        }
        
        $thumb->createthumb($file, 'file');
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            unlink($file);
            @rename($new, $file);
            
            unset($thumb);
            
            return $file;
        } else {
            return false;
        }
    }
    
    public function resizeToPath($file, $path, $size=200, $width = true, $sharp = false, $optimization = 0, $png = false) {
        if (!is_file($file)) {
            return false;
        }
        $info = pathinfo($file);
        $thumb = new Qlick_EasyPhpThumbnail();
        if ($width)
            $thumb->Thumbwidth = $size;
        else
            $thumb->Thumbheight = $size;

        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        $thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        $thumb->Sharpen = $sharp;
        if ($png) $thumb->Thumbsaveas = 'png';
        
        if ($optimization > 0) $thumb->Quality = $optimization;
        
        $thumb->createthumb($file, 'file');
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            @rename($new, $path);
            
            unset($thumb);
            
            return $path;
        } else {
            return false;
        }
    }    
    
    public function qlickstamp($file, $stamp) {

//        $info = pathinfo($file);
        $img_size = getimagesize($file);
        
        $watermarkFileLocation = Zend_Registry::get('upload_path') . '/' . $stamp;
        $watermarkImage = imagecreatefrompng($watermarkFileLocation);
        $watermarkWidth = imagesx($watermarkImage);  
        $watermarkHeight = imagesy($watermarkImage);

        $originalImage = imagecreatefromjpeg($file);

        $destX = 10;
        $destY = $img_size[1] - $watermarkHeight - 10;

        // creating a cut resource
        $cut = imagecreatetruecolor($watermarkWidth, $watermarkHeight);

        // copying that section of the background to the cut
        imagecopy($cut, $originalImage, 0, 0, $destX, $destY, $watermarkWidth, $watermarkHeight);

        // placing the watermark now
        imagecopy($cut, $watermarkImage, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight);

        // merging both of the images
        imagecopymerge($originalImage, $cut, $destX, $destY, 0, 0, $watermarkWidth, $watermarkHeight, 100);
        
        return imagejpeg($originalImage, $file, 95); //@rename($originalImage, $file);
        //return imagepng($originalImage, $file); //@rename($originalImage, $file);

    }
    
    /* 
    http://vikjavev.no/computing/ump.php?id=306
    New:  
    - In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements. 
    - From version 2 (July 17 2006) the script uses the imageconvolution function in PHP  
    version >= 5.1, which improves the performance considerably. 


    Unsharp masking is a traditional darkroom technique that has proven very suitable for  
    digital imaging. The principle of unsharp masking is to create a blurred copy of the image 
    and compare it to the underlying original. The difference in colour values 
    between the two images is greatest for the pixels near sharp edges. When this  
    difference is subtracted from the original image, the edges will be 
    accentuated.  

    The Amount parameter simply says how much of the effect you want. 100 is 'normal'. 
    Radius is the radius of the blurring circle of the mask. 'Threshold' is the least 
    difference in colour values that is allowed between the original and the mask. In practice 
    this means that low-contrast areas of the picture are left unrendered whereas edges 
    are treated normally. This is good for pictures of e.g. skin or blue skies. 

    Any suggenstions for improvement of the algorithm, expecially regarding the speed 
    and the roundoff errors in the Gaussian blur process, are welcome. 

    */ 

    function UnsharpMask($file, $amount, $radius, $threshold)    {  

    ////////////////////////////////////////////////////////////////////////////////////////////////   
    ////   
    ////                  Unsharp Mask for PHP - version 2.1.1   
    ////   
    ////    Unsharp mask algorithm by Torstein Hønsi 2003-07.   
    ////             thoensi_at_netcom_dot_no.   
    ////               Please leave this notice.   
    ////   
    ///////////////////////////////////////////////////////////////////////////////////////////////   


        $img = imagecreatefromjpeg($file); 
        // $img is an image that is already created within php using  
        // imgcreatetruecolor. No url! $img must be a truecolor image.  

        // Attempt to calibrate the parameters to Photoshop:  
        if ($amount > 500)    $amount = 500;  
        $amount = $amount * 0.016;  
        if ($radius > 50)    $radius = 50;  
        $radius = $radius * 2;  
        if ($threshold > 255)    $threshold = 255;  

        $radius = abs(round($radius));     // Only integers make sense.  
        if ($radius == 0) {  
            return $img; imagedestroy($img);        
        }  
        $w = imagesx($img); $h = imagesy($img);  
        $imgCanvas = imagecreatetruecolor($w, $h);  
        $imgBlur = imagecreatetruecolor($w, $h);  


        // Gaussian blur matrix:  
        //                          
        //    1    2    1          
        //    2    4    2          
        //    1    2    1          
        //                          
        //////////////////////////////////////////////////  


        if (function_exists('imageconvolution')) { // PHP >= 5.1   
                $matrix = array(   
                array( 1, 2, 1 ),   
                array( 2, 4, 2 ),   
                array( 1, 2, 1 )   
            );   
            imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h);  
            imageconvolution($imgBlur, $matrix, 16, 0);   
        }   
        else {   

        // Move copies of the image around one pixel at the time and merge them with weight  
        // according to the matrix. The same matrix is simply repeated for higher radii.  
            for ($i = 0; $i < $radius; $i++)    {  
                imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left  
                imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right  
                imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center  
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);  

                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up  
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down  
            }  
        }  

        if($threshold>0){  
            // Calculate the difference between the blurred pixels and the original  
            // and set the pixels  
            for ($x = 0; $x < $w-1; $x++)    { // each row 
                for ($y = 0; $y < $h; $y++)    { // each pixel  

                    $rgbOrig = ImageColorAt($img, $x, $y);  
                    $rOrig = (($rgbOrig >> 16) & 0xFF);  
                    $gOrig = (($rgbOrig >> 8) & 0xFF);  
                    $bOrig = ($rgbOrig & 0xFF);  

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);  

                    $rBlur = (($rgbBlur >> 16) & 0xFF);  
                    $gBlur = (($rgbBlur >> 8) & 0xFF);  
                    $bBlur = ($rgbBlur & 0xFF);  

                    // When the masked pixels differ less from the original  
                    // than the threshold specifies, they are set to their original value.  
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))   
                        : $rOrig;  
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))   
                        : $gOrig;  
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)   
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))   
                        : $bOrig;  



                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {  
                            $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);  
                            ImageSetPixel($img, $x, $y, $pixCol);  
                        }  
                }  
            }  
        }  
        else{  
            for ($x = 0; $x < $w; $x++)    { // each row  
                for ($y = 0; $y < $h; $y++)    { // each pixel  
                    $rgbOrig = ImageColorAt($img, $x, $y);  
                    $rOrig = (($rgbOrig >> 16) & 0xFF);  
                    $gOrig = (($rgbOrig >> 8) & 0xFF);  
                    $bOrig = ($rgbOrig & 0xFF);  

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);  

                    $rBlur = (($rgbBlur >> 16) & 0xFF);  
                    $gBlur = (($rgbBlur >> 8) & 0xFF);  
                    $bBlur = ($rgbBlur & 0xFF);  

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;  
                        if($rNew>255){$rNew=255;}  
                        elseif($rNew<0){$rNew=0;}  
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;  
                        if($gNew>255){$gNew=255;}  
                        elseif($gNew<0){$gNew=0;}  
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;  
                        if($bNew>255){$bNew=255;}  
                        elseif($bNew<0){$bNew=0;}  
                    $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;  
                        ImageSetPixel($img, $x, $y, $rgbNew);  
                }  
            }  
        }  
        imagedestroy($imgCanvas);  
        imagedestroy($imgBlur);  

        return imagejpeg($img, $file, 95);
        return $img;  

    }     
    

    /**
     * Уменьшает изображения до $height и $width с обрезкой
     * @param string $file путь к файлу
     * @param int $size OPTIONAL размер нового файла
     * @return bool
     * @see http://www.phpclasses.org/package/5168-PHP-Manipulate-images-and-generate-thumbnails.html
     */
    public function resizeWithCrop($file, $height=291, $width=517, $sharp = false) {
        if (!is_file($file)) {
            return false;
        }
        $info = pathinfo($file);
        $size = getimagesize($file);
        $thumb = new Qlick_EasyPhpThumbnail();
        // новая система интелектуального сжатия
        $original_aspect = $size[0] / $size[1];
        $thumb_aspect = $width / $height;

        if ($original_aspect >= $thumb_aspect) {
            // If image is wider than thumbnail (in aspect ratio sense)
            $thumb->Thumbheigh = $height;
            $thumb->Thumbwidth = $size[0] / ($size[1] / $height);
        } else {
            // If the thumbnail is wider than the image
            $thumb->Thumbwidth = $width;
            $thumb->Thumbheigh = $size[1] / ($size[0] / $width);
        }
        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        $thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        $thumb->Thumbsaveas = 'png';
        
        $thumb->createthumb($file, 'file');
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            unlink($file);
            @rename($new, $file);
            $size = getimagesize($file);
            // Если изображение блять превышает норму, тогда ебашим его...
            if ($size[0] > $width) {
                $left = floor(($size[0] - $width) / 2);
                $right = $size[0] - $width - $left;
                $thumb->Cropimage = array(1, 1, $left, $right, 0, 0);
            } elseif ($size[1] > $height) {
                // разрежем посередке
                $bottom = floor(($size[1] - $height) / 2);
                $top = $size[1] - $height - $bottom;
                $thumb->Cropimage = array(1, 1, 0, 0, $top, $bottom);
            }
            $thumb->Sharpen = $sharp;
            $thumb->createthumb($file, 'file');
            $new = $info['dirname'] . '/tbn_' . $info['basename'];
            @rename($new, $file);
            
            unset($thumb);
            
            return $file;
        } else {
            return false;
        }
    }

    public function resizeWithCropToPath($file, $path, $height=291, $width=517, $sharp = false, $optimization = 0) {
        if (!is_file($file)) {
            return false;
        }
        $info = pathinfo($file);
        $size = getimagesize($file);
        $thumb = new Qlick_EasyPhpThumbnail();
        // новая система интелектуального сжатия
        $original_aspect = $size[0] / $size[1];
        $thumb_aspect = $width / $height;

        if ($original_aspect >= $thumb_aspect) {
            // If image is wider than thumbnail (in aspect ratio sense)
            $thumb->Thumbheigh = $height;
            $thumb->Thumbwidth = $size[0] / ($size[1] / $height);
        } else {
            // If the thumbnail is wider than the image
            $thumb->Thumbwidth = $width;
            $thumb->Thumbheigh = $size[1] / ($size[0] / $width);
        }        
        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        $thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        //if ($size[0] < 1920) $thumb->Thumbsaveas = 'png';
        
        if ($optimization > 0) $thumb->Quality = $optimization;
        
        $thumb->createthumb($file, 'file');
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            @rename($new, $path);
            $size = getimagesize($path);
            // Если изображение блять превышает норму, тогда ебашим его...
            if ($size[0] > $width) {
                $left = floor(($size[0] - $width) / 2);
                $right = $size[0] - $width - $left;
                $thumb->Cropimage = array(1, 1, $left, $right, 0, 0);
            } elseif ($size[1] > $height) {
                // разрежем посередке
                $bottom = floor(($size[1] - $height) / 2);
                $top = $size[1] - $height - $bottom;
                $thumb->Cropimage = array(1, 1, 0, 0, $top, $bottom);
            }
            $thumb->Sharpen = $sharp;            
            $thumb->createthumb($path, 'file');
            $new = $info['dirname'] . '/tbn_' . $info['basename'];
            @rename($new, $path);
            
            unset($thumb);
            
            return $path;
        } else {
            return false;
        }
    }
    
    public function resizeAvatarToPath($file, $path, $size = 64, $aspect_width = true, $width=64, $height=64, $x = 0, $y = 0, $sharp = true) {
        
        if (!is_file($file)) {
            throw new Exception("Файл '$file' не существует");
        }
        $info = pathinfo($file);
        $imagesize = getimagesize($file);
        
        $thumb = new Qlick_EasyPhpThumbnail();

        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        $thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        //throw new Exception('x: ' . $x . ', w: ' . $width . ', y: ' . $y . ', h: ' . $heigth . ', высота: ' . $imagesize[1]);
        $thumb->Cropimage = array(1, 1, $x, $imagesize[0] - ($x + $width), $y, $imagesize[1] - ($y + $height)); // x, 250 - (x + w), y, высота - (y + h)
        
        if ($sharp) $thumb->Sharpen = true;
        
        $thumb->Thumbsaveas = 'png';
        
        if ($aspect_width)
            $thumb->Thumbwidth = $size;
        else
            $thumb->Thumbheight = $size;
        
        $thumb->createthumb($file, 'file');        
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            @rename($new, $path);
            
            unset($thumb);
            
            return $path;
        } else {
            return false;
        }        
        
    }  
    
    public function resizeAvatarWithCropToPath($file, $path, $crop_w = 64, $crop_h = 64, $aspect_width = true, $width=64, $height=64, $x = 0, $y = 0, $sharp = true) {
        if (!is_file($file)) {
            throw new Exception("Файл '$file' не существует");
        }
        $info = pathinfo($file);
        $imagesize = getimagesize($file);
        $thumb = new Qlick_EasyPhpThumbnail();

        $thumb->Thumblocation = $info['dirname'] . '/';
        $thumb->Thumbprefix = 'tbn_';
        //$thumb->Thumbsaveas = $info['extension'];
        $thumb->Thumbfilename = $info['basename'];
        //throw new Exception($y); //. ', w: ' . ($x) . ', y: ' . $y . ', h: ' . $imagesize[1] - ($y + $height) . ', высота: ' . $imagesize[1]);
        
        // если вырезаемая область $crop_w больше выделенной $width, то это погрешность, выравниваем
        //if ($width < $crop_w) $width = $crop_w;
        
        $thumb->Cropimage = array(1, 1, $x, $imagesize[0] - ($x + $width), $y, $imagesize[1] - ($y + $height)); // x, 250 - (x + w), y, высота - (y + h)
        
        $thumb->Thumbsaveas = 'png'; 

        if ($aspect_width) {
            $thumb->Thumbwidth = $crop_w;
        } else {
            $thumb->Thumbheight = $crop_h;        
        }
        
        $thumb->createthumb($file, 'file');
        //throw new Exception('1');
        $new = $info['dirname'] . '/tbn_' . $info['basename'];
        if (is_file($new)) {
            @rename($new, $path);
            $size = getimagesize($path);
            // Если изображение блять превышает норму, тогда ебашим его...
            if ($aspect_width) {
                $bottom = floor(($size[1] - $crop_h) / 2);
                $top = $size[1] - $crop_h - $bottom;
                $thumb->Cropimage = array(1, 1, 0, 0, $top, $bottom);                
            } else {
                // разрежем посередке
                $left = floor(($size[0] - $crop_w) / 2);
                $right = $size[0] - $crop_w - $left;
                $thumb->Cropimage = array(1, 1, $left, $right, 0, 0);
            }            
            if ($sharp && $info['extension'] != 'png') $thumb->Sharpen = true;            
            $thumb->createthumb($path, 'file');
            $new = $info['dirname'] . '/tbn_' . $info['basename'];
            @rename($new, $path);
            
            unset($thumb);
            
            return $path;
        } else {
            return false;
        }
    }    

    public function direct($uploads_dir) {
        return $this->upload($uploads_dir);
    }

}