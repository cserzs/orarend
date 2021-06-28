<h3>Osztályok</h3>

<p>Osztályok száma: <?php  echo count($osztalyok); ?></p>
<p><a href="/class/edit">Új osztály</a></p>

<table class="table table-sm table-bordered biggerFontSize14 table-hover">
    <thead>
    <tr>
        <th style="width: 50px;"></th>
        <th></th>
        <th></th>
        <th colspan="5" style="width: 250px;">Melyik nap van tanitas? (X = van)</th>
        <th style="width: 120px;"></th>
        <th style="width: 120px;"></th>
        <th style="width: 150px;"></th>
    </tr>
    <tr>
        <th>ID</th>
        <th>Név</th>
        <th>Rövid név</th>
        <th style="width: 50px;">Hétfő</th>
        <th style="width: 50px;">Kedd</th>
        <th style="width: 50px;">Szer.</th>
        <th style="width: 50px;">Csüt.</th>
        <th style="width: 50px;">Pén.</th>
        <th>Tanórák</th>
        <th>Óraszám</th>
        <th></th>
    </tr>
    </thead>    
    <?php foreach($osztalyok as $o): ?>
    <tr>
        <td><?php echo $o['id']; ?></td>
        <td><?php echo $o['name']; ?></td>
        <td><?php echo $o['short_name']; ?></td>
        <?php for($i = 1; $i < 6; $i++): ?>
            <td><?php echo ($o['day' . $i] == 1 ? "X": "-"); ?></td>        
        <?php endfor; ?>
        <td><?=isset($oraszamok[$o['id']]) ? $oraszamok[$o['id']]['num'] : '0';?></td>
        <td><?=isset($oraszamok[$o['id']]) ? $oraszamok[$o['id']]['sum'] : '0';?></td>
        <td><a href="/class/edit/<?=$o['id']?>">Szerkeszt</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
//$(document).ready(function() {
//    $("#uploadForm").submit(function(e) {
//        if (document.getElementById("subjectFile").files.length == 0) {
//            console.log("nincs fajl");
//        }
//    });
////    $("#uploadFile").click(function() {
////    });
//});
</script>
