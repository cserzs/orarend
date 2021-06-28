<?php//  main/edit_timetable ?>
<div id="loadingDiv">
    <div>Adatok betöltése...<br/>Mit</div>
</div>

<div class="menuBar">
    <div class=""><a href="/main/index">Vissza</a></div>
    <div class="">
        <select id="classSelect" class="classSelect">
            <option value="-1">Minden osztály</option>
        </select> 
    </div>
    <div class="">
        <select id="teacherSelect" class="teacherSelect">
            <option value="-1">Minden tanár</option>
        </select> 
    </div>
    <div class="">
        <input id="highlightColor" class="" type="color" value="#ffffff"> 
    </div>
    <div class="">
        <button id="btnMarkTeacher" class="">Kiemel</button/>        
    </div>
    <div class="">
        <button id="btnMarkLesson" class="">Tanóra kiemelése</button/>
    </div>
    <div id="lastSaveDiv" class="">Mentve:</div>
    <div>
        <span id="saveStatus">Nincs mentve!</span>
    </div>
    <div class="">
        <button id="btnSave" class="" name="save">Mentés</button>
    </div>
</div>

<div id="lessonBar">
    <table></table>
</div>

<div class="informationBar">
    <div id="selectedLessonDiv" class="selectedLesson">Kiválasztott tanóra adata</div>
    <div id="weeklyLessonCountDiv" class="weeklyLessonCount">Heti oraszám: 18</div>
    <div id="lessonInfoDiv" class="pointedLesson">Mutatott tanóra</div>
</div>

<div id="timetableHeaderDiv">
    <table id="timetableHeader">
        <thead></thead>
    </table>
</div>
<div id="timetableContentDiv">
    <table id="timetableContent">
        <tbody></tbody>
    </table>
</div>
<div id="savingDetails" class="savingDetails"></div>

<div id="selectedLessonMouseDiv">Proba</div>

<div id="savingDiv">
    <div>
        <div>Mentés...</div>
    </div>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/ui.js"></script>
<script src="/js/orarend.js"></script>
<script src="/js/main.js"></script>
