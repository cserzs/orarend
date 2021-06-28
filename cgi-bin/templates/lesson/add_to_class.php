<h2><?=$osztaly['name']?> tanórái</h2>
<br/>
<?php if (count($errors) > 0): ?>
<div>
    <div>Hibas sorok:</div>
    <?php foreach($errors as $error): ?>
        <?=$error;?><br/>
    <?php endforeach; ?>
</div>

<?php endif; ?>
<form id="lessonForm" method="post" action="/lesson/addToClass">
    <input type="hidden" name="classid" value="<?php echo $osztaly['id'];?>" />
    
    <div id="rowContainer">
        
    <?php if (count($tanorak) > 0): ?>
    
        <?php foreach($tanorak as $tanora): ?>
        <div>
            <label for="teacher_id">Tanar: </label>
            <select name="teachers[]" >
                <?php foreach($tanarok as $t): ?>
                    <option value="<?=$t['id'];?>" <?php echo $tanora['teacher_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
                <?php endforeach; ?>
            </select>

            <label for="subject_id">Tantargy:</label>
            <select name="subjects[]">
                <?php foreach($tantargyak as $t): ?>
                    <option value="<?=$t['id'];?>" <?php echo $tanora['subject_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
                <?php endforeach; ?>
            </select>

            <label for="lesson_num">Oraszam:</label>
            <input class="txtNum" type="text" name="nums[]" value="<?=$tanora['num']; ?>" /> vagy  
            <label for="lesson_week_num">Heti oraszam:</label>
            <input class="txtWeeknum" type="text" name="weeknums[]" value="0" /> * <?=$tanitasi_hetek; ?>
            <button class="btnRowDelete">Sor törlése</button>
        </div>
        <?php endforeach; ?>
    
    <?php else: ?>
    
        <div>
            <fieldset class="add-to-class">
            <label for="teacher_id">Tanár: </label>
            <select name="teachers[]" >
                <?php foreach($tanarok as $t): ?>
                    <option value="<?=$t['id'];?>"><?=$t['name'];?></option>
                <?php endforeach; ?>
            </select><br/>

            <label for="subject_id">Tantárgy:</label>
            <select name="subjects[]">
                <?php foreach($tantargyak as $t): ?>
                    <option value="<?=$t['id'];?>" ><?=$t['name'];?></option>
                <?php endforeach; ?>
            </select><br/>

            <label for="lesson_num">Óraszám:</label>
            <input class="txtNum" type="text" name="nums[]" value="0" /> vagy  
            <label for="lesson_week_num">heti óraszám:</label>
            <input class="txtWeeknum" type="text" name="weeknums[]" value="0" /> * <?=$tanitasi_hetek; ?> <button class="btnRowDelete" type="button">Sor törlése</button>
            </fieldset>
        </div>
    
    <?php endif; ?>
    
    </div>        
    
    <div>
        <br/>
        <button id="btnAddRow" type="button">Új sor</button><br/>
        <br/>
        <button id="btnSave" type="button" name="save">Mentés</button><br/>
    </div>
</form>

<script src="/js/jquery-3.3.1.min.js"></script>
<script id="row-template" type="text/x-template">
    <div>
        <fieldset class="add-to-class">
        <label for="teacher_id">Tanár: </label>
        <select name="teachers[]" >
            <?php foreach($tanarok as $t): ?>
                <option value="<?=$t['id'];?>"><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select><br/>

        <label for="subject_id">Tantárgy:</label>
        <select name="subjects[]">
            <?php foreach($tantargyak as $t): ?>
                <option value="<?=$t['id'];?>" ><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select><br/>

        <label for="lesson_num">Óraszám:</label>
        <input class="txtNum" type="text" name="nums[]" value="0" /> vagy  
        <label for="lesson_week_num">heti óraszám:</label>
        <input class="txtWeeknum" type="text" name="weeknums[]" value="0" /> * <?=$tanitasi_hetek; ?> <button class="btnRowDelete" type="button">Sor törlése</button>
        </fieldset>
    </div>
</script>
<script>
$(document).ready(function() {
    $("#btnAddRow").click(function() {
        $("#rowContainer").append($('#row-template').html());
    });
    $("#rowContainer").delegate('.btnRowDelete', 'click', function() {
        $(this).parent().remove();
    });
    $("#btnSave").click(function() {
        let error = false;
        $("#rowContainer div").each(function() {
            if ($(this).find('.txtNum').val() == 0 && $(this).find('.txtWeeknum').val() == 0) error = true;
        });
        if (error) {
            alert('Az óraszámok nem jók!');
        }
        else {
            $("#lessonForm").submit();
        }
    });
});
</script>
