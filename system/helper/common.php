<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $configuredBase = configured_base_url();
        if ($configuredBase !== null) {
            if ($path === '') {
                return $configuredBase;
            }

            return rtrim($configuredBase, '/') . '/' . ltrim($path, '/');
        }

        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $scriptName = rtrim($scriptName, '/');

        $base = preg_replace('#/(admin|catalog|install)$#', '', $scriptName) ?: '';
        $base = rtrim((string) $base, '/');

        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }

        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}

if (!function_exists('configured_base_url')) {
    function configured_base_url(): string|null
    {
        static $isLoaded = false;
        static $cachedBaseUrl = null;

        if ($isLoaded) {
            return $cachedBaseUrl;
        }

        $isLoaded = true;

        if (!defined('DIR_SYSTEM')) {
            return null;
        }

        $installedConfigFile = DIR_SYSTEM . '/config/installed.php';
        if (!is_file($installedConfigFile)) {
            return null;
        }

        $config = require $installedConfigFile;
        $baseUrl = is_array($config) ? (string) ($config['app']['base_url'] ?? '') : '';
        $baseUrl = trim($baseUrl);

        if ($baseUrl === '') {
            return null;
        }

        $cachedBaseUrl = rtrim($baseUrl, '/') . '/';

        return $cachedBaseUrl;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url($path);
    }
}

if (!function_exists('sanitize_html_fragment')) {
    function sanitize_html_fragment(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (!class_exists(\DOMDocument::class)) {
            return nv_plain_safe_html($html);
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<div id="nv-root">' . $html . '</div>';
        $previousLibxml = libxml_use_internal_errors(true);

        try {
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        } catch (\Throwable) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxml);

            return nv_plain_safe_html($html);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxml);

        $root = $doc->getElementById('nv-root');
        if (!$root instanceof \DOMElement) {
            return nv_plain_safe_html($html);
        }

        nv_sanitize_dom_node($root, $doc);

        $output = '';
        $children = [];
        foreach ($root->childNodes as $childNode) {
            $children[] = $childNode;
        }

        foreach ($children as $childNode) {
            $output .= (string) $doc->saveHTML($childNode);
        }

        return trim($output);
    }
}

if (!function_exists('nv_plain_safe_html')) {
    function nv_plain_safe_html(string $html): string
    {
        // Secure fallback when ext-dom is unavailable: render user text only.
        $withLineBreaks = preg_replace('/<\s*(br|\/p|\/div|\/li)\s*\/?>/i', "\n", $html) ?? $html;
        $plain = trim(strip_tags($withLineBreaks));

        if ($plain === '') {
            return '';
        }

        return nl2br(e($plain));
    }
}

if (!function_exists('nv_sanitize_dom_node')) {
    function nv_sanitize_dom_node(\DOMNode $node, \DOMDocument $doc): void
    {
        $allowedTags = nv_allowed_html_tags();
        $children = [];

        foreach ($node->childNodes as $childNode) {
            $children[] = $childNode;
        }

        foreach ($children as $childNode) {
            if ($childNode instanceof \DOMText) {
                continue;
            }

            if (!($childNode instanceof \DOMElement)) {
                $node->removeChild($childNode);
                continue;
            }

            $tag = strtolower($childNode->tagName);
            if (!in_array($tag, $allowedTags, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button'], true)) {
                    $node->removeChild($childNode);
                    continue;
                }

                while ($childNode->firstChild !== null) {
                    $node->insertBefore($childNode->firstChild, $childNode);
                }

                $node->removeChild($childNode);
                continue;
            }

            nv_sanitize_dom_attributes($childNode);
            nv_sanitize_dom_node($childNode, $doc);
        }
    }
}

if (!function_exists('nv_sanitize_dom_attributes')) {
    function nv_sanitize_dom_attributes(\DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        $allowedAttributes = nv_allowed_html_attributes();
        $allowedForTag = $allowedAttributes[$tag] ?? [];
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attributeName) {
            $name = strtolower($attributeName);
            $value = trim((string) $element->getAttribute($attributeName));

            if (str_starts_with($name, 'on')) {
                $element->removeAttribute($attributeName);
                continue;
            }

            if (!in_array($name, $allowedForTag, true)) {
                $element->removeAttribute($attributeName);
                continue;
            }

            if ($tag === 'a' && $name === 'href') {
                if (!nv_is_safe_link_href($value)) {
                    $element->removeAttribute($attributeName);
                    continue;
                }

                $element->setAttribute('href', $value);
                continue;
            }

            if ($tag === 'a' && $name === 'target') {
                if ($value !== '_blank') {
                    $element->removeAttribute($attributeName);
                    continue;
                }
            }

            if ($tag === 'a' && $name === 'rel') {
                $allowedRel = ['noopener', 'noreferrer', 'nofollow', 'ugc', 'sponsored'];
                $tokens = preg_split('/\s+/', strtolower($value)) ?: [];
                $tokens = array_values(array_unique(array_intersect($tokens, $allowedRel)));

                if ($tokens === []) {
                    $element->removeAttribute($attributeName);
                } else {
                    $element->setAttribute('rel', implode(' ', $tokens));
                }
            }
        }

        if ($tag === 'a' && strtolower(trim((string) $element->getAttribute('target'))) === '_blank') {
            $rel = strtolower(trim((string) $element->getAttribute('rel')));
            $relTokens = $rel !== '' ? preg_split('/\s+/', $rel) : [];
            $relTokens = is_array($relTokens) ? $relTokens : [];
            $relTokens[] = 'noopener';
            $relTokens[] = 'noreferrer';
            $relTokens = array_values(array_unique(array_filter($relTokens, static fn (string $token): bool => $token !== '')));

            $element->setAttribute('rel', implode(' ', $relTokens));
        }
    }
}

