<h3>Tantárgyak</h3>

<p>Tantárgyak száma: <?php  echo count($tantargyak); ?></p>
<p><a href="/subject/edit">Új tantárgy</a></p>

<div class="row">
    <div class="col-7">
        <table class="table table-striped table-sm biggerFontSize14">
            <thead class="thead-light">
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Név</th>
                    <th style="width: 100px;">Rövidítés</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <?php foreach($tantargyak as $tantargy): ?>
                <tr>
                    <td><?php echo $tantargy['id']; ?></td>
                    <td class="text-left"><?php echo $tantargy['name']; ?></td>        
                    <td class="text-left"><?php echo $tantargy['short_name']; ?></td>        
                    <td class="text-left"><a href="/subject/edit/<?=$tantargy['id'];?>">Szerkeszt</a></td>        
                </tr>
            <?php endforeach; ?>

        </table>
    </div>
</div>

