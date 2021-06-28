/*
orarend szerkezete
    orarend[datum] = {
        het: hanyadik het ez
        van_tanitas: van-e tanitas a napon
        oid_n: [
            van_tanitas: van-e tanitas az osztalynak
            8: TimetableLessonCell
            9: TimetableLessonCell
            ...
        ]
        ...
    }
*/
let Orarend = {
    requiredData: ['basedata', 'teacher', 'class', 'lesson', 'tt'],
    requireDataTitle: ["Félév adatai", "Tanárok", "Osztályok", "Tanórák", "Órarend"],

    specMarker: "_",
    napioraszam: 0,
    kezdooraszam: 0,
    startingDate: null,
    endDate: null,
    numberOfWeeks: 0,
    utolsoMentes: "",
    hetiMaxOraszam: 0,

    tanarok: [],
    osztalyok: [],
    lessons: [],
    orarend: {},
    orarendCells: [],

    selectedLesson: null,

    //  id => tomb_index        
    lessonLookuptable: {},
    //  TimetableDateCell: a napok datum fejlece
    dateCells: [],
    highlightedTeachers: {},
    highlightedLesson: null,
    highlightedLessonColor: "#ff0000",

    weeklyLessonCounter: null,
    weeklyLessonDisplay: null,

    saveStatus: null,
    lessonBar: null,
    timetableHeader: null,
    rowHeadController: null,

    lessonDetailController: null,
    selectedLessonDetailController: null,
    mouseSlotController: null,
    lessonHighlighter: null
};

Orarend.load = function(type, data) {
    switch(type) {
        case 'basedata':
            this.napioraszam = data['napi_oraszam'];
            this.kezdooraszam  = data['kezdo_oraszam'];            
            this.startingDate = new Date(data['elso_tanitasi_nap']);
            this.endDate = new Date(data['utolso_tanitasi_nap']);
            this.numberOfWeeks = data['hetek_szama'];
            this.utolsoMentes = data['utolso_mentes'];
            this.hetiMaxOraszam = data['heti_max_oraszam'];
            break;
        case 'teacher':
            //this.tanarok = data;
            this.tanarok = [];
            for(let i = 0; i < data.length; i++) {
                this.tanarok.push(new Orarend.Teacher(data[i]));
            }
            break;
        case 'class':
            this.osztalyok = data;
            break;
        case 'lesson':
            this.lessons = [];
            for(let i = 0; i < data.length; i++) {
                this.lessons.push(new Orarend.Lesson(data[i]));
            }
            break;
        case 'tt':
            this.orarend = data;
            break;
    }
}

Orarend.init = async function() {
    this.lessonLookuptable = {};
    for(let i = 0; i < this.lessons.length; i++) {
        this.lessonLookuptable[this.lessons[i].id] = i;
    }

    this.populateClassSelect();
    this.populateTeacherSelect();

    this.saveStatus = new UI.SaveStatus($("#saveStatus"));

    this.weeklyLessonCounter = new Orarend.WeeklyLessonCounter(this.osztalyok, this.numberOfWeeks);
    this.weeklyLessonDisplay = new UI.WeeklyLessonDisplay($("#weeklyLessonCountDiv"), this.weeklyLessonCounter);

    this.lessonDetailController = new UI.LessonDetailController($("#lessonInfoDiv"));
    this.selectedLessonDetailController = new UI.SelectedLessonDetailController($("#selectedLessonDiv"));

    this.mouseSlotController = new UI.MouseSlotController($("#selectedLessonMouseDiv"));
    this.mouseSlotController.hide();

    this.lessonBar = new UI.LessonBar($("#lessonBar table"), this.lessons);

    this.timetableHeader = new UI.TimetableHeader($("#timetableHeader thead"), this.napioraszam, this.kezdooraszam);
    this.rowHeadController = new UI.RowHeadController(this.weeklyLessonCounter, this.hetiMaxOraszam);

    this.createTimetableContent();

    this.rowHeadController.updateOverflowStatus();
}

