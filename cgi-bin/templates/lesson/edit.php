<h2>Tanóra</h2>

<?php foreach($errors as $field => $fieldErrors): ?>
    <?php foreach($fieldErrors as $error): ?>
        <div style="color: #ff0000;"><?=$error;?></div>
    <?php endforeach; ?>
<?php endforeach; ?>


<?php if ($tanora['num_in_tt'] > 0): ?>
<p>A tanár és az osztály nem változtatható, amíg van óra az órarendben!</p>
<?php endif; ?>

<form method="post" action="/lesson/edit">
    <input type="hidden" value="<?php echo $tanora['id'];?>" name="lesson_id"/>
    <input type="hidden" value="<?php echo $tanora['free_num'];?>" name="free_lesson_num"/>
    <div>ID: <?php echo $tanora['id']; ?></div>
    <div>
        <label for="teacher_id">Tanár: </label>
        <select name="teacher_id" <?php echo ($tanora['num_in_tt'] > 0 ? 'disabled' : ''); ?> >
            <?php foreach($tanarok as $t): ?>
                <option value="<?=$t['id'];?>" <?php echo $tanora['teacher_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="class_id">Osztály:</label>
        <select name="class_id" <?php echo ($tanora['num_in_tt'] > 0 ? 'disabled' : ''); ?>>
            <?php foreach($osztalyok as $t): ?>
                <option value="<?=$t['id'];?>" <?php echo $tanora['class_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="subject_id">Tantárgy:</label>
        <select name="subject_id">
            <?php foreach($tantargyak as $t): ?>
                <option value="<?=$t['id'];?>" <?php echo $tanora['subject_id'] == $t['id'] ? 'selected': ''; ?>><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label for="lesson_num">Óraszám:</label>
        <input type="text" name="lesson_num" value="<?php echo $tanora['num'];?>" size="10"/><br/>
        <label for="practice">Gyakorlati óra:</label> <input type="checkbox" name="practice" value="1" <?=$tanora['practice'] == 1 ? 'checked' : ''?>>
    </div>
    
    <div>
        <b>Csoportokra bontás</b><br/>
        <i>Csak az exportnál van hatása! Az órát szétbontja két csoportra!</i><br/>
        <label for="group_1_teacher_id">1. csoport tanára: </label>
        <select name="group_1_teacher_id">
            <option value="0">Nincs</option>
            <?php foreach($tanarok as $t): ?>
                <option value="<?=$t['id'];?>" <?php echo $tanora['group_1_teacher_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select><br/>
        <label for="group_2_teacher_id">2. csoport tanára: </label>
        <select name="group_2_teacher_id">
            <option value="0">Nincs</option>
            <?php foreach($tanarok as $t): ?>
                <option value="<?=$t['id'];?>" <?php echo $tanora['group_2_teacher_id'] == $t['id'] ? 'selected': ''; ?> ><?=$t['name'];?></option>
            <?php endforeach; ?>
        </select><br/>

    </div>
    
    <div>
        <br/>
        <?php if ($tanora['id'] > 0): ?>
            Órarendi órák: <?php echo $tanora['num_in_tt']; ?><br/>
            Elhelyezetlen órák: <?=$tanora['free_num'];?><br/>
            Heti óraszám: <?= number_format($tanora['num'] / $tanitasi_hetek, 2); ?><br/>
        <?php endif; ?>
    </div>
    <div>
        
    </div>
    <br/>
    <div>
        <p><button type="submit" name="save">Mentés</button></p>
        <p><button type="submit" name="saveAndNew">Mentés és új óra</button></p>
    </div>
    <?php if ($tanora['num_in_tt'] > 0): ?>
        <input type="hidden" value="<?php echo $tanora['teacher_id'];?>" name="teacher_id"/>
        <input type="hidden" value="<?php echo $tanora['class_id'];?>" name="class_id"/>
    <?php endif; ?>
</form>

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
