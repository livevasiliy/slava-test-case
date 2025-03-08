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

        $spreadsheet = IOFactory::load($this->getFilePath());
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray();
    }
}