Orarend.populateClassSelect = function() {
    let $select = document.getElementById("classSelect"); 

    for(let i = 0; i < this.osztalyok.length; i++) {
        let $option = document.createElement("option");
        $option.textContent = this.osztalyok[i].name;
        $option.value = this.osztalyok[i].id;
        $select.appendChild($option);
    }
}

Orarend.populateTeacherSelect = function() {
    let $select = document.getElementById("teacherSelect"); 

    for(let i = 0; i < this.tanarok.length; i++) {
        let $option = document.createElement("option");
        $option.textContent = this.tanarok[i].name;
        $option.value = this.tanarok[i].id;
        $select.appendChild($option);
    }
}

//  orarend tablazat letrehozas
Orarend.createTimetableContent = function() {
    let $timetableContent = $("#timetableContent tbody");
    
    let classIndex = 0;
    let week = 0;
    let end = this.osztalyok.length * this.numberOfWeeks;
    let $cell;
    this.orarendCells = [];
    this.dateCells = [];

    for(let i = 0; i < end; i++)
    {
        let date = new Date(this.startingDate.valueOf());
        date.setDate(date.getDate() + week * 7);

        if (classIndex == 0) {
            //  sor a foglaltsag kijelzeshez
            this.createDateHeadRow($timetableContent, date, week);
        }
        
        let $row = $(document.createElement("tr"));
        $cell = $(document.createElement('td'));
        
        let $rowHead = this.rowHeadController.create(this.osztalyok[classIndex], week, classIndex == this.osztalyok.length - 1);
        $row.append($rowHead);  

        for(let k = 0; k < 5; k++) {
            for(let lessontime = 0; lessontime < this.napioraszam; lessontime++)
            {
                
                let cell = new UI.TimetableLessonCell($(document.createElement('td')), classIndex, this.getDateId(date), this.osztalyok[classIndex].id, lessontime, week);
                if (!this.orarend[cell.dateId].van_tanitas) {
                    //  X
                    cell.setContent("&#9587");
                }
                //  adatbazisbol kapott lesson.id
                let id = this.orarend[cell.dateId]['oid' + cell.osztalyId][lessontime];
                if (id > -1)
                {
                    cell.setLesson(this.getLesson(id));
                    this.weeklyLessonCounter.inc(this.osztalyok[classIndex].id, week, 1);
                }

                this.orarend[cell.dateId]['oid' + cell.osztalyId][lessontime] = cell;
                this.orarendCells.push(cell);

                if ( !this.orarend[cell.dateId]['oid' + cell.osztalyId].van_tanitas) {
                    cell.addCssClass("notInSchool");
                }

                cell.addHoverListener(
                    () =>  {
                        Orarend.lessonDetailController.show(cell.lesson);
                        Orarend.markMousePosition(cell.$element);
                        Orarend.weeklyLessonDisplay.show(cell);
                    },
                    () =>  {
                        Orarend.lessonDetailController.hide();
                        Orarend.clearMousePositionMark(cell.$element);
                        Orarend.weeklyLessonDisplay.hide();
                    }
                );
                cell.addClickListener((event) => Orarend.mouseDownHandler(event, cell) );

                if (classIndex == this.osztalyok.length - 1) cell.addCssClass("weekSeparator");
                $row.append(cell.$element);
                
                if (lessontime == this.napioraszam - 1 && k < 4) cell.addCssClass("daySeparator");
            }
            date.setDate(date.getDate() + 1);
        }
        $timetableContent.append($row);
        
        classIndex += 1;
        if (classIndex >= this.osztalyok.length) {
            classIndex = 0;
            week += 1;
        }
    }  

}

