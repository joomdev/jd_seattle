<?php

function piechartToImage($filename, $width, $height, $values, $colors){
    if(empty($values)) return false;
    $img = imageCreateTrueColor( $width, $height );
    imagealphablending($img,true);
    $color = imageColorAllocate( $img, 255, 255, 255);
    imagefill( $img, 0, 0, $color );

    acymailing_arrayToInteger($values);
    $total = array_sum($values);
    $end = M_PI/2+2*M_PI;

	foreach($values as $i => $oneVal){
        if(empty($oneVal)) continue;

        $color = empty($colors[$i]) ? array(66, 66, 66, 1) : $colors[$i];

        imageSmoothArc($img, $width/2, $height/2, $width-20, $height-20, $color, M_PI/2+0.00000001, $end);
        $end -= (2*M_PI*$oneVal)/$total;
    }

	ob_start();
	imagePNG( $img );
	$image = ob_get_clean();
    
    acymailing_writeFile(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename, $image);

	return true;
}

function imageSmoothArcDrawSegment (&$img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $start, $stop, $seg)
{
    $fillColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], $color[3] );
    
    $xStart = abs($a * cos($start));
    $yStart = abs($b * sin($start));
    $xStop  = abs($a * cos($stop));
    $yStop  = abs($b * sin($stop));
    $dxStart = 0;
    $dyStart = 0;
    $dxStop = 0;
    $dyStop = 0;
    if ($xStart != 0)
        $dyStart = $yStart/$xStart;
    if ($xStop != 0)
        $dyStop = $yStop/$xStop;
    if ($yStart != 0)
        $dxStart = $xStart/$yStart;
    if ($yStop != 0)
        $dxStop = $xStop/$yStop;
    if (abs($xStart) >= abs($yStart)) {
        $aaStartX = true;
    } else {
        $aaStartX = false;
    }
    if ($xStop >= $yStop) {
        $aaStopX = true;
    } else {
        $aaStopX = false;
    }
	
    for ( $x = 0; $x < $a; $x += 1 ) {
        $_y1 = $dyStop*$x;
        $_y2 = $dyStart*$x;
        if ($xStart > $xStop)
        {
            $error1 = $_y1 - (int)($_y1);
            $error2 = 1 - $_y2 + (int)$_y2;
            $_y1 = $_y1-$error1;
            $_y2 = $_y2+$error2;
        }
        else
        {
            $error1 = 1 - $_y1 + (int)$_y1;
            $error2 = $_y2 - (int)($_y2);
            $_y1 = $_y1+$error1;
            $_y2 = $_y2-$error2;
        }
        
        if ($seg == 0 || $seg == 2)
        {
            $i = $seg;
            if (!($start > $i*M_PI/2 && $x > $xStart)) {
                if ($i == 0) {
                    $xp = +1; $yp = -1; $xa = +1; $ya = 0;
                } else {
                    $xp = -1; $yp = +1; $xa = 0; $ya = +1;
                }
                if ( $stop < ($i+1)*(M_PI/2) && $x <= $xStop ) {
                    $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                    $y1 = $_y1; if ($aaStopX) imageSetPixel($img, $cx+$xp*($x)+$xa, $cy+$yp*($y1+1)+$ya, $diffColor1);
                    
                } else {
                    $y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
                    $error = $y - (int)($y);
                    $y = (int)($y);
                    $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                    $y1 = $y; if ($x < $aaAngleX ) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor);
                }
                if ($start > $i*M_PI/2 && $x <= $xStart) {
                    $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                    $y2 = $_y2; if ($aaStartX) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y2-1)+$ya, $diffColor2);
                } else {
                    $y2 = 0;
                }
                if ($y2 <= $y1) imageLine($img, $cx+$xp*$x+$xa, $cy+$yp*$y1+$ya , $cx+$xp*$x+$xa, $cy+$yp*$y2+$ya, $fillColor);
            }
        }
        
        if ($seg == 1 || $seg == 3)
        {
            $i = $seg;
            if (!($stop < ($i+1)*M_PI/2 && $x > $xStop)) {
                if ($i == 1) {
                    $xp = -1; $yp = -1; $xa = 0; $ya = 0;
                } else {
                    $xp = +1; $yp = +1; $xa = 1; $ya = 1;
                }
                if ( $start > $i*M_PI/2 && $x < $xStart ) {
                    $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                    $y1 = $_y2; if ($aaStartX) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor2);
                    
                } else {
                    $y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
                    $error = $y - (int)($y);
                    $y = (int) $y;
                    $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                    $y1 = $y; if ($x < $aaAngleX ) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor);
                }
                if ($stop < ($i+1)*M_PI/2 && $x <= $xStop) {
                    $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                    $y2 = $_y1; if ($aaStopX)  imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y2-1)+$ya, $diffColor1);
                } else {
                    $y2 = 0;
                }
                if ($y2 <= $y1) imageLine($img, $cx+$xp*$x+$xa, $cy+$yp*$y1+$ya, $cx+$xp*$x+$xa, $cy+$yp*$y2+$ya, $fillColor);
            }
        }
    }
    
    for ( $y = 0; $y < $b; $y += 1 ) {
        $_x1 = $dxStop*$y;
        $_x2 = $dxStart*$y;
        if ($yStart > $yStop)
        {
            $error1 = $_x1 - (int)($_x1);
            $error2 = 1 - $_x2 + (int)$_x2;
            $_x1 = $_x1-$error1;
            $_x2 = $_x2+$error2;
        }
        else
        {
            $error1 = 1 - $_x1 + (int)$_x1;
            $error2 = $_x2 - (int)($_x2);
            $_x1 = $_x1+$error1;
            $_x2 = $_x2-$error2;
        }
        
        if ($seg == 0 || $seg == 2)
        {
            $i = $seg;
            if (!($start > $i*M_PI/2 && $y > $yStop)) {
                if ($i == 0) {
                    $xp = +1; $yp = -1; $xa = 1; $ya = 0;
                } else {
                    $xp = -1; $yp = +1; $xa = 0; $ya = 1;
                }
                if ( $stop < ($i+1)*(M_PI/2) && $y <= $yStop ) {
                    $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                    $x1 = $_x1; if (!$aaStopX) imageSetPixel($img, $cx+$xp*($x1-1)+$xa, $cy+$yp*($y)+$ya, $diffColor1);
                } 
                if ($start > $i*M_PI/2 && $y < $yStart) {
                    $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                    $x2 = $_x2; if (!$aaStartX) imageSetPixel($img, $cx+$xp*($x2+1)+$xa, $cy+$yp*($y)+$ya, $diffColor2);
                } else {
                    $x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
                    $error = $x - (int)($x);
                    $x = (int)($x);
                    $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                    $x1 = $x; if ($y < $aaAngleY && $y <= $yStop ) imageSetPixel($img, $cx+$xp*($x1+1)+$xa, $cy+$yp*$y+$ya, $diffColor);
                }
            }
        }
        
        if ($seg == 1 || $seg == 3)
        {
            $i = $seg;
            if (!($stop < ($i+1)*M_PI/2 && $y > $yStart)) {
                if ($i == 1) {
                    $xp = -1; $yp = -1; $xa = 0; $ya = 0;
                } else {
                    $xp = +1; $yp = +1; $xa = 1; $ya = 1;
                }
                if ( $start > $i*M_PI/2 && $y < $yStart ) {
                    $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                    $x1 = $_x2; if (!$aaStartX) imageSetPixel($img, $cx+$xp*($x1-1)+$xa, $cy+$yp*$y+$ya,  $diffColor2);
                } 
                if ($stop < ($i+1)*M_PI/2 && $y <= $yStop) {
                    $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                    $x2 = $_x1; if (!$aaStopX)  imageSetPixel($img, $cx+$xp*($x2+1)+$xa, $cy+$yp*$y+$ya, $diffColor1);
                } else {
                    $x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
                    $error = $x - (int)($x);
                    $x = (int)($x);
                    $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                    $x1 = $x; if ($y < $aaAngleY  && $y < $yStart) imageSetPixel($img,$cx+$xp*($x1+1)+$xa,  $cy+$yp*$y+$ya, $diffColor);
                }
            }
        }
    }
}

