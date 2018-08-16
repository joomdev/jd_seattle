<?php

class N2SmartSliderSlidesAdmin extends N2SmartSliderSlides {

    protected function slidesWhereQuery() {
        $date = N2Platform::getDate();

        return "   AND ((published = 1 AND (publish_up = '0000-00-00 00:00:00' OR publish_up < '{$date}')
                   AND (publish_down = '0000-00-00 00:00:00' OR publish_down > '{$date}'))
                   OR id = " . N2Request::getInt('slideid') . ") ";
    }

    public function hasSlides() {
        return true;
    }

    protected function createSlide($slideRow) {
        return new N2SmartSliderSlideAdmin($this->slider, $slideRow);
    }

    public function makeSlides($extend = array()) {

        if (N2Request::getCmd('nextendcontroller') == 'slides') {

            $slides = &$this->slides;

            if (N2Request::getCmd('nextendaction') == 'create') {
                if ($this->maximumSlideCount > 0) {
                    array_splice($slides, $this->maximumSlideCount - 1);
                }

                $staticSlide          = N2Request::getInt('static', 0);
                $currentlyEditedSlide = $this->createSlide(array(
                    'id'           => 0,
                    'title'        => 'Title',
                    'slider'       => N2Request::getInt('sliderid'),
                    'publish_up'   => '0000-00-00 00:00:00',
                    'publish_down' => '0000-00-00 00:00:00',
                    'published'    => 1,
                    'first'        => 0,
                    'slide'        => '',
                    'description'  => '',
                    'thumbnail'    => '',
                    'background'   => 'ffffff00|*|',
                    'params'       => json_encode(array('static-slide' => $staticSlide)),
                    'ordering'     => count($slides),
                    'generator_id' => 0
                ));

                if ($currentlyEditedSlide->isStatic()) {
                    $this->slider->addStaticSlide($currentlyEditedSlide);

                    $this->slider->setStatic(1);

                    if (count($slides) == 0) {
                        $slide2 = $this->createSlide(array(
                            'id'           => 0,
                            'title'        => 'Title',
                            'slider'       => N2Request::getInt('sliderid'),
                            'publish_up'   => '0000-00-00 00:00:00',
                            'publish_down' => '0000-00-00 00:00:00',
                            'published'    => 1,
                            'first'        => 0,
                            'slide'        => '',
                            'description'  => '',
                            'thumbnail'    => '',
                            'background'   => 'ffffff00|*|',
                            'params'       => '',
                            'ordering'     => count($slides),
                            'generator_id' => 0
                        ));
                        array_push($slides, $slide2);
                    }
                } else {

                    for ($i = 0; $i < count($slides); $i++) {
                        if ($slides[$i]->isStatic()) {
                            $this->slider->addStaticSlide($slides[$i]);
                            array_splice($slides, $i, 1);
                            $i--;
                        }
                    }

                    array_push($slides, $currentlyEditedSlide);
                    $this->slider->firstSlideIndex = count($slides) - 1;
                }
            } else {

                $currentlyEdited      = N2Request::getInt('slideid');
                $currentlyEditedSlide = null;
                $isStatic             = false;

                $staticSlidesCount = 0;
                for ($i = 0; $i < count($slides); $i++) {
                    if ($slides[$i]->isStatic()) {
                        $staticSlidesCount++;
                    }
                }

                $countSlides = count($slides);

                for ($i = 0; $i < count($slides) && $countSlides > $staticSlidesCount; $i++) {
                    if ($slides[$i]->isStatic()) {
                        if ($slides[$i]->id == $currentlyEdited) {
                            $isStatic = true;
                            $this->slider->setStatic(1);
                        }
                        $this->slider->addStaticSlide($slides[$i]);
                        array_splice($slides, $i, 1);
                        $i--;
                    }
                }

                // If we edit a static slide -> remove other static slides from the canvas.
                if ($isStatic) {
                    for ($i = 0; $i < count($this->slider->staticSlides); $i++) {
                        if ($this->slider->staticSlides[$i]->id != $currentlyEdited) {
                            array_splice($this->slider->staticSlides, $i, 1);
                            $i--;
                        }
                    }
                }

                for ($i = 0; $i < count($slides); $i++) {
                    $slides[$i]->initGenerator($extend);
                }

                for ($i = count($slides) - 1; $i >= 0; $i--) {
                    if ($slides[$i]->hasGenerator()) {
                        array_splice($slides, $i, 1, $slides[$i]->expandSlideAdmin());
                    }
                }

                if ($isStatic) {
                    if (count($slides) == 0) {
                        $slide2 = $this->createSlide(array(
                            'id'           => 0,
                            'title'        => 'Title',
                            'slider'       => N2Request::getInt('sliderid'),
                            'publish_up'   => '0000-00-00 00:00:00',
                            'publish_down' => '0000-00-00 00:00:00',
                            'published'    => 1,
                            'first'        => 0,
                            'slide'        => '',
                            'description'  => '',
                            'thumbnail'    => '',
                            'background'   => 'ffffff00|*|',
                            'params'       => '',
                            'ordering'     => count($slides),
                            'generator_id' => 0
                        ));
                        array_push($slides, $slide2);

                        $currentlyEditedSlide = $slides[0];
                    } else {
                        $currentlyEditedSlide = $this->slider->staticSlides[0];
                    }
                    $this->slider->firstSlideIndex = 0;
                } else {
                    for ($i = 0; $i < count($slides); $i++) {
                        if ($slides[$i]->id == $currentlyEdited) {
                            $this->slider->firstSlideIndex = $i;
                            $currentlyEditedSlide          = $slides[$i];
                            break;
                        }
                    }

                    if ($this->maximumSlideCount > 0) {
                        array_splice($slides, $this->maximumSlideCount);
                        $found = false;
                        for ($i = 0; $i < count($slides); $i++) {
                            if ($slides[$i] == $currentlyEditedSlide) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $this->slider->firstSlideIndex          = count($slides) - 1;
                            $slides[$this->slider->firstSlideIndex] = $currentlyEditedSlide;
                        }
                    }
                }
            }

            $currentlyEditedSlide->setCurrentlyEdited();
        }
    }
}
