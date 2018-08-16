<?php

class N2 {

    public static $version = '2.0.21';

    private static $api = 'https://secure.nextendweb.com/api/api.php';

    private static $api2 = 'https://api.nextendweb.com/v1/';

    public static function api($posts, $returnUrl = false) {

        if (N2Settings::get('api-secondary', 0)) {
            $api = self::$api2;
        } else {
            $api = self::$api;
        }

        if ($returnUrl) {
            $posts_default = array(
                'platform' => N2Platform::getPlatform()
            );

            return $api . '?' . http_build_query($posts + $posts_default);
        }
        if (class_exists('JHttp')) {
            $posts_default = array(
                'platform' => N2Platform::getPlatform()
            );

            $client = new JHttp();
            try {
                $response = $client->post($api, http_build_query($posts + $posts_default, '', '&'), array('Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'), 5);
            } catch (Exception $e) {
            }
            if (isset($response) && $response && $response->code != '200') {

                if (isset($response->headers['Content-Type'])) {
                    $contentType = $response->headers['Content-Type'];
                }
                $data = $response->body;
            } else {
                /*N2Message::error(n2_('Unable to contact with the licensing server. Possible reasons might be the CURL not enabled in php.ini, call to remote url(secure.nextenedweb.com) is disabled on your server. Contact your server host, and ask them to check these two things!'));
                return array(
                    'status' => 'ERROR_HANDLED'
                );*/
            }
        }
    

        if (!isset($data)) {
            if (function_exists('curl_init') && function_exists('curl_exec') && N2Settings::get('curl', 1)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api);

                $posts_default = array(
                    'platform' => N2Platform::getPlatform()
                );
                curl_setopt($ch, CURLOPT_POSTFIELDS, $posts + $posts_default);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
                curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);

                if (N2Settings::get('curl-clean-proxy', 0)) {
                    curl_setopt($ch, CURLOPT_PROXY, '');
                }
                $data        = curl_exec($ch);
                $errorNumber = curl_errno($ch);
                if ($errorNumber == 60 || $errorNumber == 77) {
                    curl_setopt($ch, CURLOPT_CAINFO, N2LIBRARY . '/cacert.pem');
                    $data = curl_exec($ch);
                }
                $contentType     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $error           = curl_error($ch);
                $curlErrorNumber = curl_errno($ch);
                curl_close($ch);

                if ($curlErrorNumber) {
                    N2Message::error($curlErrorNumber . $error);

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
            } else {
                $posts_default = array(
                    'platform' => N2Platform::getPlatform()
                );

                $opts    = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($posts + $posts_default)
                    )
                );
                $context = stream_context_create($opts);
                $data    = file_get_contents($api, false, $context);
                if ($data === false) {
                    N2Message::error(n2_('CURL disabled in your php.ini configuration. Please enable it!'));

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
                $headers = self::parseHeaders($http_response_header);
                if ($headers['status'] != '200') {
                    N2Message::error(n2_('Unable to contact with the licensing server, please try again later!'));

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
                if (isset($headers['content-type'])) {
                    $contentType = $headers['content-type'];
                }
            }
        }

        switch ($contentType) {
            case 'text/html; charset=UTF-8':
                //CloudFlare challenge
                preg_match('/"your_ip">.*?:[ ]*(.*?)<\/span>/', $data, $matches);
                if (count($matches)) {
                    $blockedIP = $matches[1];

                    N2Message::error(sprintf('Your ip address (%s) is blocked by our hosting provider.<br>Please try: Global Settings -> Framework settings -> Secondary server -> On, or contact us (support@nextendweb.com) with your ip to whitelist it.', $blockedIP));

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }

                N2Message::error(sprintf('Unexpected response from the API.<br>Please try: Global Settings -> Framework settings -> Secondary server -> On, contact us (support@nextendweb.com) with the following log:') . '<br><textarea style="width: 100%;height:200px;font-size:8px;">' . base64_encode($data) . '</textarea>');

                return array(
                    'status' => 'ERROR_HANDLED'
                );
                break;
            case 'application/json':
                return json_decode($data, true);
        }

        return $data;
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