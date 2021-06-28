let Orarend = {
    requiredData: ['basedata', 'class', 'lesson', 'tt'],

    specMarker: "_",
    napok: ["Hetfő", "Kedd", "Szerda", "Csütörtök", "Péntek"],
    napioraszam: 0,
    kezdooraszam: 0,
    startingDate: null,
    endDate: null,
    numberOfWeeks: 0,
    utolsoMentes: "",
    hetiMaxOraszam: 0,
    exportKivetelek: [],

    osztalyok: [],
    lessons: [],
    orarend: {},

    selectedLesson: null,
    markedWeeks: [],
    weeklyLessonsCount: {},

    lookupTables: {
        //  id => tomb_index        
        lessonIdToIndex: {},
        //  az osztalyokat mutato cellak (a 1. oszlop az orarendben)
        $classCells: {},
        $weeklyAvailability: {},
        $lessontimesRow: {},
        highlightedTeachers: {},
        $weeklyLessonCountDiv: null
    },

    $tableContainer: null,
    $timetableContent: null,
    $timetableContentDiv: null,
    $lessonInfoDiv: null,
    $selectedLessonDiv: null,
    $lessonBar: null,
    $timetableHeader: null,
    $selectedLessonMouseDiv: null

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
            this.exportKivetelek = data['export_kivetelek'];
            break;
        case 'class':
            this.osztalyok = data;
            break;
        case 'lesson':
            this.lessons = data;
            break;
        case 'tt':
            this.orarend = data;
            break;
    }
}

Orarend.init = function() {
    this.createLessonLookupTable();

    this.lookupTables.$weeklyLessonCountDiv = $("#weeklyLessonCountDiv");

    this.createLessonBar(this.lessons);

    this.createTimetableHeader();

    this.createTimetableContent();

    this.$selectedLessonMouseDiv = $("#selectedLessonMouseDiv");
    this.$selectedLessonMouseDiv.toggle(false);
    this.$selectedLessonDiv = $("#selectedLessonDiv");

    this.$lessonInfoDiv = $("#lessonInfoDiv");

    this.updateWeeklyLessonsCount();
   
}

Orarend.createLessonLookupTable = function() {
    this.lookupTables.lessonIdToIndex = {};
    for(let i = 0; i < this.lessons.length; i++) {
        this.lookupTables.lessonIdToIndex['to' + this.lessons[i].id] = i;
    }
}

//  a tanora listaban levo adatokat frissiti (pl: oraszam)
Orarend.updateLessonInfoOnBar = function(lesson) {
    let tanarnev = lesson.tanar;
    if (this.exportKivetelek.indexOf(+lesson.tantargyid) > -1) tanarnev += this.specMarker;
    lesson.$listelem.html(tanarnev + "<br/>" + lesson.osztalynev + ", " + lesson.oraszam);
    if (lesson.oraszam < 1) lesson.$listelem.addClass("noMoreLesson")
    else lesson.$listelem.removeClass("noMoreLesson");
}

//  tanorak listaja letrehozasa
Orarend.createLessonBar = function(lessons) {
    this.$lessonBar = $("#lessonBar table");
    let $row = $(document.createElement("tr"));
    for(let i = 0; i < lessons.length; i++) {
        let $cell = $(document.createElement("td"));
        lessons[i].$listelem = $cell;
        $cell.data("lessonid", lessons[i].id);
        this.updateLessonInfoOnBar(lessons[i]);
        $row.append($cell);
    }
    this.$lessonBar.append($row);
}

//  az orarend tablazat fejlece
Orarend.createTimetableHeader = function() {
    this.$timetableHeader = $("#timetableHeader thead");
    
    let $row = $(document.createElement("tr"));
    let $cell = $(document.createElement("th"));

    //  osztalyok oszlopa
    $cell.html(" ");
    $cell.data("lessonid", -2);
    $row.append($cell);
    
    //  napok
    for(let i = 0; i < 5; i++) {
        let $cell = $(document.createElement("th"));
        $cell.attr("colspan", this.napioraszam);
        $cell.html(this.napok[i]);
        if (i < 4) $cell.addClass("daySeparator");            
        $row.append($cell);
    }
    this.$timetableHeader.append($row);

    //  masodik sor: oraszamok
    $row = $(document.createElement("tr"));
    $cell = $(document.createElement("th"));

    //  osztalyok oszlop
    $cell.html(" ");
    $cell.data("lessonid", -2);
    $row.append($cell);

    //  oraszamok naponta
    for(let k = 0; k < 5; k++) {
        for(let i = 0; i < this.napioraszam; i++) {
            let $cell = $(document.createElement("th"));
            $cell.html( (this.kezdooraszam + i) );
            $row.append($cell);
        }
        if (k < 4) $cell.addClass("daySeparator");
    }
    this.$timetableHeader.append($row);
    this.lookupTables.$lessontimesRow = $row;

}

