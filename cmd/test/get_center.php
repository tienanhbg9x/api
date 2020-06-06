
<?php

$coords[]=array('lat' => '53.344104','lng'=>'-6.2674937');
$coords[]=array('lat' => '51.5081289','lng'=>'-0.128005');    
    
 
print_r(get_center($coords));

function get_center($coords)
{
    $count_coords = count($coords);
    $xcos=0.0;
    $ycos=0.0;
    $zsin=0.0;
    
        foreach ($coords as $lnglat)
        {
            $lat = $lnglat['lat'] * pi() / 180;
            $lon = $lnglat['lng'] * pi() / 180;
            
            $acos = cos($lat) * cos($lon);
            $bcos = cos($lat) * sin($lon);
            $csin = sin($lat);
            $xcos += $acos;
            $ycos += $bcos;
            $zsin += $csin;
        }
    
    $xcos /= $count_coords;
    $ycos /= $count_coords;
    $zsin /= $count_coords;
    $lon = atan2($ycos, $xcos);
    $sqrt = sqrt($xcos * $xcos + $ycos * $ycos);
    $lat = atan2($zsin, $sqrt);
    
    return array($lat * 180 / pi(), $lon * 180 / pi());
}

?>