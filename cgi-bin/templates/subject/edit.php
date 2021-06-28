<h3>Tantárgy</h3>

<?php foreach($_validator->getErrors() as $field => $errors): ?>
    <?php foreach($errors as $error): ?>
        <div style="color: #ff0000;"><?=$error;?></div>
    <?php endforeach; ?>
<?php endforeach; ?>

<form method="post" action="/subject/edit">
    <input type="hidden" value="<?php echo $tantargy['id'];?>" name="id"/>
    <p>ID: <?php echo $tantargy['id']; ?></p>
    <p>
        <label for="name">Név: </label>
        <input type="text" name="name" value="<?=$tantargy['name']?>" size="70" />
    </p>
    <p>
        <label for="short_name">Rövidítés: </label>
        <input type="text" name="short_name" value="<?=$tantargy['short_name']?>" size="10" />
    </p>
    <p>
        <button type="submit" name="save">Mentés</button>
    </p>
</form>

<br/>
<?php if ($tantargy['id'] > 0): ?>
    <p>Használva: <?=$tanorakSzama; ?><br/>
    Amíg használatban van nem lehet törölni!</p>
<?php endif; ?>
<ul>
<?php foreach($tanorak as $to): ?>
    <li><?php echo $to['osztaly']; ?>,
        <a href="/teacher/edit/<?=$to['tanarid'];?>"><?php echo $to['tanar']; ?></a>
        (<?php echo $to['num']; ?> óra) (<a href="/lesson/edit/<?=$to['id'];?>">tanóra</a>)</li>
<?php endforeach; ?>
</ul>

<?php if ($tantargy['id'] > 0 && $tanorakSzama < 1): ?>
<form id="deleteForm" method="post" action="/subject/delete">
<input type="hidden" name="id" value="<?=$tantargy['id'];?>" />    
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