//  orarend tablazat letrehozas
Orarend.createTimetableContent = function() {
    this.$timetableContentDiv = $("#timetableContentDiv");
    this.$timetableContent = $("#timetableContent tbody");
    
    this.lookupTables.$classCells = {};
    this.weeklyLessonsCount = {};

    let classIndex = 0;
    let week = 0;
    let end = this.osztalyok.length * this.numberOfWeeks;
    let $cell;

    for(let i = 0; i < end; i++)
    {
        let date = new Date(this.startingDate.valueOf());
        date.setDate(date.getDate() + week * 7);

        if (classIndex == 0) {
            //  sor a foglaltsag kijelzeshez
            let $row = $(document.createElement("tr"));
            
            $cell = $(document.createElement('td'));
            $cell.html((week+1) + ". het");
            $cell.data("lessonid", -2);
            $cell.addClass("dateHeader");
            $row.append($cell);
            
            let dateHeader = new Date(date.valueOf());
            for(let k = 0; k < 5; k++) {
                for(let n = 0; n < this.napioraszam; n++)
                {
                    $cell = $(document.createElement('td'));

                    $cell.html("-");
                    $cell.html(this.getShortDate(dateHeader));
                    $cell.addClass("dateHeader");
                    $cell.data("lessonid", -2);
                    $row.append($cell);
                }
                dateHeader.setDate(dateHeader.getDate() + 1);
                if (k < 4) $cell.addClass("daySeparator");
            }
            
            this.$timetableContent.append($row);
            this.lookupTables.$weeklyAvailability['w' + week] = $row;
        }
        
        
        let $row = $(document.createElement("tr"));
        $cell = $(document.createElement('td'));
        
        let oid = 'oid' + this.osztalyok[classIndex].id;
        
        if (this.weeklyLessonsCount[oid] == undefined) this.weeklyLessonsCount[oid] = [];
        if (this.weeklyLessonsCount[oid]['w' + week] == undefined) this.weeklyLessonsCount[oid]['w' + week] = 0;
                
        $cell = $(document.createElement('td'));
        $cell.html('<span>' + this.osztalyok[classIndex].name + '</span><span class="classLessonWeeklyCountInvalid"></span>');
        $cell.data("lessonid", -3);
        $cell.data("classId", this.osztalyok[classIndex].id);
        
        if (this.lookupTables.$classCells[oid] == undefined) this.lookupTables.$classCells[oid] = [];
        this.lookupTables.$classCells[oid].push($cell);
        
        if (classIndex == this.osztalyok.length - 1) $cell.addClass("weekSeparator");
        $row.append($cell);

        for(let k = 0; k < 5; k++) {
            for(let lessontime = 0; lessontime < this.napioraszam; lessontime++)
            {
                /*
                let cell = new Orarend.LessonCell($(document.createElement('td')), classIndex, this.getDateId(date), this.osztalyok[classIndex].id, lessontime, week);
                if (!this.orarend[cell.getDateId()].van_tanitas) {
                    //  X
                    cell.setContent("&#9587");
                }
                let id = this.orarend[cell.getDateId()]['oid' + cell.getClassId()][lessontime];
                if (id > -1)
                {
                    cell.setLessonId(id);

                    let lesson = this.getLesson(id);
                    let cellContent = '<span>' + lesson.tanar;
                    if (this.exportKivetelek.indexOf(+lesson.tantargyid) > -1) cellContent += this.specMarker;
                    cellContent += "</span>";

                    cell.setContent(cellContent);
                    
                    this.weeklyLessonsCount[oid]['w' + week] += 1;
                }

                this.orarend[cell.getDateId()]['oid' + cell.getClassId()][lessontime] = cell;

                if ( !this.orarend[cell.getDateId()]['oid' + cell.getClassId()].van_tanitas) {
                    cell.addCssClass("notInSchool");
                }

                if (classIndex == this.osztalyok.length - 1) cell.addCssClass("weekSeparator");
                $row.append(cell.getDomElement());
                */
                                
                let $cell = $(document.createElement('td'));

                $cell.data("lessonid", -1);
                $cell.data("classIndex", classIndex);
                $cell.data("dateId", this.getDateId(date));                
                $cell.data("oid", 'oid' + this.osztalyok[classIndex].id);
                $cell.data("lessontime", lessontime);
                $cell.data("week", week);

                if (this.orarend[$cell.data("dateId")].van_tanitas) {
                    $cell.html(" ");
                }
                else {
                    //  X
                    $cell.html("&#9587");
                }

                let id = this.orarend[$cell.data("dateId")][$cell.data("oid")][lessontime];
                if (id > -1)
                {
                    $cell.data("lessonid", id);
                    let lesson = this.getLesson(id);
                    let cellContent = '<span>' + lesson.tanar;
                    if (this.exportKivetelek.indexOf(+lesson.tantargyid) > -1) cellContent += this.specMarker;
                    cellContent += "</span>";
                    $cell.html(cellContent);
                    
                    this.weeklyLessonsCount[oid]['w' + week] += 1;
                }
                this.orarend[$cell.data("dateId")][$cell.data("oid")][lessontime] = $cell;

                if ( !this.orarend[$cell.data("dateId")][$cell.data("oid")].van_tanitas) {
                    $cell.addClass("notInSchool");
                }

                if (classIndex == this.osztalyok.length - 1) $cell.addClass("weekSeparator");
                $row.append($cell);
                
            }
            if (k < 4) $cell.addClass("daySeparator");
            date.setDate(date.getDate() + 1);
        }
        this.$timetableContent.append($row);
        
        classIndex += 1;
        if (classIndex >= this.osztalyok.length) {
            classIndex = 0;
            week += 1;
        }
    }    
}

