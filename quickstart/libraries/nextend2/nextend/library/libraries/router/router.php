<?php

class N2RouterAbstract {

    protected $baseUrl;

    /**
     * @param $info N2ApplicationInfo
     */
    public function __construct($info) {
        $this->baseUrl = $info->getUrl();
    }

    /**
     * @param array|string $url
     *
     * @return string
     */
    public function createUrl($url, $isPost = false) {
        //create url from array
        // [0] = controller/method
        // [1] = query parameters
        if (is_array($url)) {
            $href = $this->route($url[0], (isset($url[1]) ? $url[1] : array()), $isPost);
        } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
            //completed url, no mods, just fun
            $href = $url;
        } elseif (strpos($url, "/")) {
            //create url from string
            //format: controller/method
            $href = $this->route($url, array(), $isPost);
        } else {
            //fake link, replace to hashtag
            $href = "#";
        }

        return $href;
    }

    public function createAjaxUrl($url, $isPost = false) {
        //create url from array
        // [0] = controller/method
        // [1] = query parameters

        $parameters = array('nextendajax' => 1) + N2Form::tokenizeUrl();

        if (!isset($url[1])) {
            $url[1] = $parameters;
        } else {
            $url[1] = array_merge($url[1], $parameters);
        }

        return $this->createUrl($url, $isPost);
    }

    public function route($url, $params = array(), $isPost = false) {


        if (strpos($url, "/") === false) {
            throw new Exception("Invalid action {$url}. Valid format controller/method");
        }

        $parsedAction = explode("/", $url);

        $url = "";

        if (strpos($this->baseUrl, "?") !== false) {
            $url .= $this->baseUrl . "&nextendcontroller=" . $this->normalizeParameter($parsedAction[0]);
        } else {
            $url .= $this->baseUrl . "?nextendcontroller=" . $this->normalizeParameter($parsedAction[0]);
        }

        $url .= "&nextendaction=" . $this->normalizeParameter($parsedAction[1]);

        if ($isPost) {
            $params += N2Form::tokenizeUrl();
        }

        if (count($params)) {
            $url .= "&" . http_build_query($params, null, '&');
        }

        return $url;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function normalizeParameter($string) {
        return str_replace(array(
            "?",
            "&"
        ), "", strtolower(trim($string)));
    }

    public function setMultiSite() {
        return $this;
    }

    public function unSetMultiSite() {
        return $this;
    }

}

N2Loader::import("libraries.router.router", 'platform');