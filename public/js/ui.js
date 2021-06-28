let UI = {}

UI.SaveStatus = class {
    constructor($element) {
        this.$element = $element;
        this.isSaved();
    }
    needToSave() {
        this.$element.css("color", "#000000");        
    }
    isSaved() {
        this.$element.css("color", "#ffffff");
    }
}

UI.LessonBar = class {
    constructor($element, lessons) {
        this.$element = $element;
        this.items = [];
        this.lookupByLesson = {};
        let $row = $(document.createElement("tr"));
        for(let i = 0; i < lessons.length; i++) {
            let item = new UI.LessonBarItem($(document.createElement("td")), lessons[i]);
            item.updateUI();
            item.addHoverListener(
                () => Orarend.lessonDetailController.show(item.lesson),
                () => Orarend.lessonDetailController.hide()
            );
            item.addClickListener(() => Orarend.selectLessonFromBar(item));

            this.items.push(item);
            this.lookupByLesson[item.lesson.id] = item;

            $row.append(item.$element);
        }
        this.$element.append($row);
    }
    filter(classid, teacherid) {
        let visible = function(id, selectedid) {
            if (selectedid == -1) return true;
            return (selectedid == id);
        };
        for(let i = 0; i < this.items.length; i++) {
            if (visible(this.items[i].lesson.osztalyId, classid) && visible(this.items[i].lesson.tanarId, teacherid)) {
                this.items[i].$element.show();
            }
            else {
                this.items[i].$element.hide();
            }
        }
    }
    highlight(tanarId, color) {
        for(let i = 0; i < this.items.length; i++) {
            if (this.items[i].lesson.tanarId == tanarId)
                this.items[i].$element.css("background-color", color);
        }        
    }
    update(lesson) {
        this.lookupByLesson[lesson.id].updateUI();
    }
}

UI.LessonBarItem = class {
    constructor($element, lesson, specMarker) {
        this.$element = $element;
        this.lesson = lesson;
        this.specMarker = specMarker;
    }
    updateUI() {
        let tanarnev = this.lesson.tanar;
        let s = tanarnev + "<br/>" + this.lesson.osztalynev + ", " + this.lesson.oraszam;
        if (this.lesson.practice) s = "*" + s;
        this.$element.html(s);
        if (this.lesson.oraszam < 1) this.$element.addClass("noMoreLesson")
        else this.$element.removeClass("noMoreLesson");
    }
    addHoverListener(mouseIn, mouseOut) {
        this.$element.hover(mouseIn, mouseOut);
    }
    addClickListener(listener) {
        this.$element.click(listener);
    }
}

//  a ramutatott lesson adatai (ez lehet a lessonbar-on vagy az orarendben)
UI.LessonDetailController = class  {
    constructor($element) {
        this.$element = $element;
    }
    show(lesson) {
        //console.log("LessondetailController: " + lesson);
        if (lesson === null) this.$element.html("&nbsp;");
        else {
            let gyak = (lesson.practice == 1 ? "Gyakorlat | ": "");
            let s = `#${lesson.id} ${lesson.tanarteljes} - ${lesson.osztalynev}<br/>` +
                `${gyak}${lesson.tantargy}<br/>` + 
                `Összes óra: ${lesson.osszoraszam} / elhelyezésre vár: ${lesson.oraszam}`;
            this.$element.html(s);
        }
    }
    hide() {
        this.$element.html("&nbsp;");      
    }
}

UI.WeeklyLessonDisplay = class {
    constructor($element, weeklyLessonCounter) {
        this.$element = $element;
        this.weeklyLessonCounter = weeklyLessonCounter;
    }
    update_del(osztalyId, newValue) {
        this.$element.html("Heti oraszám<br/>" + newValue);
    }
    //  param: lessontableCell
    show(cell) {
        let n = 0;
        if (cell.osztalyId !== undefined) {
            n = this.weeklyLessonCounter.get(cell.osztalyId, cell.week);
        }
        this.$element.html("Heti oraszám<br/>" + n);
    }
    hide() {
        this.$element.html("Heti oraszám");        
    }
}

