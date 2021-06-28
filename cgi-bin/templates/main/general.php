<?php//  main/index ?>
<h3>Aktuális időszak adatai</h3>

<div>
<p><b>Név:</b> <?php echo $season_name; ?><br/>
<b>ID:</b> <?=$ttm->season_id;?></p>
<ul>
    <li>Első tanítási nap: <?=$ttm->elso_tanitasi_nap;?></li>
    <li>Utolsó tanítási nap: <?=$ttm->utolso_tanitasi_nap;?></li>
    <li>Napi óraszám: <?=$ttm->napi_oraszam;?></li>
    <li>Kezdő óraszám: <?=$ttm->kezdo_oraszam;?>. óra</li>
    <li>Tanítási hetek száma: <?=$ttm->tanitasi_hetek;?></li>
    <li>Naptári hetek: <?=$ttm->hetek_szama;?></li>
</ul>
</div>

<h3>Melyik napokon nincs tanítás?</h3>
<form method="post" action="/main/general">
<table border="1" width="1200">
    <tr>
        <td>Hétfő</td>
        <td>Kedd</td>
        <td>Szerda</td>
        <td>Csütörtök</td>
        <td>Péntek</td>
    </tr>
    <?php
    $index = 0;
    foreach($napok as $nap): ?>
        <?php if ($index % 5 == 0): ?> <tr> <?php endif; ?>
            <td><input type="checkbox" name="exceptionDate[]" value="<?=$nap['datum'];?>" <?php echo $nap['van_tanitas'] ? '' : 'checked'; ?>/> <?php echo $nap['datum']; ?></td>
        <?php if (($index + 1) % 5 == 0): ?> </tr> <?php endif; ?>
        <?php $index += 1;?>
    <?php endforeach; ?>
</table>
<input type="submit" value="Mentés" />
</form>

<br/>
<hr/>

<h3>Órarend törlése</h3>
<p>
<form id="clearForm" method="post" action="/main/cleartt">
    <p>Törli a teljes órarendet!</p>
    <p><input type="submit" id="btnDelete" name="deltt" value="Töröl"></p>
</form>

</p>

<h3>Adatok importálása</h3>
<p>Másik időszak adatainak átvétele!<br/>
Az importáláskor másolatot készít az eredetikről (új id-t kapnak)!<br/>
Tanórákat és órarendet a klónozással lehet lemásolni.
</p>
<p>
<form id="importForm" method="post" action="/main/import2season">
    <p>
    Időszakok: 
    <select name="season_origin_id">
        <?php foreach($seasons as $season): ?>
            <option value="<?=$season['season_id'];?>"><?=$season['nev'] . ' (' . $season['elso_tanitasi_nap'] . ' - ' . $season['utolso_tanitasi_nap'] . ') #' . $season['season_id'];?></value>
        <?php endforeach; ?>
    </select><br/>
    Mit vegyen át?<br/>
    <input type="checkbox" id="subject" name="subject" value="1"> <label for="subject">Tantárgyak</label><br>
    <input type="checkbox" id="schoolclass" name="schoolclass" value="1"> <label for="schoolclass">Osztályok</label><br>
    <input type="checkbox" id="teacher" name="teacher" value="1"> <label for="teacher">Tanárok</label><br>
    <input type="submit" id="btnImport" value="Import"></p>
</form>

</p>

<br/>

<h3>Időszak átnevezése</h3>
<p>
<form id="renameForm" method="post" action="/main/renameseason">
    Név: <?=$season_name; ?><br/>
    <label for="nev">Új név:</label> <input type="text" name="nev" size="25" /><br/>
    <input type="submit" id="btnSave" value="Mentés">
</form>
</p>

<pre>
    <?php //print_r($napok); ?>
</pre>

<script type="text/javascript">
$(document).ready(function() {
    $("#btnDelete").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos törlöd a teljes órarendet?')) { $("#clearForm").submit(); }
    });
    $("#btnImport").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos?')) { $("#importForm").submit(); }
    });
});
</script>