Orarend.createDateHeadRow = function($container, date, week) {
    let $row = $(document.createElement("tr"));
    
    let cell = new UI.TimetableDateCell($(document.createElement('td')), "", -1, -1, -1);
    cell.setContent((week+1) + ". hét");
    cell.addCssClass("dateHeader");
    $row.append(cell.$element);
    
    //  datumok sora
    let dateHeader = new Date(date.valueOf());
    for(let k = 0; k < 5; k++) {
        let cell = null;
        for(let n = 0; n < this.napioraszam; n++)
        {
            cell = new UI.TimetableDateCell($(document.createElement('td')), this.getDateId(dateHeader), week, k, n);
            cell.setContent(this.getShortDate(dateHeader));
            this.dateCells.push(cell);
            $row.append(cell.$element);
        }
        dateHeader.setDate(dateHeader.getDate() + 1);
        if (k < 4) cell.addCssClass("daySeparator");
    }
    
    $container.append($row);
}

Orarend.getShortDate = function(datum) {
    let m = datum.getMonth() + 1;
    let d = datum.getDate();
    let s = (m < 10 ? "0" + m: m);
    s += ".";
    s += (d < 10 ? "0" + d: d);
    return s;
}

Orarend.getDateId = function(datum) {
    let m = datum.getMonth() + 1;
    let d = datum.getDate();
    let s = "" + datum.getFullYear() + ".";
    s += (m < 10 ? "0" + m: m);
    s += ".";
    s += (d < 10 ? "0" + d: d);
    return s;
}

Orarend.getClass = function(id) {
    for(let i = 0; i < this.osztalyok.length; i++)
        if (this.osztalyok[i].id == id) return this.osztalyok[i];
    return null;
};

Orarend.getLesson = function(id) {
    return this.lessons[this.lessonLookuptable[id]];
};

Orarend.getTeacher = function(id) {
    for(let i = 0; i < this.tanarok.length; i++) {
        if (this.tanarok[i].id == id) return this.tanarok[i];
    }
    return null;
}

//  van-e tanitas az adott napon?
Orarend.isTeachingAtDay = function(dateid) {
    return this.orarend[dateid].van_tanitas;
}

//  van-e tanitas az adott napon, az adott osztalynak?
Orarend.isTeachingAt = function(dateid, classid) {
    return this.orarend[dateid]['oid' + classid].van_tanitas;
}

//  tanora cella
Orarend.getLessonCell = function(dateid, classid, lessontime) {
    return this.orarend[dateid]['oid' + classid][lessontime];
}

//  megjeloli az eger poziciot a fejlecben es oldalt
Orarend.markMousePosition = function($elem) {
    $elem.parent().children().eq(0).addClass("backgroundYellow");
    this.timetableHeader.markLessontime($elem.index(), "backgroundYellow");
}

//  torli a fejlec es az oldalso eger jelolest
Orarend.clearMousePositionMark = function($elem) {
    $elem.parent().children().eq(0).removeClass("backgroundYellow");
    this.timetableHeader.clearLessontimeMark($elem.index(), "backgroundYellow");
}

//  foglalt-e a tanar adott napon, adott oraban
Orarend.isTeacherBusy = function(tanarId, dateId, lessontime) {
    for(let i = 0; i < this.osztalyok.length; i++) {
        let lesson = this.orarend[dateId]['oid' + this.osztalyok[i].id][lessontime].lesson;
        if (lesson !== null && lesson.tanarId == tanarId) {
            return true;
        }
    }
    return false;
}

// return milyen szin jelenjen meg
Orarend.highlightTeacher = function(tanarId, color) {
    if (color == "#ffffff" || tanarId < 1) return "#ffffff";

    let returnColor = color;
    if (this.highlightedTeachers[tanarId] == undefined) {
        this.highlightedTeachers[tanarId] = color;
    }
    else if (this.highlightedTeachers[tanarId] == color) {
        //  kijeloles torlese
        delete this.highlightedTeachers[tanarId];
        color = "";
        returnColor = "#ffffff";
    }
    else {
        //  uj szin
        this.highlightedTeachers[tanarId] = color;
    }
    for(let i = 0; i < this.orarendCells.length; i++) {
        if (this.orarendCells[i].lesson !== null && this.orarendCells[i].lesson.tanarId == tanarId) {
            this.orarendCells[i].highlightTeacher(color);
        }
    }
    this.lessonBar.highlight(tanarId, color);
    return returnColor;
}