UI.SelectedLessonDetailController = class  {
    constructor($element) {
        this.$element = $element;
    }
    show(lesson) {
        if (lesson === null) {
            this.$element.html("Kiválasztott tanóra adatai");
        }
        else {
            let gyak = (lesson.practice == 1 ? "Gyakorlat | ": "");
            let s = `#${lesson.id} ${lesson.tanarteljes} - ${lesson.osztalynev}<br/>` +
                `${gyak}${lesson.tantargy}<br/>` + 
                `Összes óra: ${lesson.osszoraszam} / elhelyezésre vár: ${lesson.oraszam}`;
            this.$element.html(s);            
        }
    }
}

//  a tanorak drag and drop slotja
UI.MouseSlotController = class {
    constructor($element) {
        this.$element = $element;
        this.visible = false;
    }
    show(lesson, event = null) {
        this.$element.html(lesson.tanar + "<br/>" + lesson.oraszam);
        this.$element.toggle(true);
        this.visible = true;
        if (event !== null) this.mouseMove(event);
    }
    hide() {
        this.$element.toggle(false);
        this.visible = false;
    }
    mouseMove(event) {
        if (this.visible) this.$element.css({left: event.pageX + 10, top: event.pageY});
    }
}

UI.TimetableHeader = class {
    constructor($element, napioraszam, kezdooraszam) {
        this.$element = $element;
        this.$lessontimeRow = null;
        this.napok = ["Hetfő", "Kedd", "Szerda", "Csütörtök", "Péntek"];
        this._createDayRow(napioraszam);
        this._createLessontimeRow(napioraszam, kezdooraszam);
    }
    _createDayRow(napioraszam) {
        let $row = $(document.createElement("tr"));
        let $cell = $(document.createElement("th"));
    
        //  osztalyok oszlopa (ures)
        $cell.html(" ");
        $row.append($cell);
        
        //  napok neve
        for(let i = 0; i < this.napok.length; i++) {
            let $cell = $(document.createElement("th"));
            $cell.attr("colspan", napioraszam);
            $cell.html(this.napok[i]);
            if (i < this.napok.length-1) $cell.addClass("daySeparator");            
            $row.append($cell);
        }
        this.$element.append($row);
    }
    _createLessontimeRow(napioraszam, kezdooraszam) {
        let $row = $(document.createElement("tr"));
        let $cell = $(document.createElement("th"));
    
        //  osztalyok oszlop (ures)
        $cell.html(" ");
        $row.append($cell);
    
        //  oraszamok naponta oszlop
        for(let k = 0; k < this.napok.length; k++) {
            let $cell;
            for(let i = 0; i < napioraszam; i++) {
                $cell = $(document.createElement("th"));
                $cell.html( (kezdooraszam + i) );
                $row.append($cell);
            }
            if (k < 4) $cell.addClass("daySeparator");
        }
        this.$element.append($row);
        this.$lessontimeRow = $row;
    }
    markLessontime(elemIndex, cssClass) {
        this.$lessontimeRow.children().eq(elemIndex).addClass(cssClass);
    }
    clearLessontimeMark(elemIndex, cssClass) {
        this.$lessontimeRow.children().eq(elemIndex).removeClass(cssClass);
    }
}

//  datumokat tartalmazo cella (minden nap tetejen)
UI.TimetableDateCell = class {
    constructor($element, dateId, week, day, lessontime) {
        this.$element = $element;
        this.dateId = dateId;
        this.week = week;
        this.day = day;
        this.lessontime = lessontime;

        this.$element.addClass("dateHeader");
    }
    setContent(value) {
        this.$element.html(value);
    }
    addCssClass(className) {
        this.$element.addClass(className);
    }
    removeCssClass(className) {
       this.$element.removeClass(className); 
    }
}

