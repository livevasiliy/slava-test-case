<?php

declare(strict_types=1);

namespace App\FileReaders;

use App\Exceptions\FilePathIsNotSetException;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use Redis;

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

        $client = new Redis;
        $client->connect(
            config('database.redis.default.host'),
            (int) config('database.redis.default.port')
        );
        $pool = new RedisCachePool($client);
        $simpleCache = new SimpleCacheBridge($pool);

        Settings::setCache($simpleCache);

        $spreadsheet = IOFactory::load($this->getFilePath());
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray();
    }
}
