<h3>Tanár - <?=$tanar['name']?></h3>

<?php foreach($_validator->getErrors() as $field => $errors): ?>
    <?php foreach($errors as $error): ?>
        <div style="color: #ff0000;"><?=$error;?></div>
    <?php endforeach; ?>
<?php endforeach; ?>

<form method="post" action="/teacher/edit">
    <input type="hidden" value="<?php echo $tanar['id'];?>" name="id"/>
    <p>ID: <?php echo $tanar['id']; ?></p>
    <p>
        <label for="name">Név: </label>
        <input type="text" name="name" value="<?=$tanar['name']?>" size="40" />
    </p>
    <p>
        <label for="short_name">Rövidítés: </label>
        <input type="text" name="short_name" value="<?=$tanar['short_name']?>" size="10" />
    </p>
    <p>
        Melyik nap nem jó a tanár számárá?<br/>

        <label for="day1">Hétfő: </label> <input type="checkbox" name="day1" value="1" <?=$tanar['day1'] == 1 ? 'checked' : ''?>><br>
        <label for="day2">Kedd: </label> <input type="checkbox" name="day2" value="1" <?=$tanar['day2'] == 1 ? 'checked' : ''?>><br>
        <label for="day3">Szerda: </label> <input type="checkbox" name="day3" value="1" <?=$tanar['day3'] == 1 ? 'checked' : ''?>><br>
        <label for="day4">Csütörtök: </label> <input type="checkbox" name="day4" value="1" <?=$tanar['day4'] == 1 ? 'checked' : ''?>><br>
        <label for="day5">Péntek: </label> <input type="checkbox" name="day5" value="1" <?=$tanar['day5'] == 1 ? 'checked' : ''?>><br>
    </p>
    <p>
        <button type="submit" name="save">Mentés</button>
    </p>
</form>

<br/>

<?php if ($tanar['id'] > 0): ?>
<p>Órái: <?=$tanorakSzama; ?><br/>
Amíg van órája, addig nem lehet törölni!</p>
<?php endif; ?>

<ul>
<?php foreach($tanorak as $to): ?>
    <li><?php echo $to['osztaly']; ?>,
        <?php echo $to['tantargy'];?>, 
        (<?php echo $to['num']; ?> óra) (<a href="/lesson/edit/<?=$to['id'];?>">tanóra</a>)</li>
<?php endforeach; ?>
</ul>



<br/>
<?php if ($tanar['id'] > 0 && $tanorakSzama < 1): ?>
<form id="deleteForm" method="post" action="/teacher/delete">
<input type="hidden" name="id" value="<?=$tanar['id'];?>" />    
<p>
    <input id="btnDelete" type="submit" value="Törlés">
</p>    
</form>
<?php endif; ?>

<script>
$(document).ready(function() {
    $("#btnDelete").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos törlöd?')) { $("#deleteForm").submit(); }
    });
});
</script>
