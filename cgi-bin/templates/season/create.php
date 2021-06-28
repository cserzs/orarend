<?php // main/prepapreNew ?>
<h2>Új időszak</h2>

<?php foreach($errors as $field => $fieldErrors): ?>
    <?php foreach($fieldErrors as $error): ?>
        <div style="color: #ff0000;"><?=$error;?></div>
    <?php endforeach; ?>
<?php endforeach; ?>


<div>
    <p>A következő lépéseket kell elvégezni!</p>
    <ol>
        <li>Beállítani az általános adatokat (lenti űrlap)</li>
        <li>Beállítani melyik napokon nincs tanítás (ezekre a napokra nem lehet órát tenni!)</li>
        <li>Ha szükséges a tanárok és tantárgyak listájának fríssítése / átvétele</li>
        <li>Osztályokat felvinni (átvenni), beállítani melyik napokon van nekik tanítás</li>
        <li>Felvinni a tanórákat</li>
    </ol>
</div>

<div>
    <form id="form" method="post" action="/seasons/create">
        <label for="nev">Név: </label> <input type="text" name="nev" value="<?=$adatok['nev'];?>" size="30" /><br/>
        <label for="firstday">Első tanítási nap: </label> <input type="text" name="firstday" value="<?=$adatok['firstday'];?>" size="15" /> Hétfői napra essen! Formátum: 2018.09.03<br/>
        <label for="lastday">Utolsó tanítási nap: </label> <input type="text" name="lastday" value="<?=$adatok['lastday'];?>"  size="15" /> Vasárnapra essen! Formátum: 2018.09.03<br/>
        <label for="lessonperday">Napi óraszám (max ennyi óra lehet egy nap): </label> <input type="text" name="lessonperday" value="<?=$adatok['lessonperday'];?>"  size="10" /> (általában 8)<br/>
        <label for="starttime">Kezdő óraszám (hányadik órában kezdődik az esti tanítás): </label> <input type="text" name="starttime" value="<?=$adatok['starttime'];?>"  size="10" /> (általában 8)<br/>
        <label for="weeksnum">Tanítási hetek száma (a heti óraszám számoláshoz kell): </label> <input type="text" name="weeksnum" value="<?=$adatok['weeksnum'];?>"  size="10" /> (általában 18)<br/>
        <br/>
        <input id="btnSend" type="submit" value="Mentés" />
    </form>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script>
var baseUrl = '<?php echo $baseUrl; ?>';
$(document).ready(function() {
    $("#btnSend").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos?')) {
            $("#form").submit();
        }
    });
});
</script>
