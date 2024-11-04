<?php
namespace SvedenParser\Http;
use SvedenParser\Logger\HtmlLogger;

final class UrlBuilder
{
    public const URL_PATTERN = '/^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:[-a-zA-Z0-9()@:%_\+.~#?&\/=]*)$/';
    public const PARAMS_URL = [
        [
            'scheme' => 'https',
            'isPrefix' => true,
            'slash' => true,
        ],
        [
            'scheme' => 'https',
            'isPrefix' => false,
            'slash' => true,
        ],
        [
            'scheme' => 'https',
            'isPrefix' => true,
            'slash' => false,
        ],
        [
            'scheme' => 'https',
            'isPrefix' => false,
            'slash' => false,
        ],
        [
            'scheme' => 'http',
            'isPrefix' => true,
            'slash' => true,
        ],
        [
            'scheme' => 'http',
            'isPrefix' => false,
            'slash' => true,
        ],
        [
            'scheme' => 'http',
            'isPrefix' => true,
            'slash' => false,
        ],
        [
            'scheme' => 'http',
            'isPrefix' => false,
            'slash' => false,
        ],
    ];
    /**
     * Строит валидный URL сайта
     * @param string $url Изначальный URL
     * @return string
     */
    public static function build(string $url, array $params): string
    {
        $url = self::deleteSpaces($url);
        $url = self::fixSpecificErrors($url);
        $domain = self::getDomain(parse_url($url), $params);
        $url = $params['scheme'] . "://" . $domain . '/';
        
        return $url;
    }
    // удалить пробелы 
    private static function deleteSpaces(string $url): string
    {
        $url = trim(strtolower($url));
        $url = preg_replace('/\s+/', '', $url);
        return $url;
    }
    // исправить специфичные ошибки
    private static function fixSpecificErrors(string $url): string 
    {
        $url = str_replace("www/", "www.", $url);
        $url = str_replace("http:\\\\", "", $url);
        return $url;
    }

    private static function getDomain(array $url, array $params): string
    {
        if (array_key_exists('host', $url)) {
            $domain = $url['host'];
        } else {
            $domain = $url['path'];
        }
        if ($params['isPrefix'] && strpos($domain, 'www.') === false) {
            return 'www.' . $domain;
        } 
        if (!$params['isPrefix'] && strpos($domain, 'www.') !== false) {
            return str_replace("www.", "", $domain);
        }
        return $domain;
    }

    public static function isUrl(string $uri): bool
    {
        return preg_match(UrlBuilder::URL_PATTERN, $uri);
    }

    public static function addUri(string $url, string $uri): string
    {
        if (!$uri)
            return $url;

        $url .= substr($url, -1) == '/' ? '' : '/';
        $url .= substr($uri, 0, 1) == '/' ? substr($uri, 1) : $uri;
        return $url;
    }

    public static function checkUri(string $uri): bool
    {
        if (str_ends_with($uri, ".pdf")
            || str_ends_with($uri, ".docx")
            || str_ends_with($uri, ".doc")
            || str_starts_with($uri, "javascript")
        ) {
            return false;
        }
        return true;
    }

    public static function slashIsFirst(string $uri): bool
    {
        return 0 === strpos($uri, '/');
    }

    public static function isPath(string $url): bool
    {
        return array_key_exists('path', parse_url($url));
    }
}