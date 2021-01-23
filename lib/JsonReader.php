<?php

namespace Lib;

use Generator;
use IteratorAggregate;

class JsonReader implements IteratorAggregate
{
    /** @var string */
    private $fileName;

    /** @var int */
    private $chunkSize;

    /** @var resource */
    private $stream;

    /** @var string */
    private $jsonBuffer;

    /**
     * JsonReader constructor.
     * @param string $fileName
     * @param float|int $chunkSize
     */
    public function __construct(string $fileName, $chunkSize = 1024 * 8)
    {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
        $this->stream = fopen($this->fileName, 'r');
        $this->jsonBuffer = '';
    }

    /**
     * @return Generator
     */
    public function getIterator(): Generator
    {
        while ('' !== ($bytes = fread($this->stream, $this->chunkSize))) {
            /**
             * 因为只是作为思路展示，所以这里只针对试题提供的json格式进行处理
             * 如果需要可以处理所有可能json格式，还需要继续需要完善或者使用开源的包
             */
            $bytesLength = strlen($bytes);
            for ($i = 0; $i < $bytesLength; ++$i) {
                $byte = $bytes[$i];
                if ($byte == '[' || $byte == ']') {
                    continue;
                }
                $this->jsonBuffer .= $byte;
                if (substr($this->jsonBuffer, -2) == '},') {
                    yield trim($this->jsonBuffer, ',');
                    $this->jsonBuffer = '';
                    continue;
                }
            }
        }
    }
}