if (!function_exists('nv_allowed_html_tags')) {
    function nv_allowed_html_tags(): array
    {
        return ['div', 'strong', 'em', 'a', 'p', 'span', 'br', 'ul', 'ol', 'li'];
    }
}

if (!function_exists('nv_allowed_html_attributes')) {
    function nv_allowed_html_attributes(): array
    {
        return [
            'a' => ['href', 'target', 'rel', 'title'],
            'div' => [],
            'strong' => [],
            'em' => [],
            'p' => [],
            'span' => [],
            'br' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
        ];
    }
}

if (!function_exists('nv_is_safe_link_href')) {
    function nv_is_safe_link_href(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        $href = trim($href);
        if (preg_match('/[\x00-\x1F\x7F]/', $href) === 1) {
            return false;
        }

        $parsed = parse_url($href);
        if (!is_array($parsed) || !isset($parsed['scheme'])) {
            return str_starts_with($href, '/')
                || str_starts_with($href, '#')
                || str_starts_with($href, '?')
                || str_starts_with($href, './')
                || str_starts_with($href, '../');
        }

        return in_array(strtolower((string) $parsed['scheme']), ['http', 'https', 'mailto', 'tel'], true);
    }
}

if (!function_exists('nv_set_language')) {
    function nv_set_language(mixed $language): void
    {
        $GLOBALS['nv_language'] = $language;
    }
}

if (!function_exists('nv_language')) {
    function nv_language(): mixed
    {
        return $GLOBALS['nv_language'] ?? null;
    }
}

if (!function_exists('lang')) {
    function lang(string $key, array $replace = [], string $default = ''): string
    {
        $language = nv_language();

        if (is_object($language) && method_exists($language, 'get')) {
            return (string) $language->get($key, $replace, $default);
        }

        $value = $default !== '' ? $default : $key;
        foreach ($replace as $token => $tokenValue) {
            $value = str_replace('{' . (string) $token . '}', (string) $tokenValue, $value);
        }

        return $value;
    }
}

if (!function_exists('current_locale')) {
    function current_locale(): string
    {
        $language = nv_language();

        if (is_object($language) && method_exists($language, 'getLocale')) {
            return (string) $language->getLocale();
        }

        return 'pt-br';
    }
}

if (!function_exists('available_locales')) {
    function available_locales(): array
    {
        $language = nv_language();

        if (is_object($language) && method_exists($language, 'getAvailableLocales')) {
            $locales = $language->getAvailableLocales();

            return is_array($locales) ? $locales : ['pt-br'];
        }

        return ['pt-br'];
    }
}

if (!function_exists('html_lang')) {
    function html_lang(): string
    {
        $language = nv_language();

        if (is_object($language) && method_exists($language, 'getHtmlLang')) {
            return (string) $language->getHtmlLang();
        }

        return 'pt-BR';
    }
}

if (!function_exists('translate_markup')) {
    function translate_markup(string $content): string
    {
        $language = nv_language();

        if (is_object($language) && method_exists($language, 'translateMarkup')) {
            return (string) $language->translateMarkup($content);
        }

        return $content;
    }
}

if (!function_exists('locale_switch_url')) {
    function locale_switch_url(string $locale): string
    {
        $locale = strtolower(trim(str_replace('_', '-', $locale)));
        if ($locale === '') {
            return (string) ($_SERVER['REQUEST_URI'] ?? '/');
        }

        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if ($requestUri === '') {
            return '?lang=' . rawurlencode($locale);
        }

        $parts = parse_url($requestUri);
        $path = (string) ($parts['path'] ?? '');
        if ($path === '') {
            $path = '/';
        }

        $query = [];
        if (isset($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }

        $query['lang'] = $locale;
        $queryString = http_build_query($query);

        return $queryString === '' ? $path : ($path . '?' . $queryString);
    }
}