//  sorok elejen a fejlecek (osztalynevek)
UI.RowHeadController = class {
    constructor(weeklyLessonCounter, maxLessonPerWeek) {
        this.$cells = [];
        this.cellsByClass = {};
        this.maxLessonPerWeek = maxLessonPerWeek;
        this.weeklyLessonCounter = weeklyLessonCounter;
    }
    create(osztaly, week, isLast) {
        let $element = $(document.createElement('td'));
        $element.html('<span>' + osztaly.name + '</span><span class="classLessonWeeklyCountInvalid"></span>');
        $element.data("osztalyId", osztaly.id);
        $element.data("week", week);
        
        if (this.cellsByClass[osztaly.id] == undefined) this.cellsByClass[osztaly.id] = [];
        this.cellsByClass[osztaly.id].push($element);
        this.$cells.push($element);

        if (isLast) $element.addClass('weekSeparator');

        return $element;
    }
    getRowHeads(osztalyId) {
        return this.cellsByClass[osztalyId];
    }
    //  a heti oraszam ellenorzese
    updateOverflowStatus() {
        for(let i = 0; i < this.$cells.length; i++) {
            if (this.weeklyLessonCounter.get(this.$cells[i].data('osztalyId'), this.$cells[i].data('week')) > this.maxLessonPerWeek) {
                this.$cells[i].children().eq(1).html('#');
            }
            else {
                this.$cells[i].children().eq(1).html("");
            }
        }
    }
    //  az osztalyt es tanorakat
    highlightClass(osztalyId) {
        for(let i = 0; i < this.cellsByClass[osztalyId].length; i++) {
            this.cellsByClass[osztalyId][i].find('span').addClass("classSelection");
        }
    }
    clearHighlight(osztalyId) {
        for(let i = 0; i < this.cellsByClass[osztalyId].length; i++) {
            this.cellsByClass[osztalyId][i].find('span').removeClass("classSelection");
        }
    }
    //  a heten tobb oraja van, mint a megengedett
    showLessonOverflow_del(osztalyId, week) {
        this.cellsByClass[osztalyId][week].children().eq(1).html('#');
    }
    clearLessonOverflow_del(osztalyId, week) {
        this.cellsByClass[osztalyId][week].children().eq(1).html("");
    }
}

//  az orarend egy olyan cellaja, ami nem tanorat tartalmaz pl: datum, osztaly
UI.TimetableCell = class {
    constructor($element) {
        this.$element = $element;
    }
    setContent(value) {
        this.$element.html(value);
    }
    addCssClass(className) {
        this.$element.addClass(className);
    }
}

UI.TimetableClassCell = class {
    constructor($element) {
        this.$element = $element;
        this.classId = -1;
        this.className = "";
    }
    //  az osztaly adatai, amit tarol
    setClassData(classId, className){
        this.classId = classId;
        this.className = className;
        this.setContent('<span>' + className + '</span><span class="classLessonWeeklyCountInvalid"></span>');
    }
    setContent(value) {
        this.$element.html(value);
    }
    addCssClass(className) {
        this.$element.addClass(className);
    }
}

//  az orarend egy olyan cellaja, ami tanorat tartalmazhat
UI.TimetableLessonCell = class {
    constructor($element, classIndex, dateId, osztalyId, lessontime, week) {
        this.$element = $element;
        this.classIndex = classIndex;
        this.dateId = dateId;
        this.osztalyId = osztalyId;
        this.lessontime = lessontime;
        this.week = week;

        this.lesson = null;
        
        this.setContent(" ");
    }
    addHoverListener(mouseIn, mouseOut) {
        this.$element.hover(mouseIn, mouseOut);
    }
    addClickListener(listener) {
        this.$element.mousedown(listener);
    }
    setContent(content) {
        this.$element.html(content + "<span></span>");
        //this.$element.html(content);
    }
    addCssClass(className) {
        this.$element.addClass(className);
    }
    highlightTeacher(color) {
        this.$element.children().first().css("background-color", color);
    }
    getHighlightColor() {
        return this.$element.children().first().css("background-color");
    }
    highlightLesson(color) {
        this.$element.children().eq(1).html("❰❰");
        //this.$element.children().eq(1).css("background-color", "#ffffff");
        this.$element.children().eq(1).css("color", color);
        
        //this.$element.children().first().css("background-color", color);
        //this.$element.children().first().css("text-decoration", "line-through");
    }
    clearLessonHighlight() {
        
        this.$element.children().eq(1).html("");
        this.$element.children().eq(1).css("background-color", "");
        
        //this.$element.children().first().css("background-color", "");
        //this.$element.children().first().css("text-decoration", "none");
    }
    setLesson(lesson) {
        if (lesson === null) {
            this.lesson = null;
            this.setContent(" ");
        }
        else {
            this.lesson = lesson;
            let s = '<span>' + lesson.tanar + "</span>";
            this.setContent(s);
        }
    }
}