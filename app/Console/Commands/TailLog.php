<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SplFileObject;

class TailLog extends Command
{
    protected $signature = 'logs:tail {--lines=150} {--file=laravel} {--redact}';

    protected $description = 'Print the last N lines from a log file, optionally redacting secrets.';

    public function handle(): int
    {
        $lines = (int) $this->option('lines');
        $fileOpt = $this->option('file');

        $file = $fileOpt === 'laravel' ?
            storage_path('logs/laravel.log') :
            $fileOpt;

        if (! is_readable($file)) {
            $this->error("Not readable: {$file}");

            return 1;
        }

        $out = $this->tail($file, $lines);

        if ($this->option('redact')) {
            $out = array_map([$this, 'redact'], $out);
        }

        $this->line(implode(PHP_EOL, $out));

        return 0;
    }

    private function tail(string $path, int $n): array
    {
        $f = new SplFileObject($path, 'r');
        $f->seek(PHP_INT_MAX);
        $last = $f->key();
        $buf = [];
        for ($i = $last; $i >= 0 && count($buf) < $n; $i--) {
            $f->seek($i);
            $buf[] = rtrim((string) $f->current(), "\r\n");
        }

        return array_reverse($buf);
    }

    private function redact(string $line): string
    {
        // Basic PII/secret redaction; extend as needed
        $line = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $line);
        $line = preg_replace('/(?i)bearer\s+[a-z0-9\-_\.]+/', 'Bearer [token]', $line);
        $line = preg_replace('/password=([^&\s]+)/i', 'password=[redacted]', $line);
        $line = preg_replace('/api(_)?key=([^&\s]+)/i', 'apiKey=[redacted]', $line);

        return $line;
    }
}
