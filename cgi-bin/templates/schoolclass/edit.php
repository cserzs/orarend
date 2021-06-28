<h3>Osztály - <?=$osztaly['name']?></h3>

<?php foreach($_validator->getErrors() as $field => $errors): ?>
    <?php foreach($errors as $error): ?>
        <div style="color: #ff0000;"><?=$error;?></div>
    <?php endforeach; ?>
<?php endforeach; ?>

<form method="post" action="/class/edit">
    <input type="hidden" value="<?php echo $osztaly['id'];?>" name="class_id"/>
    <p>Órák száma: <?=$orak_szama;?></p>
    <div>ID: <?php echo $osztaly['id']; ?></div>
    <div>
        <label for="name">Név: </label>
        <input type="text" name="name" value="<?=$osztaly['name']?>" />
    </div>
    <div>
        <label for="short_name">Rövid név:</label>
        <input type="text" name="short_name" value="<?=$osztaly['short_name']?>" />
    </div>
    <div>
        Mikor van tanítás az osztály számára?<br/>

        <label for="day1">Hétfő: </label> <input type="checkbox" name="day1" value="1" <?=$osztaly['day1'] == 1 ? 'checked' : ''?>><br>
        <label for="day2">Kedd: </label> <input type="checkbox" name="day2" value="1" <?=$osztaly['day2'] == 1 ? 'checked' : ''?>><br>
        <label for="day3">Szerda: </label> <input type="checkbox" name="day3" value="1" <?=$osztaly['day3'] == 1 ? 'checked' : ''?>><br>
        <label for="day4">Csütörtök: </label> <input type="checkbox" name="day4" value="1" <?=$osztaly['day4'] == 1 ? 'checked' : ''?>><br>
        <label for="day5">Péntek: </label> <input type="checkbox" name="day5" value="1" <?=$osztaly['day5'] == 1 ? 'checked' : ''?>><br>
    </div>
    <div>
        <button type="submit" name="save">Mentés</button>
    </div>
</form>
<br/>
<form id="classDeleteForm" action="/class/delete" method="post">
    <input type="hidden" name="id" value="<?=$osztaly['id'];?>" />
    <p><button id="btnDelete" type="submit" name="delete">Törlés</button></p>
</form>

<pre>
    <?php // print_r($osztaly)?>
</pre>
<script>
var baseUrl = '<?php echo $baseUrl; ?>';
$(document).ready(function() {
    $("#btnDelete").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos törlöd?')) { $("#classDeleteForm").submit(); }
    });
});
</script>
