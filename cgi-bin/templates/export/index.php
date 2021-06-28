<h3>Export</h3>

<h4>Órarend Krétához</h4>
<p>
Excel formátum, a következő oszlop sorrenddel:<br/>
<?php echo implode(", ", $kretaFejlec); ?><br/>
</p>
<div>
<form method='post' action='/export/toKreta'>
    <p>Exportálandó időpont választás!</p>
    Időpontok: <select name='date'>
        <?php foreach($napok as $nap): ?>
            <option value="<?=$nap['datum'];?>" > <?=$nap['datum'];?> - <?=$nap['nap_neve'];?> </option>
        <?php endforeach; ?>
    </select><br/>
    <input type="radio" name="period" value="1" checked> Egész heti<br>
    <input type="radio" name="period" value="2"> Csak a kiválasztott nap<br>    
	<input type="radio" name="period" value="3"> Teljes félév<br>
    <input type="checkbox" id="valid-column" name="valid-column" value="1"> <label for="valid-column">Érvényességi idővel (sima dátum helyett)</label><br>
    <input type="submit" value="Export">
</form>
</div>

<br/>

<h4>Órarend nyomtatáshoz</h4>
<!--Teljes órarend <a href="/export/toPrint/1">pdf</a><br/>-->
<p><a href="/export/toPrint/2">Dátum szerint csoportosítva, részletes, listába rendezett (excel)</a></p>
<p><a href="/export/toPrint/5">Táblázatos formában, tanárok rövidítésével, gyakorlat nélkül (excel)</a></p>
<p><a href="/export/webview">Táblázatos formában, tanárok rövidítésével, gyakorlat nélkül, webes</a> (meg csak reszben jo)</p>
<p><a href="/export/toPrint/3">Tanáronként csoportosítva, listába rendezve (excel)</a></p>
<p><a href="/export/toPrint/4">Osztályonként csoportosítva, listába rendezve (excel)</a></p>
<p><a href="/export/toPrint/6">Felsorolva minden óra, minden adatával együtt (excel)</a></p>

<h4>Tanórák</h4>
<p><a href="/export/toPrint/11">Tanárok szerint csoportosítva (excel)</a></p>
<p><a href="/export/toPrint/12">Osztályok szerint csoportosítva (excel)</a></p>

<h4>Tanárok</h4>
<p><a href="/export/toPrint/21">Tanárok listája, tanított tanórák számával (Excel)</a></p>