//  milyen szinnnel van kiemelve a tanar (ha kivan)
Orarend.getHighlightColorOfTeacher = function(tanarId) {
    return this.highlightedTeachers[tanarId] == undefined ? "#ffffff" : this.highlightedTeachers[tanarId];
}

Orarend.isTeacherHighlighted = function(tanarId) {
    return this.highlightedTeachers[tanarId] != undefined;
}

Orarend.toggleLessonHighlight = function() {
    if (this.highlightedLesson == null) {
        if (this.selectedLesson != null) {
            this.highlightLesson(this.selectedLesson);
        }
    }
    else {
        this.clearLessonHighlight();
    }
}

Orarend.highlightLesson = function(lesson) {
    this.highlightedLesson = lesson;
    for(let i = 0; i < this.orarendCells.length; i++) {
        if (this.orarendCells[i].lesson !== null && this.orarendCells[i].lesson.id == lesson.id) {
            this.orarendCells[i].highlightLesson(this.highlightedLessonColor);
        }
    }
}

Orarend.clearLessonHighlight = function() {
    if (this.highlightedLesson == null) return;
    for(let i = 0; i < this.orarendCells.length; i++) {
        if (this.orarendCells[i].lesson !== null && this.orarendCells[i].lesson.id == this.highlightedLesson.id) {
            this.orarendCells[i].clearLessonHighlight();
        }
    }
    this.highlightedLesson = null;
}

//  a napok felett kijelzi, hogy mikor foglalt a tanar vagy az osztaly
Orarend.updateLessontimeAvailability = function(lesson) {
    this.clearLessontimeAvailability();

    for(let i = 0; i < this.dateCells.length; i++) {
        let cell = this.dateCells[i];
        let teacher = this.getTeacher(lesson.tanarId);
        if (this.isTeacherBusy(lesson.tanarId, cell.dateId, cell.lessontime)) {
            cell.addCssClass("lessontimeColor2");
        }
        else if (!teacher.canTeach(cell.day)) {
            cell.addCssClass("lessontimeColor4");
        }
        else if (!this.orarend[cell.dateId]['oid' + lesson.osztalyId].van_tanitas) {
            cell.addCssClass("lessontimeColor3");
        }
        else {
            cell.addCssClass("lessontimeColor1");
        }
    }
}

Orarend.clearLessontimeAvailability = function() {
    for(let i = 0; i < this.dateCells.length; i++) {
        this.dateCells[i].removeCssClass("lessontimeColor1 lessontimeColor2 lessontimeColor3 lessontimeColor4");
    }
}

Orarend.highlightClass = function(osztalyId) {
    this.rowHeadController.highlightClass(osztalyId);
    let cells = this.rowHeadController.getRowHeads(osztalyId);
    for(let i = 0; i < cells.length; i++) {
        cells[i].siblings().each(function() {
            if ($(this).hasClass("notInSchool")) $(this).addClass("classSelectionCellDarkcolor");
            else $(this).addClass("classSelectionCellColor");
        });
    }
}

Orarend.clearClassHighlight = function(osztalyId) {
    this.rowHeadController.clearHighlight(osztalyId);
    let cells = this.rowHeadController.getRowHeads(osztalyId);
    for(let i = 0; i < cells.length; i++) {
        cells[i].siblings().removeClass("classSelectionCellColor classSelectionCellDarkcolor");        
    }
}

Orarend.setSelectedLesson = function(lessonid) {
    //  elozo torlese ha van
    this.clearSelectedLesson();
    
    this.selectedLesson = this.getLesson(lessonid);
    this.highlightClass(this.selectedLesson.osztalyId);
    this.updateLessontimeAvailability(this.selectedLesson);
}

