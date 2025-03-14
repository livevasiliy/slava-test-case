<?php

declare(strict_types=1);

namespace App\FileReaders;

use App\Exceptions\FilePathIsNotSetException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxFileReader extends FileReader
{
    /**
     * @throws FilePathIsNotSetException
     */
    public function read(): array
    {
        if (is_null($this->getFilePath())) {
            throw new FilePathIsNotSetException;
        }

        $client = new \Redis();
        $client->connect('127.0.0.1', 6379);
        $pool = new \Cache\Adapter\Redis\RedisCachePool($client);
        $simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);

        \PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);

        $spreadsheet = IOFactory::load($this->getFilePath());
        $sheet = $spreadsheet->getActiveSheet();


        return $sheet->toArray();
    }
}
