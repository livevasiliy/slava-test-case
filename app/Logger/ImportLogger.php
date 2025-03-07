<?php

declare(strict_types=1);

namespace App\Logger;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class ImportLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        Log::channel('import_errors')->emergency($this->interpolate($message, $context));
    }

    public function alert($message, array $context = []): void
    {
        Log::channel('import_errors')->alert($this->interpolate($message, $context));
    }

    public function critical($message, array $context = []): void
    {
        Log::channel('import_errors')->critical($this->interpolate($message, $context));
    }

    public function error($message, array $context = []): void
    {
        Log::channel('import_errors')->error($this->interpolate($message, $context));
    }

    public function warning($message, array $context = []): void
    {
        Log::channel('import_errors')->warning($this->interpolate($message, $context));
    }

    public function notice($message, array $context = []): void
    {
        Log::channel('import_errors')->notice($this->interpolate($message, $context));
    }

    public function info($message, array $context = []): void
    {
        Log::channel('import_errors')->info($this->interpolate($message, $context));
    }

    public function debug($message, array $context = []): void
    {
        Log::channel('import_errors')->debug($this->interpolate($message, $context));
    }

    public function log($level, $message, array $context = []): void
    {
        Log::channel('import_errors')->log($level, $this->interpolate($message, $context));
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
