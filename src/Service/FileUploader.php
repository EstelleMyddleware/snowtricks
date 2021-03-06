<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;
    private $logger;

    public function __construct($targetDirectory, SluggerInterface $slugger, LoggerInterface $logger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            $this->logger->error($e->getMessage().' '.$e->getFile().' line : '.$e->getLine());
            throw new FileException('Failed to upload file : '.$e->getMessage().' on '.$e->getFile().' line : '.$e->getLine());
        }

        return $fileName;
    }

    public function remove($fileName)
    {
        // Physically remove the file from the server
        if (file_exists($this->getTargetDirectory().'/'.$fileName)) {
            unlink($this->getTargetDirectory().'/'.$fileName);
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function loadFromOtherDir(File $file)
    {
        $originalFilename = pathinfo($file->getFileName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            $this->logger->error($e->getMessage().' '.$e->getFile().' line : '.$e->getLine());
            throw new FileException('Failed to load file : '.$e->getMessage().' on '.$e->getFile().' line : '.$e->getLine());
        }

        return $fileName;
    }
}
