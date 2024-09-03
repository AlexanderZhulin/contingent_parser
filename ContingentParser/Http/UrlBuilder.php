<?php
namespace ContingentParser\Http;

class UrlBuilder
{
    public function __construct() {}
    /**
     * Строит валидный URL сайта
     * @param string $url
     * Изначальный URL
     * @return string
     */
    public function build(string $url) : string
    {
        // Строит -> https://<base_uri>
        $url = trim(strtolower($url));
        $url = preg_replace('/\s+/', '', $url);
        $url = str_replace("www/", "www.", $url);
        $url = str_replace("http:\\\\", "", $url);
        if (!preg_match('#^https?://#', $url)) {
            $url = "http://$url";
        }
        // $url = str_replace("http://", "https://", $url);
        $arr = parse_url($url);
        $url = $arr['scheme'] . '://' . $arr['host'] . '/';
        // $url = str_replace("www.", "", $url);
        $url = str_replace("_", "/", $url);
        return trim($url);
    }
}