Orarend.getShortDate = function(datum) {
    let m = datum.getMonth() + 1;
    let d = datum.getDate();
    let s = "";
    if (m < 10) s += "0" + m;
    else s += m;
    s += ".";
    if (d < 10) s += "0" + d;
    else s += d;
    return s;
}

Orarend.getDateId = function(datum) {
    let m = datum.getMonth() + 1;
    let d = datum.getDate();
    let s = "" + datum.getFullYear() + ".";
    if (m < 10) s += "0" + m;
    else s += m;
    s += ".";
    if (d < 10) s += "0" + d;
    else s += d;
    return s;
}

Orarend.getClass = function(id) {
    for(let i = 0; i < this.osztalyok.length; i++)
        if (this.osztalyok[i].id == id) return this.osztalyok[i];
    return null;
};

Orarend.getLesson = function(id) {
    let index = this.lookupTables.lessonIdToIndex['to' + id];
    return this.lessons[index];
};

Orarend.showLessonInfo = function($elem) {
    if ($elem.data("lessonid") < 0) this.$lessonInfoDiv.html($elem.data("dateId"));
    else {
        let lesson = this.getLesson($elem.data("lessonid"));
        this.$lessonInfoDiv.html( this.getFormattedLesson(lesson) );
    }
}

Orarend.hideLessonInfo = function() {
    this.$lessonInfoDiv.html("&nbsp;");
}

//  megjeloli az eger poziciot a fejlecben es oldalt
Orarend.markMousePosition = function($elem) {
    $elem.parent().children().eq(0).addClass("backgroundYellow");
    // $elem.parent().children().eq(1).children().first().addClass("backgroundYellow");
    let index = $elem.index();
    this.lookupTables.$lessontimesRow.children().eq(index).addClass("backgroundYellow");
}

//  torli a fejlec es az oldalso eger jelolest
Orarend.clearMousePositionMark = function($elem) {
    $elem.parent().children().eq(0).removeClass("backgroundYellow");
    // $elem.parent().children().eq(1).children().first().removeClass("backgroundYellow");
    let index = $elem.index();
    this.lookupTables.$lessontimesRow.children().eq(index).removeClass("backgroundYellow");
}

//  egy osztaly heti oraszamanak megjelenitese
Orarend.showWeeklyLessonsCount = function($elem) {
    let oid = $elem.data("oid");
    let w = $elem.data("week");
    let n = 0;
    if (oid == undefined) n = 0;
    else n = this.weeklyLessonsCount[oid]['w' + w];
    this.lookupTables.$weeklyLessonCountDiv.html("Heti oraszam: " + n);
}

//  a tanora megjelenitese
Orarend.getFormattedLesson = function(lesson) {
    return "[" + lesson.id + "] " + 
            lesson.tanarteljes + " | " + 
            lesson.osztalynev + " | " + 
            lesson.tantargy + " | " +
            " összes / szabad: " + lesson.osszoraszam + 
            " / " + lesson.oraszam;
}

