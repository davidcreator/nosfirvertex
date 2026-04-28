<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Language
{
    private string $baseDirectory;
    private string $locale;
    private string $fallbackLocale;
    private array $availableLocales = [];
    private array $data = [];
    private array $phraseMap = [];

    public function __construct(string $baseDirectory, string $locale = 'pt-br', string $fallbackLocale = 'pt-br')
    {
        $this->baseDirectory = rtrim(str_replace('\\', '/', $baseDirectory), '/');
        $this->availableLocales = self::discoverLocales($this->baseDirectory);

        if ($this->availableLocales === []) {
            $this->availableLocales = ['pt-br'];
        }

        $requestedFallback = $this->normalizeLocale($fallbackLocale);
        $this->fallbackLocale = in_array($requestedFallback, $this->availableLocales, true)
            ? $requestedFallback
            : $this->availableLocales[0];

        $requestedLocale = $this->normalizeLocale($locale);
        $this->locale = in_array($requestedLocale, $this->availableLocales, true)
            ? $requestedLocale
            : $this->fallbackLocale;

        $this->loadAll();
    }

    public static function discoverLocales(string $baseDirectory): array
    {
        $baseDirectory = rtrim(str_replace('\\', '/', $baseDirectory), '/');

        if (!is_dir($baseDirectory)) {
            return [];
        }

        $entries = scandir($baseDirectory);
        if ($entries === false) {
            return [];
        }

        $locales = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $baseDirectory . '/' . $entry;
            if (!is_dir($path)) {
                continue;
            }

            $normalized = strtolower(trim($entry));
            if ($normalized !== '') {
                $locales[] = $normalized;
            }
        }

        sort($locales);

        return array_values(array_unique($locales));
    }

    public function get(string $key, array $replace = [], string $default = ''): string
    {
        $value = null;

        if (array_key_exists($key, $this->data)) {
            $value = $this->data[$key];
        } else {
            $value = $this->getByPath($key);
        }

        if (!is_string($value)) {
            $value = $default !== '' ? $default : $key;
        }

        return $this->replaceTokens($value, $replace);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    public function getHtmlLang(): string
    {
        return str_replace('_', '-', ucwords(str_replace('-', '_', $this->locale), '_'));
    }

    public function translateMarkup(string $content): string
    {
        if ($content === '' || $this->locale === $this->fallbackLocale || $this->phraseMap === []) {
            return $content;
        }

        return strtr($content, $this->phraseMap);
    }

    private function normalizeLocale(string $locale): string
    {
        return strtolower(trim(str_replace('_', '-', $locale)));
    }

    private function loadAll(): void
    {
        $fileMap = [];

        foreach ($this->collectRelativeFiles($this->fallbackLocale) as $relativeFile) {
            $fileMap[$relativeFile] = true;
        }

        foreach ($this->collectRelativeFiles($this->locale) as $relativeFile) {
            $fileMap[$relativeFile] = true;
        }

        $files = array_keys($fileMap);
        sort($files);

        foreach ($files as $relativeFile) {
            $fallbackPath = $this->buildFilePath($this->fallbackLocale, $relativeFile);
            $localePath = $this->buildFilePath($this->locale, $relativeFile);

            if (is_file($fallbackPath)) {
                $this->mergeFromFile($fallbackPath);
            }

            if ($this->locale !== $this->fallbackLocale && is_file($localePath)) {
                $this->mergeFromFile($localePath);
            }
        }
    }

    private function collectRelativeFiles(string $locale): array
    {
        $localeDirectory = $this->baseDirectory . '/' . $locale;
        if (!is_dir($localeDirectory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localeDirectory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item instanceof \SplFileInfo || !$item->isFile()) {
                continue;
            }

            if (strtolower($item->getExtension()) !== 'php') {
                continue;
            }

            $fullPath = str_replace('\\', '/', $item->getPathname());
            $relative = ltrim(substr($fullPath, strlen($localeDirectory)), '/');
            if ($relative !== '') {
                $files[] = $relative;
            }
        }

        sort($files);

        return array_values(array_unique($files));
    }

    private function buildFilePath(string $locale, string $relativeFile): string
    {
        return $this->baseDirectory . '/' . $locale . '/' . ltrim(str_replace('\\', '/', $relativeFile), '/');
    }

    private function mergeFromFile(string $file): void
    {
        $loaded = require $file;
        if (!is_array($loaded)) {
            return;
        }

        $phrases = $loaded['__phrases'] ?? null;
        if (is_array($phrases)) {
            $this->phraseMap = array_replace($this->phraseMap, $this->sanitizePhraseMap($phrases));
            unset($loaded['__phrases']);
        }

        $this->data = array_replace_recursive($this->data, $loaded);
    }

    private function sanitizePhraseMap(array $phraseMap): array
    {
        $result = [];

        foreach ($phraseMap as $source => $target) {
            $source = (string) $source;
            $target = (string) $target;

            if ($source === '') {
                continue;
            }

            $result[$source] = $target;
        }

        return $result;
    }

    private function getByPath(string $key): mixed
    {
        if ($key === '') {
            return null;
        }

        $segments = explode('.', $key);
        $value = $this->data;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function replaceTokens(string $text, array $replace): string
    {
        if ($replace === []) {
            return $text;
        }

        $result = $text;
        foreach ($replace as $token => $value) {
            $result = str_replace('{' . (string) $token . '}', (string) $value, $result);
        }

        return $result;
    }
}
