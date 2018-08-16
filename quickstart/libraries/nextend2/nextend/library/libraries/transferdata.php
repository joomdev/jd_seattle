<?php

class N2TransferData {

    public static function get($url) {

        if (function_exists('curl_init') && function_exists('curl_exec') && N2Settings::get('curl', 1)) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);


            if (N2Settings::get('curl-clean-proxy', 0)) {
                curl_setopt($ch, CURLOPT_PROXY, '');
            }

            $data = curl_exec($ch);
            if (curl_errno($ch) == 60) {
                curl_setopt($ch, CURLOPT_CAINFO, N2LIBRARY . '/cacert.pem');
                $data = curl_exec($ch);
            }
            $error           = curl_error($ch);
            $curlErrorNumber = curl_errno($ch);
            curl_close($ch);

            if ($curlErrorNumber) {
                N2Message::error($curlErrorNumber . $error);

                return false;
            }

            return $data;
        } else {

            if (!ini_get('allow_url_fopen')) {
                N2Message::error(n2_('The <a href="http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">allow_url_fopen</a> is not turned on in your server, which is necessary to read rss feeds. You should contact your server host, and ask them to enable it!'));

                return false;
            }

            $opts    = array(
                'http' => array(
                    'method' => 'GET'
                )
            );
            $context = stream_context_create($opts);
            $data    = file_get_contents($url, false, $context);
            if ($data === false) {
                N2Message::error(n2_('CURL disabled in your php.ini configuration. Please enable it!'));

                return false;
            }
            $headers = self::parseHeaders($http_response_header);
            if ($headers['status'] != '200') {
                N2Message::error(n2_('Unable to contact with the licensing server, please try again later!'));

                return false;
            }

            return $data;
        }
    }

    private static function parseHeaders(array $headers, $header = null) {
        $output = array();
        if ('HTTP' === substr($headers[0], 0, 4)) {
            list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
            unset($headers[0]);
        }
        foreach ($headers as $v) {
            $h = preg_split('/:\s*/', $v);
            if (count($h) >= 2) {
                $output[strtolower($h[0])] = $h[1];
            }
        }
        if (null !== $header) {
            if (isset($output[strtolower($header)])) {
                return $output[strtolower($header)];
            }

            return;
        }

        return $output;
    }
}