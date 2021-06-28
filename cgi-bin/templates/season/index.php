<?php//  main/seasons ?>

<div class="container">

<h4>Új időszak</h4>
<p>
<a href="/seasons/create">Új időszak</a>
</p>

<br/>

<h3>Elérhető időszakok</h3>
<table class="table table-hover table-sm">
<thead>
<tr>
    <th>#</th>
    <th>Név</th>
    <th>Időszak</th>
    <th></th>
    <th></th>
</tr>
</thead>
<?php foreach($seasons as $season): ?>
    <tr>
        <td><?= $season['season_id'];?></td>
        <td><?php echo ($season['season_id'] == $active_season ? "&#9989; ": ""); ?><?= $season['nev'];?></td>
        <td><?= $season['elso_tanitasi_nap'] . ' - ' . $season['utolso_tanitasi_nap'];?></td>
        <td><a href="javascript:void(0);" onClick="confirmDelete(<?=$season['season_id']?>)">Töröl</a></td>
        <td><a href="/seasons/activate/<?=$season['season_id'];?>">Aktivál</a></td>
    </t>
<?php endforeach; ?>
</table>

<br/>

<h4>Időszak klónozás</h4>
<p>A teljes időszakról készít másolatot.</p>
<p>
<form method="post" id="cloneForm" action="/seasons/clone">
    <label for="season_nev">Új időszak neve:</label> <input type="text" size="30" name="season_nev" /><br/>
    <label for="season_master_id">Időszakok:</label> <select name="season_master_id">
        <?php foreach($seasons as $season): ?>
            <option value="<?=$season['season_id'];?>"><?=$season['nev'] . ' (' . $season['elso_tanitasi_nap'] . ' - ' . $season['utolso_tanitasi_nap'] . ') #' . $season['season_id'];?></value>
        <?php endforeach; ?>
    </select><br/>
    <button type="submit" id="btnClone">Klónozás</button>
</form>
</p>
</div>

<pre>

<?php //print_r($seasons); ?>
</pre>

<script type="text/javascript">
let delurl="<?= $delurl;?>";
function confirmDelete(id) {
    if (confirm("Biztos törölni szeretnéd?\nTöröl mindent, ami az időszakhoz tartozik!!"))
        window.location = delurl + id;
    else
        return false;    
}

$(document).ready(function() {
    $("#btnClone").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos?')) { $("#cloneForm").submit(); }
    });
});
</script>
</script>