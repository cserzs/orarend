<h3>Tanórák</h3>
<div>
    <a href="/lesson/edit">Új tanóra</a><br/>
</div>
<br/>
<div>
    <form method="post" action="/lesson/showClass">
        Osztály órái: 
        <select id="classSelect" name="classid">
            <?php foreach($osztalyok as $o): ?>
                <option value="<?=$o['id'];?>" ><?=$o['name'];?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Mutat" />
        <input id="btnAddToClass" type="button" value="Új tanórák az osztálynak" />
    </form>
</div>
<div>
    <form method="post" action="/lesson/showTeacher">
        Tanár órái: 
        <select name="teacherid">
            <?php foreach($tanarok as $o): ?>
                <option value="<?=$o['id'];?>" ><?=$o['name'];?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Mutat" />
    </form>
</div>

<h3>Lista</h3>

<p>Tanórák száma: <?=$osszesTanora; ?></p>

<table class="table table-sm biggerFontSize14 table-hover">
    <tr>
        <th style="width: 50px;">#</th>
        <th style="width: 150px;">Osztály</th>
        <th>Tanár</th>
        <th>Tantárgy</th>
        <th style="width: 70px;">Óraszám</th>
        <th style="width: 90px;">Órarendi óra</th>
        <th style="width: 120px;">Elhelyezetlen órák</th>
        <th style="width: 90px;">Heti óraszám</th>
        <th style="width: 50px;">Gyak.</th>
        <th style="width: 80px;"></th>
        <th></th>
    </tr>
    <?php foreach($tanorak as $t): ?>
    <tr>
        <td><?php echo $t['id']; ?></td>
        <td><?php echo $t['osztaly']; ?></td>
        <td><?php echo $t['tanar']; ?></td>
        <td><?php echo $t['tantargy']; ?></td>
        <td><?php echo $t['num']; ?></td>
        <td><?php echo $t['num_in_tt']; ?></td>
        <td><?php echo $t['free_num']; ?></td>
        <td><?php echo number_format($t['num'] / $tanitasi_hetek, 2); ?></td>
        <td><?php echo ($t['gyakorlat'] == 1 ? "X": "");?></td>
        <td><a href="/lesson/edit/<?=$t['id'];?>">Szerkeszt</a></td>
        <td>
            <?php if ($t['num_in_tt'] > 0): ?>
                Nem lehet törölni, amíg van órarendi óra
            <?php else: ?>
                <a class="_deleteBtn" href="#" data-id="<?=$t['id'];?>">Töröl</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($paginationVisible): ?>
<div>
<?php for($i = 0; $i < $lastPage; $i++): ?>
    <span><a href="/lesson/index?page=<?=($i+1);?>"><?=($i+1);?></a></span>
<?php endfor; ?>
</div>
<?php endif; ?>

<?php //echo $paginationLinks; ?>

<pre>
<?php //print_r($tanorak); ?>
</pre>

<script src="/js/jquery-3.3.1.min.js"></script>
<script>
var baseUrl = '<?php echo $baseUrl; ?>';
$(document).ready(function() {
    $("._deleteBtn").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos torlod?')) {
            window.location.replace(baseUrl + 'lesson/delete/' + $(this).data('id'));            
        }
    });
    $("#btnAddToClass").click(function() {
        window.location.replace(baseUrl + 'lesson/addToClass/' + $("#classSelect").val());
    });
});
</script>
