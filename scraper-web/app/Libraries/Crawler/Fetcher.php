<?php

namespace App\Libraries\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Fetcher
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'         => 60,
            'verify'          => false,
            'allow_redirects' => [
                'max'             => 5,
                'protocols'       => ['http', 'https'],
                'track_redirects' => true
            ],
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection'      => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest'  => 'document',
                'Sec-Fetch-Mode'  => 'navigate',
                'Sec-Fetch-Site'  => 'none',
                'Sec-Fetch-User'  => '?1',
                'Cache-Control'   => 'max-age=0',
            ],
            'curl' => [
                // PAKSA HTTP/1.1 - Banyak WAF memutus koneksi HTTP/2 dari library non-browser
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_IPRESOLVE    => CURL_IPRESOLVE_V4,
                // Hilangkan header 'Expect: 100-continue' yang menyebabkan error 'rewind'
                CURLOPT_HTTPHEADER   => ['Expect:'],
                // Beberapa server butuh SSL Cipher tertentu
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ],
            'expect' => false,
        ]);
    }

    public function get($url)
    {
        try {

            $response = $this->client->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("HTTP Error: " . $response->getStatusCode());
            }

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $msg = "Server merespon dengan status: " . $e->getResponse()->getStatusCode();
            }
            throw new \Exception("Gagal mengambil data dari $url. Detail: " . $msg);
        } catch (\Exception $e) {
            throw new \Exception("Gagal: " . $e->getMessage());
        }
    }
}
