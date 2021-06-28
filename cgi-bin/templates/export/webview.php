<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Orarend</title>
</head>
<body>
<h2>Orarend</h2>
<?php

foreach($datumok as $het)
{
    echo '<table width="100%">';
    echo '<tr>';
    echo '<td></td>';
    foreach($het as $nap)
    {
        echo '<td colspan="' . $napi_oraszam . '">' . $nap['datum'] . ' - ' . $nap['napnev'] . '</td>';
    }
    echo '</tr>';
    
    echo '<tr>';
    echo '<td></td>';
    for($k = 0; $k < 5; $k++)
    {
        for($i = 0; $i < $napi_oraszam; $i++)
        {
            echo '<td>' . ($kezdo_oraszam + $i) . '</td>';
        }
    }
    echo '</tr>';
    
    
    foreach($osztalyok as $osztaly)
    {
        echo '<tr>';
        echo '<td>' . $osztaly['short_name'] . '</td>';
        
        foreach($het as $nap)
        {
            for($i = 0; $i < $napi_oraszam; $i++)
            {
                $ora = $kezdo_oraszam + $i;
                if (isset($orarend[$nap['datum']][$osztaly['short_name']][$ora])) echo '<td>' . $orarend[$nap['datum']][$osztaly['short_name']][$ora]['tanar_rovid'] . '</td>';
                else echo '<td>-</td>';
            }
        }
        echo '</tr>';
    }

    
    echo '</table>';
    echo '<br/>';
}

echo 'Keszult: ?';
?>
    
<pre>
<?php //print_r($datumok); ?>
</pre>
</body>
</html>