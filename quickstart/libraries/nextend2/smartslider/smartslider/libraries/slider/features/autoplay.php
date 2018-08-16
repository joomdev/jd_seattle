<?php

class N2SmartSliderFeatureAutoplay {

    private $slider;

    public $isEnabled = 0, $isStart = 0, $duration = 8000;
    public $interval = 0, $intervalModifier = 'loop', $intervalSlide = 'current', $allowReStart = 0;
    public $stopOnClick = 1, $stopOnMouseEnter = 1, $stopOnMediaStarted = 1;
    public $resumeOnMouseLeave = 0, $resumeOnMediaEnded = 1, $resumeOnSlideChanged = 0;


    public function __construct($slider) {

        $this->slider = $slider;
        $params       = $slider->params;

        $this->isEnabled = intval($params->get('autoplay', 0));
        $this->isStart   = intval($params->get('autoplayStart', 1));
        $this->duration  = intval($params->get('autoplayDuration', 8000));

        if ($this->duration < 1) {
            $this->duration = 1500;
        }


        list($this->interval, $this->intervalModifier, $this->intervalSlide) = (array)N2Parse::parse($slider->params->get('autoplayfinish', '0|*|loop|*|current'));


        $this->allowReStart = intval($params->get('autoplayAllowReStart', 0));

        $this->interval = intval($this->interval);

        $this->stopOnClick        = intval($params->get('autoplayStopClick', 1));
        $this->stopOnMouse        = $params->get('autoplayStopMouse', 'enter');
        $this->stopOnMediaStarted = intval($params->get('autoplayStopMedia', 1));


        $this->resumeOnClick      = intval($params->get('autoplayResumeClick', 0));
        $this->resumeOnMouse      = $params->get('autoplayResumeMouse', 0);
        $this->resumeOnMediaEnded = intval($params->get('autoplayResumeMedia', 1));


    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['autoplay'] = array(
            'enabled'              => $this->isEnabled,
            'start'                => $this->isStart,
            'duration'             => $this->duration,
            'autoplayToSlide'      => 0,
            'autoplayToSlideIndex' => -1,
            'allowReStart'         => $this->allowReStart,
            'pause'                => array(
                'click'        => $this->stopOnClick,
                'mouse'        => $this->stopOnMouse,
                'mediaStarted' => $this->stopOnMediaStarted
            ),
            'resume'               => array(
                'click'        => $this->resumeOnClick,
                'mouse'        => $this->resumeOnMouse,
                'mediaEnded'   => $this->resumeOnMediaEnded,
                'slidechanged' => $this->resumeOnSlideChanged
            )
        );

        switch ($this->intervalModifier) {
            case 'slide':
                $properties['autoplay']['autoplayToSlide'] = $this->interval;
                if ($this->intervalSlide == 'next') {
                    $properties['autoplay']['autoplayToSlide']++;
                }
                break;
            case 'slideindex':
                $interval = max(1, $this->interval);
                if ($this->intervalSlide == 'next') {
                    $interval++;
                }

                if ($interval > count($this->slider->slides)) {
                    $interval = 1;
                }

                $properties['autoplay']['autoplayToSlideIndex'] = $interval;
            default:
                $properties['autoplay']['autoplayToSlide'] = $this->interval * count($this->slider->slides) - 1;
                if ($this->intervalSlide == 'next') {
                    $properties['autoplay']['autoplayToSlide']++;
                }
                break;
        }
    }
}