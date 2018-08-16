<?php

class N2AjaxResponse {

    /**
     * @var N2ApplicationType
     */
    private $appType;

    private $isError = false;

    private $response = array(
        'data'         => null,
        'notification' => array()
    );

    public function __construct($appType) {
        $this->appType = $appType;
    }

    public function error($showNotification = true) {
        $this->isError = true;
        $this->respond(null, $showNotification);
    }

    public function respond($data = null, $showNotification = true) {
        $this->response['data'] = $data;

        self::fix_output_buffer();

        if ($showNotification) {
            $this->response['notification'] = N2Message::showAjax();
        }
        header("Content-Type: application/json");
        if ($this->isError) {
            header("HTTP/1.0 403 Forbidden");
        }
        echo json_encode($this->response);
        n2_exit(true);
    }

    public function redirect($url) {

        self::fix_output_buffer();

        $this->response['redirect'] = $this->appType->router->createUrl($url);
        echo json_encode($this->response);
        n2_exit(true);
    }

    private static function fix_output_buffer() {

        $ob_list_handlers       = ob_list_handlers();
        $ob_list_handlers_count = count($ob_list_handlers);

        $exclude = array(
            'ob_gzhandler',
            'zlib output compression'
        );

        if ($ob_list_handlers_count && !in_array($ob_list_handlers[$ob_list_handlers_count - 1], $exclude)) {
            ob_clean();
        }
    }
}