Orarend.clearSelectedLesson = function() {
    if (this.selectedLesson == null) return;
    this.clearClassHighlight(this.selectedLesson.osztalyId);
    this.clearLessontimeAvailability();
    this.clearLessonHighlight();
    this.selectedLesson = null;
}

//  egy tanora kivalasztasa a tanora listarol
Orarend.selectLessonFromBar = function(lessonbarItem) {
    this.setSelectedLesson(lessonbarItem.lesson.id);
    this.selectedLessonDetailController.show(lessonbarItem.lesson);
    this.mouseSlotController.show(lessonbarItem.lesson);
}

//  param: TimetableLessonCell
Orarend.mouseDownHandler = function(event, cell) {
    if (event.which == 1)
    {
        //  bal gomb
        if (this.selectedLesson == null)
        {
            //  orat felvenni, ha van
            if (cell.lesson === null) return;

            this.setSelectedLesson(cell.lesson.id);

            this.selectedLesson.oraszam += 1;
            this.lessonBar.update(this.selectedLesson);
            this.selectedLessonDetailController.show(cell.lesson);
            this.mouseSlotController.show(cell.lesson, event);
            this.lessonDetailController.show(cell.lesson);

            this.weeklyLessonCounter.dec(cell.osztalyId, cell.week, 1);
            this.rowHeadController.updateOverflowStatus();

            cell.setLesson(null);

            this.saveStatus.needToSave();
            this.updateLessontimeAvailability(this.selectedLesson);
        }
        else {
            //  ora elhelyezese a targetCell-be, ha lehet
            let targetCell = cell;

            if ( !this.orarend[targetCell.dateId].van_tanitas) return;
            
            if (targetCell.osztalyId != this.selectedLesson.osztalyId) {
                //  nem az osztalyhoz tartozo cellara lett klikkelve!!! frissiteni kell a targetCell-t 
                targetCell = this.orarend[targetCell.dateId]['oid' + this.selectedLesson.osztalyId][targetCell.lessontime];
            }
            
            if (targetCell.lesson !== null) {
                //  mar van ora
                if (targetCell.lesson.id == this.selectedLesson.id) {
                    //  ha azonos a kijelolttel, akkor felvenni
                    this.selectedLesson.oraszam += 1;

                    targetCell.setLesson(null);

                    this.lessonBar.update(this.selectedLesson);
                    this.selectedLessonDetailController.show(this.selectedLesson);
                    this.lessonDetailController.show(this.selectedLesson);
                    this.mouseSlotController.show(this.selectedLesson);
                    this.updateLessontimeAvailability(this.selectedLesson);

                    this.weeklyLessonCounter.dec(this.selectedLesson.osztalyId, targetCell.week, 1);
                    this.rowHeadController.updateOverflowStatus();
                    this.saveStatus.needToSave();
                    
                    return;
                }
                else {
                    return;
                }
            }
            
            if (this.selectedLesson.oraszam < 1) return;

            if (this.isTeacherBusy(this.selectedLesson.tanarId, targetCell.dateId, targetCell.lessontime)) return;

            targetCell.setLesson(this.selectedLesson);

            if (this.isTeacherHighlighted(this.selectedLesson.tanarId)) {
                targetCell.highlightTeacher(this.getHighlightColorOfTeacher(this.selectedLesson.tanarId));
            }
            if (this.highlightedLesson != null && this.highlightedLesson.id == this.selectedLesson.id) {
                targetCell.highlightLesson(this.highlightedLessonColor);
            }
            
            this.selectedLesson.oraszam -= 1;
            this.lessonBar.update(this.selectedLesson);
            this.lessonDetailController.show(this.selectedLesson);
            this.updateLessontimeAvailability(this.selectedLesson);

            if (this.selectedLesson.oraszam < 1) {
                this.clearSelectedLesson();
                this.mouseSlotController.hide();
            }
            else {
                this.mouseSlotController.show(this.selectedLesson);
            }
            
            this.selectedLessonDetailController.show(this.selectedLesson);
            this.saveStatus.needToSave();
            
            this.weeklyLessonCounter.inc(targetCell.osztalyId, targetCell.week, 1);
            this.rowHeadController.updateOverflowStatus();

        }
    }
    else if (event.which == 3)
    {
        //  jobb klikk
        if (this.selectedLesson == null) {
            //  ora torlese
            if (cell.lesson === null) return;

            cell.lesson.oraszam += 1;
            
            this.lessonDetailController.hide();
            this.lessonBar.update(cell.lesson);
            this.saveStatus.needToSave();
            
            this.weeklyLessonCounter.dec(cell.osztalyId, cell.week, 1);
            this.rowHeadController.updateOverflowStatus();

            cell.setLesson(null);
        }
        else {
            //  kijeloles torlese
            this.clearSelectedLesson();
            this.selectedLessonDetailController.show(null);
            this.mouseSlotController.hide();
        }
    }
};

