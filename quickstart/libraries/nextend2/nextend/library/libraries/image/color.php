<?php

/**
 *
 * Color values manipulation utilities. Provides methods to convert from and to
 * Hex, RGB, HSV and HSL color representattions.
 *
 * Several color conversion logic are based on pseudo-code from
 * http://www.easyrgb.com/math.php
 *
 * @category Lux
 *
 * @package  Lux_Color
 *
 * @author   Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @version  $Id$
 *
 */
class N2Color {

    static function colorToRGBA($value) {
        $rgba = self::hex2rgba($value);

        return 'RGBA(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ')';
    }

    static function hex2alpha($value) {
        return intval(hexdec(substr($value, 6, 2)) / 2);
    }

    static function colorToCss($value) {
        return array(
            substr($value, 0, 2) == '00' ? false : substr($value, 0, 6),
            self::colorToRGBA($value)
        );
    }

    static function colorToSVG($value) {
        $rgba = self::hex2rgba($value);

        return array(
            substr($value, 0, 6),
            round($rgba[3] / 127, 2)
        );
    }

    /**
     *
     * Converts hexadecimal colors to RGB.
     *
     * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
     *                    with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
     *
     * @return array RGB values: 0 => R, 1 => G, 2 => B
     *
     */
    static function hex2rgb($hex) {


        // Remove #.
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }
        if (strlen($hex) == 3) {
            $hex .= $hex;
        }
        if (strlen($hex) != 6) {
            return false;
        }

        // Convert each tuple to decimal.
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return array(
            $r,
            $g,
            $b
        );
    }

    static function hex2rgba($hex) {


        // Remove #.
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }
        if (strlen($hex) == 6) {
            $hex .= 'ff';
        }
        if (strlen($hex) != 8) {
            return false;
        }

        // Convert each tuple to decimal.
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $a = intval(hexdec(substr($hex, 6, 2)) / 2);

        return array(
            $r,
            $g,
            $b,
            $a
        );
    }

    static function hex82hex($hex) {


        // Remove #.
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }
        if (strlen($hex) == 6) {
            $hex .= 'ff';
        }
        if (strlen($hex) != 8) {
            return false;
        }

        return array(
            substr($hex, 0, 6),
            substr($hex, 6, 2)
        );
    }

    /**
     *
     * Converts hexadecimal colors to HSV.
     *
     * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
     *                    with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
     *
     * @return array HSV values: 0 => H, 1 => S, 2 => V
     *
     */
    function hex2hsv($hex) {

        return $this->rgb2hsv($this->hex2rgb($hex));
    }

    /**
     *
     * Converts hexadecimal colors to HSL.
     *
     * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
     *                    with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
     *
     * @return array HSL values: 0 => H, 1 => S, 2 => L
     *
     */
    static function hex2hsl($hex) {

        return self::rgb2hsl(self::hex2rgb($hex));
    }

    /**
     *
     * Converts RGB colors to hexadecimal.
     *
     * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
     *
     * @return string Hexadecimal value with six digits, e.g., CCCCCC.
     *
     */
    function rgb2hex($rgb) {

        if (count($rgb) < 3) {
            return false;
        }
        list($r, $g, $b) = $rgb;

        // From php.net.
        $r = 0x10000 * max(0, min(255, $r));
        $g = 0x100 * max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return strtoupper(str_pad(dechex($r + $g + $b), 6, 0, STR_PAD_LEFT));
    }

    /**
     *
     * Converts RGB to HSV.
     *
     * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
     *
     * @return array HSV values: 0 => H, 1 => S, 2 => V
     *
     */
    function rgb2hsv($rgb) {


        // RGB values = 0 ?? 255
        $var_R = ($rgb[0] / 255);
        $var_G = ($rgb[1] / 255);
        $var_B = ($rgb[2] / 255);

        // Min. value of RGB
        $var_Min = min($var_R, $var_G, $var_B);

        // Max. value of RGB
        $var_Max = max($var_R, $var_G, $var_B);

        // Delta RGB value
        $del_Max = $var_Max - $var_Min;
        $V       = $var_Max;

        // This is a gray, no chroma...
        if ($del_Max == 0) {

            // HSV results = 0 ?? 1
            $H = 0;
            $S = 0;
        } else {

            // Chromatic data...
            $S     = $del_Max / $var_Max;
            $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
            $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
            $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
            if ($var_R == $var_Max) {
                $H = $del_B - $del_G;
            } else if ($var_G == $var_Max) {
                $H = (1 / 3) + $del_R - $del_B;
            } else if ($var_B == $var_Max) {
                $H = (2 / 3) + $del_G - $del_R;
            }
            if ($H < 0) {
                $H += 1;
            }
            if ($H > 1) {
                $H -= 1;
            }
        }

        // Returns agnostic values.
        // Range will depend on the application: e.g. $H*360, $S*100, $V*100.

        return array(
            $H,
            $S,
            $V
        );
    }

    /**
     *
     * Converts RGB to HSL.
     *
     * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
     *
     * @return array HSL values: 0 => H, 1 => S, 2 => L
     *
     */
    static function rgb2hsl($rgb) {


        // Where RGB values = 0 ?? 255.
        $var_R = $rgb[0] / 255;
        $var_G = $rgb[1] / 255;
        $var_B = $rgb[2] / 255;

        // Min. value of RGB
        $var_Min = min($var_R, $var_G, $var_B);

        // Max. value of RGB
        $var_Max = max($var_R, $var_G, $var_B);

        // Delta RGB value
        $del_Max = $var_Max - $var_Min;
        $L       = ($var_Max + $var_Min) / 2;
        if ($del_Max == 0) {

            // This is a gray, no chroma...
            // HSL results = 0 ?? 1

            $H = 0;
            $S = 0;
        } else {

            // Chromatic data...
            if ($L < 0.5) {
                $S = $del_Max / ($var_Max + $var_Min);
            } else {
                $S = $del_Max / (2 - $var_Max - $var_Min);
            }
            $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
            $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
            $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
            if ($var_R == $var_Max) {
                $H = $del_B - $del_G;
            } else if ($var_G == $var_Max) {
                $H = (1 / 3) + $del_R - $del_B;
            } else if ($var_B == $var_Max) {
                $H = (2 / 3) + $del_G - $del_R;
            }
            if ($H < 0) {
                $H += 1;
            }
            if ($H > 1) {
                $H -= 1;
            }
        }

        return array(
            $H,
            $S,
            $L
        );
    }

    /**
     *
     * Converts HSV colors to hexadecimal.
     *
     * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
     *
     * @return string Hexadecimal value with six digits, e.g., CCCCCC.
     *
     */
    function hsv2hex($hsv) {

        return $this->rgb2hex($this->hsv2rgb($hsv));
    }

    /**
     *
     * Converts HSV to RGB.
     *
     * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
     *
     * @return array RGB values: 0 => R, 1 => G, 2 => B
     *
     */
    function hsv2rgb($hsv) {

        $H = $hsv[0];
        $S = $hsv[1];
        $V = $hsv[2];

        // HSV values = 0 ?? 1
        if ($S == 0) {
            $R = $V * 255;
            $G = $V * 255;
            $B = $V * 255;
        } else {
            $var_h = $H * 6;

            // H must be < 1
            if ($var_h == 6) {
                $var_h = 0;
            }

            // Or ... $var_i = floor( $var_h )
            $var_i = floor($var_h);
            $var_1 = $V * (1 - $S);
            $var_2 = $V * (1 - $S * ($var_h - $var_i));
            $var_3 = $V * (1 - $S * (1 - ($var_h - $var_i)));
            switch ($var_i) {
                case 0:
                    $var_r = $V;
                    $var_g = $var_3;
                    $var_b = $var_1;
                    break;
                case 1:
                    $var_r = $var_2;
                    $var_g = $V;
                    $var_b = $var_1;
                    break;
                case 2:
                    $var_r = $var_1;
                    $var_g = $V;
                    $var_b = $var_3;
                    break;
                case 3:
                    $var_r = $var_1;
                    $var_g = $var_2;
                    $var_b = $V;
                    break;
                case 4:
                    $var_r = $var_3;
                    $var_g = $var_1;
                    $var_b = $V;
                    break;
                default:
                    $var_r = $V;
                    $var_g = $var_1;
                    $var_b = $var_2;
            }

            //RGB results = 0 ?? 255
            $R = $var_r * 255;
            $G = $var_g * 255;
            $B = $var_b * 255;
        }

        return array(
            $R,
            $G,
            $B
        );
    }

    /**
     *
     * Converts HSV colors to HSL.
     *
     * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
     *
     * @return array HSL values: 0 => H, 1 => S, 2 => L
     *
     */
    function hsv2hsl($hsv) {

        return $this->rgb2hsl($this->hsv2rgb($hsv));
    }

    /**
     *
     * Converts hexadecimal colors to HSL.
     *
     * @param array $hsl HSL values: 0 => H, 1 => S, 2 => L
     *
     * @return string Hexadecimal value. Accepts values with 3 or 6 numbers,
     * with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
     *
     */
    function hsl2hex($hsl) {

        return $this->rgb2hex($this->hsl2rgb($hsl));
    }

    /**
     *
     * Converts HSL to RGB.
     *
     * @param array $hsv HSL values: 0 => H, 1 => S, 2 => L
     *
     * @return array RGB values: 0 => R, 1 => G, 2 => B
     *
     */
    static function hsl2rgb($hsl) {

        list($H, $S, $L) = $hsl;
        if ($S == 0) {

            // HSL values = 0 ?? 1
            // RGB results = 0 ?? 255

            $R = $L * 255;
            $G = $L * 255;
            $B = $L * 255;
        } else {
            if ($L < 0.5) {
                $var_2 = $L * (1 + $S);
            } else {
                $var_2 = ($L + $S) - ($S * $L);
            }
            $var_1 = 2 * $L - $var_2;
            $R     = 255 * self::_hue2rgb($var_1, $var_2, $H + (1 / 3));
            $G     = 255 * self::_hue2rgb($var_1, $var_2, $H);
            $B     = 255 * self::_hue2rgb($var_1, $var_2, $H - (1 / 3));
        }

        return array(
            $R,
            $G,
            $B
        );
    }

    /**
     *
     * Support method for hsl2rgb(): converts hue ro RGB.
     *
     * @param
     *
     * @param
     *
     * @param
     *
     * @return int
     *
     */
    static function _hue2rgb($v1, $v2, $vH) {

        if ($vH < 0) {
            $vH += 1;
        }
        if ($vH > 1) {
            $vH -= 1;
        }
        if ((6 * $vH) < 1) {
            return ($v1 + ($v2 - $v1) * 6 * $vH);
        }
        if ((2 * $vH) < 1) {
            return $v2;
        }
        if ((3 * $vH) < 2) {
            return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
        }

        return $v1;
    }

    /**
     *
     * Converts hexadecimal colors to HSL.
     *
     * @param array $hsl HSL values: 0 => H, 1 => S, 2 => L
     *
     * @return array HSV values: 0 => H, 1 => S, 2 => V
     *
     */
    function hsl2hsv($hsl) {

        return $this->rgb2hsv($this->hsl2rgb($hsl));
    }

    /**
     *
     * Updates HSV values.
     *
     * @param array $hsv    HSV values: 0 => H, 1 => S, 2 => V
     *
     * @param array $values Values to update: 0 => value to add to H (0 to 360),
     *                      1 and 2 => values to multiply S and V (0 to 100). Example:
     *
     * {{{code:php
     *     // Update saturation to 80% in the provided HSV.
     *     $hsv = array(120, 0.75, 0.75);
     *     $new_hsv = $color->updateHsv($hsv, array(null, 80, null));
     * }}}
     *
     */
    function updateHsv($hsv, $values) {

        if (isset($values[0])) {
            $hsv[0] = max(0, min(360, ($hsv[0] + $values[0])));
        }
        if (isset($values[1])) {
            $hsv[1] = max(0, min(1, ($hsv[1] * ($values[1] / 100))));
        }
        if (isset($values[2])) {
            $hsv[2] = max(0, min(1, ($hsv[2] * ($values[2] / 100))));
        }

        return $hsv;
    }

    /**
     *
     * Updates HSL values.
     *
     * @param array $hsl    HSL values: 0 => H, 1 => S, 2 => L
     *
     * @param array $values Values to update: 0 => value to add to H (0 to 360),
     *                      1 and 2 => values to multiply S and V (0 to 100). Example:
     *
     * {{{code:php
     *     // Update saturation to 80% in the provided HSL.
     *     $hsl = array(120, 0.75, 0.75);
     *     $new_hsl = $color->updateHsl($hsl, array(null, 80, null));
     * }}}
     *
     */
    function updateHsl($hsl, $values) {

        if (isset($values[0])) {
            $hsl[0] = max(0, min(1, ($hsl[0] + $values[0] / 360)));
        }
        if (isset($values[1])) {
            $hsl[1] = max(0, min(1, ($hsl[1] * ($values[1] / 100))));
        }
        if (isset($values[2])) {
            $hsl[2] = max(0, min(1, ($hsl[2] * ($values[2] / 100))));
        }

        return $hsl;
    }

    static function rgb2array($rgb) {

        return array(
            base_convert(substr($rgb, 0, 2), 16, 10),
            base_convert(substr($rgb, 2, 2), 16, 10),
            base_convert(substr($rgb, 4, 2), 16, 10),
        );
    }

}