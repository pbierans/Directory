<?php

namespace PatrickBierans\Directory;

use PatrickBierans\Container\SolidContainer;
use PatrickBierans\ContainerIntegration\MagicGetIntegration;

/**
 * Reads all files of a directory recursive into a flat array of paths.
 * files starting with "." or "_" are skipped.
 * You can use it like this:
 * foreach (SimpleDirectory(".") as $file){
 *   $file->fileinfo // is a SplFileInfo
 * }
 * you can add a title and a description via files:
 * > __title.txt
 * > __description.txt
 * and access them via $this->title and $this->description
 * it will create these files for you if missing!
 */
class SimpleDirectory implements \Iterator, \Countable {

    use MagicGetIntegration;

    public static $autoCreateMetaFiles = true;

    /**
     * @var SolidContainer[] <- addEntry()
     */
    protected $Data = [];
    /**
     * @var int
     */
    protected $index = 0;
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $Filter;
    /**
     * @var bool
     */
    protected $recursive = false;

    /**
     * Directory constructor.
     *
     * @param string $path
     * @param null|array $filter
     */
    public function __construct($path, $filter = null) {
        $this->path = $path;
        $this->Filter = $filter;

        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);

        $forbittenFirstChars = ['.', '_'];

        foreach ($iterator as $fileInfo) {
            /**
             * @var \SplFileInfo $fileInfo
             */
            if ($this->Filter === null || \in_array($fileInfo->getExtension(), $this->Filter, false)) {
                $pieces = explode('/', trim($fileInfo->getRealPath(), '/'));
                foreach ($pieces as $piece) {
                    $firstChar = $piece[0];
                    if (\in_array($firstChar, $forbittenFirstChars, true)) {
                        continue 2;
                    }
                }
                $this->addEntry($fileInfo);
            }
        }
        $this->sortEntries();

        $Meta = [];
        $allowedMeta = ['title', 'description', 'comment'];
        foreach ($allowedMeta as $meta) {
            $file = $this->path . '/__' . $meta . '.txt';
            if (file_exists($file)) {
                $Meta[$meta] = trim(file_get_contents($file));
            } else {
                if (self::$autoCreateMetaFiles) {
                    file_put_contents($file, $this->path);
                    chmod($file, 666);
                }
            }
        }
        $this->setMagicGetContainer(new SolidContainer($Meta));
    }

    /**
     * @param \SplFileInfo $fileInfo
     */
    public function addEntry(\SplFileInfo $fileInfo): void {
        $Data = [
            'fileinfo' => $fileInfo
        ];
        $this->Data[] = new SolidContainer($Data);
    }

    /**
     * Might be overwritten...
     * @return bool
     */
    protected function sortEntries(): bool {
        return false;
    }

    /**
     * @return mixed|SolidContainer
     */
    public function current() {
        return $this->Data[$this->index];
    }

    /**
     *
     */
    public function next(): void {
        $this->index++;
    }

    /**
     * @return int|mixed
     */
    public function key() {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool {
        return isset ($this->Data[$this->index]);
    }

    /**
     *
     */
    public function rewind(): void {
        $this->index = 0;
    }

    /**
     * @return int
     */
    public function count(): int {
        return \count($this->Data);
    }

    /**
     *
     */
    public function dump(): void {
        /** @noinspection ForgottenDebugOutputInspection */
        \var_dump($this->Data);
    }

}