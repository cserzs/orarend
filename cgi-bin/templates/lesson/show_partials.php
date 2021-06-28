<h2><?=$owner;?> órái</h2>

<div>
<p>Összes óraszám: <?=$osszoraszam; ?><br/>
Ebből<br/>
- elmélet: <?= ($osszoraszam - $gyakorlatok_szama); ?><br/>
- gyakorlat: <?= $gyakorlatok_szama; ?>
</p>
<p>Heti óraszám: <?= number_format($osszoraszam / $tanitasi_hetek, 2); ?></p>
<p><a href="/lesson/edit">Új tanóra</a></p>
    
</div>

<table class="table table-hover">
    <tr>
        <th style="width: 30px;"></th>
        <th style="width: 50px;">ID</th>
        <th style="width: 150px;">Osztály</th>
        <th>Tanár</th>
        <th>Tantárgy</th>
        <th style="width: 70px;">Óraszám</th>
        <th style="width: 90px;">Órarendi órák</th>
        <th style="width: 100px;">Elhelyezetlen órák</th>
        <th style="width: 90px;">Heti óraszám</th>
        <th style="width: 50px;">Gyak.</th>
        <th style="width: 80px;"></th>
        <th></th>
    </tr>
    <?php foreach($tanorak as $t): ?>
    <tr>
        <td><input type="checkbox" /></td>
        <td><?php echo $t['id']; ?></td>
        <td><?php echo $t['osztaly']; ?></td>
        <td><?php echo $t['tanar']; ?></td>
        <td><?php echo $t['tantargy']; ?></td>
        <td><?php echo $t['num']; ?></td>
        <td><?php echo $t['num_in_tt']; ?></td>
        <td><?php echo $t['free_num']; ?></td>
        <td><?php echo number_format($t['num'] / $tanitasi_hetek, 2); ?></td>
        <td><?php echo ($t['gyakorlat'] == 1 ? "X": "");?></td>
        <td><a href="/lesson/edit/<?=$t['id'];?>">Edit</a></td>
        <td>
            <?php if ($t['num_in_tt'] > 0): ?>
                nem lehet törölni, amíg van órarendi óra
            <?php else: ?>
                <a class="_deleteBtn" href="#" data-id="<?=$t['id'];?>">Torles</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<pre>
<?php //print_r($tanorak); ?>
</pre>

<script src="/js/jquery-3.3.1.min.js"></script>
<script>
var baseUrl = '<?php echo $baseUrl; ?>';
let from = '<?php echo $from; ?>';
$(document).ready(function() {
    $("._deleteBtn").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos torlod?')) {
            window.location.replace(baseUrl + 'lesson/delete/' + $(this).data('id') + '?from=' + from);            
        }
    });
});
</script>