function imageSmoothArc ( &$img, $cx, $cy, $w, $h, $color, $start, $stop)
{
    while ($start < 0)
        $start += 2*M_PI;
    while ($stop < 0)
        $stop += 2*M_PI;
    
    while ($start > 2*M_PI)
        $start -= 2*M_PI;
    
    while ($stop > 2*M_PI)
        $stop -= 2*M_PI;
    
    
    if ($start > $stop)
    {
        imageSmoothArc ( $img, $cx, $cy, $w, $h, $color, $start, 2*M_PI);
        imageSmoothArc ( $img, $cx, $cy, $w, $h, $color, 0, $stop);
        return;
    }
    
    $a = 1.0*round ($w/2);
    $b = 1.0*round ($h/2);
    $cx = 1.0*round ($cx);
    $cy = 1.0*round ($cy);
    
    $aaAngle = atan(($b*$b)/($a*$a)*tan(0.25*M_PI));
    $aaAngleX = $a*cos($aaAngle);
    $aaAngleY = $b*sin($aaAngle);
    
    $a -= 0.5;
    $b -= 0.5;
    
    for ($i=0; $i<4;$i++)
    {
        if ($start < ($i+1)*M_PI/2)
        {
            if ($start > $i*M_PI/2)
            {
                if ($stop > ($i+1)*M_PI/2)
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY , $color, $start, ($i+1)*M_PI/2, $i);
                }
                else
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $start, $stop, $i);
                    break;
                }
            }
            else
            {
                if ($stop > ($i+1)*M_PI/2)
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $i*M_PI/2, ($i+1)*M_PI/2, $i);
                }
                else
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $i*M_PI/2, $stop, $i);
                    break;
                }
            }
        }
    }
}
?>
