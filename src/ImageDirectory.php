<?php

namespace PatrickBierans\Directory;

use PatrickBierans\Container\SolidContainer;

/**
 * A specialization allowing you to do image galleries.
 * files and directories staring with "." or "_" are skipped.
 * Files are sorted ascending.
 * you can add a title and a description via files:
 * > a.jpg
 * > a.jpg.title.txt
 * > a.jpg.description.txt
 * and access them via:
 * foreach (SimpleDirectory(".") as $Image){
 *   $Image->fileinfo // is a SplFileInfo
 *   $Image->filename
 *   $Image->title
 *   $Image->description
 * }
 * it will create these files for you if missing!
 */
class ImageDirectory extends SimpleDirectory {

    /**
     * ImageDirectory constructor.
     *
     * @param string $path
     * @param array $filter allowed file extensions
     */
    public function __construct($path, $filter = ['jpg', 'png', 'gif']) {
        parent::__construct($path, $filter);
    }

    /**
     * @param \SplFileInfo $fileInfo
     */
    public function addEntry(\SplFileInfo $fileInfo): void {
        if ($fileInfo->isFile()) {
            $fname = substr($fileInfo->getBasename(), 0, -1 - \strlen($fileInfo->getExtension()));
            $Data = [
                'fileinfo' => $fileInfo,
                'basename' => $fileInfo->getBasename(),
                'filename' => $fileInfo->getRealPath(),
            ];
            $allowedMeta = ['title', 'description', 'comment'];
            foreach ($allowedMeta as $meta) {
                $file = $fileInfo->getRealPath() . '.' . $meta . '.txt';
                if (file_exists($file)) {
                    $Data[$meta] = trim(file_get_contents($file));
                } else {
                    if (self::$autoCreateMetaFiles) {
                        file_put_contents($file, $fname);
                        chmod($file, 666);
                    }
                }
            }
            $this->Data[] = new SolidContainer($Data);
        }
    }

    /**
     * Here we DO want to sort them ascending
     * @return bool
     */
    protected function sortEntries(): bool {
        usort($this->Data, function ($a, $b) {
            /**
             * @var SolidContainer $a
             * @var SolidContainer $b
             */
            /** @noinspection PhpUndefinedFieldInspection */
            return ($a->filename < $b->filename) ? -1 : 1;
        });
        return true;
    }

}