<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Http;

use EasyAdm\Kernel\Exceptions\InvalidArgumentException;
use EasyAdm\Kernel\Exceptions\RuntimeException;
use EasyAdm\Kernel\Support\File;

/**
 * Class StreamResponse.
 */
class StreamResponse extends Response
{
    /**
     * @return bool|int
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    public function save(string $directory, string $filename = '', bool $appendSuffix = true)
    {
        $this->getBody()->rewind();

        $directory = rtrim($directory, '/');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true); // @codeCoverageIgnore
        }

        if (! is_writable($directory)) {
            throw new InvalidArgumentException(sprintf("'%s' is not writable.", $directory));
        }

        $contents = $this->getBody()->getContents();

        if (empty($contents) || $contents[0] === '{') {
            throw new RuntimeException('Invalid media response content.');
        }

        if (empty($filename)) {
            if (preg_match('/filename="(?<filename>.*?)"/', $this->getHeaderLine('Content-Disposition'), $match)) {
                $filename = $match['filename'];
            } else {
                $filename = md5($contents);
            }
        }

        if ($appendSuffix && empty(pathinfo($filename, PATHINFO_EXTENSION))) {
            $filename .= File::getStreamExt($contents);
        }

        file_put_contents($directory . '/' . $filename, $contents);

        return $filename;
    }

    /**
     * @return bool|int
     *
     * @throws \AdMarketingAPI\Kernel\Exceptions\InvalidArgumentException
     * @throws \AdMarketingAPI\Kernel\Exceptions\RuntimeException
     */
    public function saveAs(string $directory, string $filename, bool $appendSuffix = true)
    {
        return $this->save($directory, $filename, $appendSuffix);
    }
}
