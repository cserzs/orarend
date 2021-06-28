<h3>Tanárok</h3>

<p>Tanárok száma: <?php  echo count($tanarok); ?></p>
<p><a href="/teacher/edit">Új tanár</a></p>

<div class="row">
    <div class="col-10">
        <table class="table table-striped table-sm biggerFontSize14">
            <thead class="thead-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Név</th>
                    <th style="width: 100px;">Rövidítés</th>
                    <th style="width: 100px;"></th>
                    <th style="width: 50px;">Hétfő</th>
                    <th style="width: 50px;">Kedd</th>
                    <th style="width: 50px;">Szer.</th>
                    <th style="width: 50px;">Csüt.</th>
                    <th style="width: 50px;">Pén.</th>
                </tr>
            </thead>
            <?php foreach($tanarok as $tanar): ?>
                <tr>
                    <td><?php echo $tanar['id']; ?></td>
                    <td class="text-left"><?php echo $tanar['name']; ?></td>        
                    <td class="text-left"><?php echo $tanar['short_name']; ?></td>        
                    <td class="text-left"><a href="/teacher/edit/<?=$tanar['id'];?>">Szerkeszt</a></td>        
                    <td><?php echo ($tanar['day1'] == 1 ? "X": ""); ?></td>
                    <td><?php echo ($tanar['day2'] == 1 ? "X": ""); ?></td>
                    <td><?php echo ($tanar['day3'] == 1 ? "X": ""); ?></td>
                    <td><?php echo ($tanar['day4'] == 1 ? "X": ""); ?></td>
                    <td><?php echo ($tanar['day5'] == 1 ? "X": ""); ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>
</div>
