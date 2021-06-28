<h2>Karbantartás</h2>
<p>verzió: 2.0</p>

<p>Beállítások megváltoztatása! (Ha nincs érték megadva, akkor kiírja az aktuális értékét!)</p>
<form id="changeSettings" method="post" action="/main/change_settings">
    <p>Név: <input type="text" name="key" value="" /></p>
    <p>Érték: <input type="text" name="value" value="" /></p>
    <p><input type="submit" name="change" value="Mentés"></p>
</form>

<script type="text/javascript">
$(document).ready(function() {
    $("#btnDelete").click(function(e) {
        e.preventDefault();
        if (confirm('Biztos törlöd a teljes órarendet?')) { $("#clearForm").submit(); }
    });
});
</script>