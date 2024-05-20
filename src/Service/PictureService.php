<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function addAvatar(UploadedFile $picture, ?int $width=250, ?int $height=250) {
        $filename = md5(uniqid(rand(), true)). '.png';

        $pictureInfos = getimagesize($picture);

        if($pictureInfos === false) {
            throw new Exception('format d\'image incorrect');
        }

        switch ($pictureInfos['mime'])
        {
            case 'image/webp' :
                $pictureSource = imagecreatefromwebp($picture);
                break;
            case 'image/jpeg' :
                $pictureSource = imagecreatefromjpeg($picture);
                break;
            case 'image/png' :
                $pictureSource = imagecreatefrompng($picture);
                break;
            default :
                throw new Exception('format d\'image incorrect');            
        }

        $pictureWidth = $pictureInfos[0];
        $pictureHeight = $pictureInfos[1];

        switch($pictureWidth <=> $pictureHeight)
        {
            case -1 :
                $squareSize = $pictureWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 0 :
                $squareSize = $pictureWidth;
                $src_x = 0;
                $src_y = ($pictureHeight - $squareSize)/2;
                break;
            case 1 :
                $squareSize = $pictureHeight;
                $src_y = 0;
                $src_x = ($pictureWidth - $squareSize)/2;
                break;
        }

        $resizedPicture = imagecreatetruecolor($width, $height);

        imagecopyresampled($resizedPicture, $pictureSource, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);

        $path = $this->params->get('avatar_directory');
        if(!file_exists($path))
        {
            mkdir($path, 0755, true);
        }

        imagepng($resizedPicture, $path.$filename);

        return $filename;
    }

    public function deleteAvatar(string $filename) {

        $success = false;

        if($filename !== 'default.png')
        {
            
            $path = $this->params->get('avatar_directory').$filename;
            
            if(file_exists($path))
            {
                unlink($path);
                $succes = true;
            }
        }

        return $success;
    }
}