/**
 * Az orarend tomb felepitese: datum, pozicio, lesson_id
 * A tanorak tomb felepitese: lessonid, oraszam
 * @returns array
 */
Orarend.prepareToSave = function() {
    let orarend = [];
    // var tanorak = [];
    for(let i = 0; i < this.orarendCells.length; i++) {
        let cell = this.orarendCells[i];
        if (cell.lesson !== null) {
            orarend.push([cell.dateId, cell.lessontime, cell.lesson.id]);
        }
    }

    //        for(var i = 0; i < this.lessons.length; i++) {
    //            if (this.lessons[i]._oraszam != this.lessons[i]) {
    //                tanorak.push([this.lessons[i].id, this.lessons[i].oraszam]);
    //                this.lessons[i]._oraszam = this.lessons[i].oraszam;
    //            }
    //        }
    
    return {
        orarend: orarend
    };
}

Orarend.WeeklyLessonCounter = class {
    constructor(osztalyok, week) {
        this.weeklyLessons = {};
        for(let i = 0; i < osztalyok.length; i++) {
            let oid = 'oid' + osztalyok[i].id;
            this.weeklyLessons[oid] = [];
            for(let k = 0; k < week; k++) {
                this.weeklyLessons[oid]['w' + k] = 0;
            }
        }
    }
    inc(classId, week, value) {
        this.weeklyLessons['oid' + classId]['w' + week] += value;
    }
    dec(classId, week, value) {
        this.weeklyLessons['oid' + classId]['w' + week] -= value;
    }
    get(classId, week) {
        return this.weeklyLessons['oid' + classId]['w' + week];
    }
}

Orarend.Lesson = class {
    constructor(dataJson) {
        this.id = dataJson['id'];
        this.tanarteljes = dataJson['tanarteljes'];
        this.tanarId = dataJson['tanarid'];
        this.tanar = dataJson['tanar'];
        this.tantargy = dataJson['tantargy'];
        this.tantargyId = dataJson['tantargyid'];
        this.osztalyId = dataJson['osztalyid'];
        this.osztalynev = dataJson['osztalynev'];
        this.osszoraszam = dataJson['osszoraszam'];
        this.oraszam = dataJson['oraszam'];
        this._oraszam = dataJson['_oraszam'];
        this.practice = dataJson['practice'];
    }
}
Orarend.Teacher = class {
    constructor(jsonData) {
        this.id = jsonData['id'];
        this.name = jsonData['name'];
        this.short_name = jsonData['short_name'];
        this.day1 = jsonData['day1'];
        this.day2 = jsonData['day2'];
        this.day3 = jsonData['day3'];
        this.day4 = jsonData['day4'];
        this.day5 = jsonData['day5'];
    }
    canTeach(day) {
        return this['day' + (day+1)] == 0;
    }
}
/*
Orarend.Schoolclass = class {
    constructor(jsonData) {
        this.id = jsonData['id'];
        this.name = jsonData['name'];
    }
}
*/