//  foglalt-e a tanar adott napon, adott oraban
Orarend.isTeacherBusy = function(tanar, dateId, lessontime) {
    for(let i = 0; i < this.osztalyok.length; i++) {
        let lessonid = this.orarend[dateId]['oid' + this.osztalyok[i].id][lessontime].data("lessonid");
        if (lessonid > -1)
        {
            let lesson = this.getLesson(lessonid);
            if (lesson.tanar == tanar) return true;
        }
    }
    return false;
}

Orarend.filterLessonDiv = function(classid, teacherid) {
    let visible = function(id, selectedid) {
        if (selectedid == -1) return true;
        return (selectedid == id);
    };
    for(let i = 0; i < this.lessons.length; i++) {
        if (visible(this.lessons[i].osztalyid, classid) && visible(this.lessons[i].tanarid, teacherid)) {
            this.lessons[i].$listelem.show();
        }
        else {
            this.lessons[i].$listelem.hide();
        }
    }
}

Orarend.highlightTeacher = function(tanarid, color) {
    if (this.lookupTables.highlightedTeachers[tanarid] == undefined) {
        this.lookupTables.highlightedTeachers[tanarid] = color;
    }
    else if (this.lookupTables.highlightedTeachers[tanarid] == color) {
        //  kijeloles torlese
        delete this.lookupTables.highlightedTeachers[tanarid];
        color = "";
    }
    else {
        //  uj szin
        this.lookupTables.highlightedTeachers[tanarid] = color;
    }
    let currentDate = new Date(this.startingDate.valueOf());
    while(currentDate <= this.endDate)
    {
        let dateId = this.getDateId(currentDate);
        if (this.orarend[dateId] == undefined || !this.orarend[dateId].van_tanitas)
        {
            currentDate.setDate(currentDate.getDate() + 1);
            continue;
        }
        
        for(let i = 0; i < this.osztalyok.length; i++) 
        {
            let oid = 'oid' + this.osztalyok[i].id;
            for(let k = 0; k < this.napioraszam; k++) {
                if (this.orarend[dateId][oid][k].data('lessonid') > -1)
                {
                    let lesson = this.getLesson(this.orarend[dateId][oid][k].data('lessonid'));
                    if (lesson.tanarid == tanarid) {
                        this.orarend[dateId][oid][k].children().first().css("background-color", color);
                    }
                }
            }
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    for(let i = 0; i < this.lessons.length; i++) {
        if (this.lessons[i].tanarid == tanarid) {
            this.lessons[i].$listelem.css("background-color", color);
        }
    }
}

Orarend.getVisibleWeeks = function() {
    let parentTop = this.$timetableContentDiv.offset().top;
    let parentHeight = this.$timetableContentDiv.height();
    let result = [];
    for(let i = 0; i < this.numberOfWeeks; i++) {
        let y = this.lookupTables.$weeklyAvailability['w' + i].offset().top;
        if (y >= parentTop && y <= parentTop + parentHeight) {
            result.push(i)
        }
    }
    return result;
}

//  a het felett kijelzi, hogy mikor foglalt a tanar vagy az osztaly
Orarend.updateLessontimeAvailability = function(lessonid) {
    let lesson = this.getLesson(lessonid);
    this.clearLessontimeAvailability();
    for(let week = 0; week < this.numberOfWeeks; week++) {
        
        let index = 1;            

        let date = new Date(this.startingDate.valueOf());            
        date.setDate(date.getDate() + week * 7);

        for(let i = 0; i < 5; i++) {

            let dateId = this.getDateId(date);

            for(let n = 0; n < this.napioraszam; n++)
            {
                if (this.isTeacherBusy(lesson.tanar, dateId, n)) {
                    this.lookupTables.$weeklyAvailability['w' + week].children().eq(index).addClass("lessontimeColor2");
                }
                else if (!this.orarend[dateId]['oid' + lesson['osztalyid']].van_tanitas) {
                    this.lookupTables.$weeklyAvailability['w' + week].children().eq(index).addClass("lessontimeColor3");
                }
                else {
                    this.lookupTables.$weeklyAvailability['w' + week].children().eq(index).addClass("lessontimeColor1");
                }
                index += 1;
            }

            date.setDate(date.getDate() + 1);
        }
    }
}

Orarend.clearLessontimeAvailability = function() {
    for(let week = 0; week < this.numberOfWeeks; week++) {
        this.lookupTables.$weeklyAvailability['w' + week].children().removeClass("lessontimeColor1 lessontimeColor2 lessontimeColor3");
    }
}

//  a heti oraszam ellenorzese
Orarend.updateWeeklyLessonsCount = function() {
    for(let i = 0; i < this.osztalyok.length; i++) { 
        let oid = 'oid' + this.osztalyok[i].id;
        for(let w = 0; w < this.numberOfWeeks; w++) {
            if (this.weeklyLessonsCount[oid]['w' + w] > this.maxLessonsPerWeek) {
                this.lookupTables.$classCells[oid][w].children().eq(1).html('#');
            }
            else {
                this.lookupTables.$classCells[oid][w].children().eq(1).html("");
            }
        }
    }
}

Orarend.showClassHighlight = function(osztalyid) {
    let oid = 'oid' + osztalyid;
    for(let i = 0; i < this.lookupTables.$classCells[oid].length; i++) {
        this.lookupTables.$classCells[oid][i].find('span').addClass("classSelection");
        this.lookupTables.$classCells[oid][i].siblings().each(function() {
//                if ( !$(this).hasClass("notInSchool")) $(this).addClass("classSelectionCellColor");
            if ($(this).hasClass("notInSchool")) $(this).addClass("classSelectionCellDarkcolor");
            else $(this).addClass("classSelectionCellColor");
        });
    }
}

Orarend.hideClassHighlight = function(osztalyid) {
    let oid = 'oid' + osztalyid;
    for(let i = 0; i < this.lookupTables.$classCells[oid].length; i++) {
        this.lookupTables.$classCells[oid][i].find('span').removeClass("classSelection");
        this.lookupTables.$classCells[oid][i].siblings().removeClass("classSelectionCellColor classSelectionCellDarkcolor");
    }
}

Orarend.setSelectedLesson = function(lessonid) {
    //  elozo torlese ha van
    this.clearSelectedLesson();
    
    this.selectedLesson = this.getLesson(lessonid);
    this.showClassHighlight(this.selectedLesson.osztalyid);
    this.updateLessontimeAvailability(this.selectedLesson.id);
}

Orarend.clearSelectedLesson = function() {
    if (this.selectedLesson == null) return;
    this.hideClassHighlight(this.selectedLesson.osztalyid);
    this.clearLessontimeAvailability();
    this.selectedLesson = null;
}

//  egy tanora kivalasztasa a tanora listarol
Orarend.selectLessonFromBar = function($elem) {
    this.setSelectedLesson($elem.data("lessonid"));
    this.updateSelectedLessonDiv();
}

Orarend.updateSelectedLessonDiv = function() {
    if (this.selectedLesson == null)
    {
        this.$selectedLessonDiv.html("Kivalasztott tanora adatai");
        this.$selectedLessonMouseDiv.toggle(false);
        return;
    }
    this.$selectedLessonDiv.html( this.getFormattedLesson(this.selectedLesson) );
    this.$selectedLessonMouseDiv.html(this.selectedLesson.tanar + "<br/>" + this.selectedLesson.oraszam);
    this.$selectedLessonMouseDiv.toggle(true);
}

Orarend.mouseMoveHandler = function(e) {
    if (this.selectedLesson == null) return;
    this.$selectedLessonMouseDiv.css({left: e.pageX + 10, top: e.pageY});
}

Orarend.mouseDownHandler = function(event, $elem) {
    if ($elem.data("lessonid") < -1) return;

    let lessonid = $elem.data("lessonid");
    let dateId = $elem.data("dateId");
    let oid = $elem.data("oid");
    let lessontime = $elem.data("lessontime");
    let week = $elem.data("week");
    
    if (event.which == 1)
    {
        //  bal gomb
        if (this.selectedLesson == null)
        {
            //  orat felvenni, ha van                
            if (lessonid < 0) return;
                            
            $elem.data("lessonid", -1);
            $elem.html(" ");
            
            this.setSelectedLesson(lessonid);
            
            this.selectedLesson.oraszam += 1;
            this.updateSelectedLessonDiv();
            this.updateLessonInfoOnBar(this.selectedLesson);
            this.updateLessontimeAvailability(this.selectedLesson.id);
            this.changeSaveStatus(true);
            
            this.weeklyLessonsCount[oid]['w' + week] -= 1;
            this.updateWeeklyLessonsCount();
        }
        else {
            //  ora elhelyezese, ha lehet
            
            if ( !this.orarend[dateId].van_tanitas) return;
            
            if (oid != ('oid' + this.selectedLesson.osztalyid)) {
                //  nem az osztalyhoz tartozo cellara lett klikkelve!!! frissiteni kell: lessonid, oid, 
                oid = 'oid' + this.selectedLesson.osztalyid;
                lessonid = this.orarend[dateId][oid][lessontime].data('lessonid');
            }
            
            if (lessonid > -1) {
                //  mar van ora
                if (lessonid == this.selectedLesson.id) {
                    //  ha azonos a kijelolttel, akkor felvenni
                    this.selectedLesson.oraszam += 1;
                    this.orarend[dateId][oid][lessontime].data('lessonid', -1);
                    this.orarend[dateId][oid][lessontime].html(" ");
                    this.updateSelectedLessonDiv();
                    this.updateLessonInfoOnBar(this.selectedLesson);
                    this.updateLessontimeAvailability(this.selectedLesson.id);

                    this.weeklyLessonsCount[oid]['w' + week] -= 1;
                    this.updateWeeklyLessonsCount();
                    this.changeSaveStatus(true);
                    
                    return;
                }
                else {
                    return;
                }
            }
            
            if (this.selectedLesson.oraszam < 1) return;

            if (this.isTeacherBusy(this.selectedLesson.tanar, dateId, lessontime)) return;

//                var oid2 = 'oid' + this.selectedLesson.osztalyid;
            this.orarend[dateId][oid][lessontime].data("lessonid", this.selectedLesson.id);

            var cellContent = '<span>' + this.selectedLesson.tanar;
            if (this.exportKivetelek.indexOf(+this.selectedLesson.tantargyid) > -1) cellContent += this.specMarker;
            cellContent += "</span>";
            this.orarend[dateId][oid][lessontime].html(cellContent);
            
            if (this.lookupTables.highlightedTeachers[this.selectedLesson.tanarid] !== undefined) {
                this.orarend[dateId][oid][lessontime].children().first().css('background-color', this.lookupTables.highlightedTeachers[this.selectedLesson.tanarid]);
            }
            
            this.selectedLesson.oraszam -= 1;
            this.updateLessonInfoOnBar(this.selectedLesson);
            this.updateLessontimeAvailability(this.selectedLesson.id);

            if (this.selectedLesson.oraszam < 1) this.clearSelectedLesson();
            
            this.updateSelectedLessonDiv();
            this.changeSaveStatus(true);
            
            this.weeklyLessonsCount[oid]['w' + week] += 1;
            this.updateWeeklyLessonsCount();
        }
    }
    else if (event.which == 3)
    {
        if (this.selectedLesson == null) {
            //  ora torlese
            if (lessonid > -1) {
                let lesson = this.getLesson(lessonid);
                lesson.oraszam += 1;
                
                $elem.data("lessonid", -1);
                $elem.html(" ");
                
                this.updateLessonInfoOnBar(lesson);
                this.changeSaveStatus(true);
                
                this.weeklyLessonsCount[oid]['w' + week] -= 1;
                this.updateWeeklyLessonsCount();
            }
        }
        else {
            //  kijeloles torlese
            this.clearSelectedLesson();
            this.updateSelectedLessonDiv();
        }
    }
}

/*
* A mentes szoveg lathatosaga.
*/
Orarend.changeSaveStatus = function(needSave) {
    if (needSave) $("#saveStatus").show();
    else $("#saveStatus").hide();
}

/**
 * Az orarend tomb felepitese: datum, pozicio, lesson_id
 * A tanorak tomb felepitese: lessonid, oraszam
 * @returns array
 */
Orarend.prepareToSave = function() {
    let orarend = [];
//        var tanorak = [];
    
    let currentDate = new Date(this.startingDate.valueOf());
    while(currentDate <= this.endDate)
    {
        let dateId = this.getDateId(currentDate);
        if (this.orarend[dateId] == undefined || !this.orarend[dateId].van_tanitas)
        {
            currentDate.setDate(currentDate.getDate() + 1);
            continue;
        }
        
        for(let i = 0; i < this.osztalyok.length; i++) 
        {
            let oid = 'oid' + this.osztalyok[i].id;
            for(let k = 0; k < this.napioraszam; k++) {
                if (this.orarend[dateId][oid][k].data('lessonid') > -1)
                {
                    orarend.push([dateId, k, this.orarend[dateId][oid][k].data('lessonid')]);
                }
            }
        }
        
        currentDate.setDate(currentDate.getDate() + 1);
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
