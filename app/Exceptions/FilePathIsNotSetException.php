<?php

namespace App\Exceptions;

use Exception;

class FilePathIsNotSetException extends Exception
{
    protected $message = 'File path is not set.';
}
