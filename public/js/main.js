document.oncontextmenu = function() {return false;};

let App = {
    $loadingContent: null,
    $lastSaveDiv: null
}

App.fetchData = async function(url) {
    try {
        const data = await fetch(url);
        const dataJson = await data.json();

        if (dataJson) {
            return await {data: dataJson, error: false};
        }
    }
    catch(error) {
        return await {data: false, error: error.message};
    }
}

App.showSaveResult = function(data) {
    console.log(data);
    let $div = $("#savingDetails");
    $div.removeClass("bg-danger text-white");
    if (data.error) {
        $div.html(data.error);
        $div.addClass("bg-danger text-white");
    }
    else if (data.msg) {
        let len = data.errors.length;
        $div.html("Mentés: " + data.msg + ", Mentés ideje: " + data.time.toFixed(3) + " sec<br/>" +
            "Kapott adat: " + data.total + " / Mentett adat: " + data.saved + "<br/>Hibák száma: " + len);
        Orarend.utolsoMentes = data.msg;
        this.$lastSaveDiv.html("Mentve: " + data.msg);
    }
    else {
        $div.html("Sikertelen mentés! Szerver hiba!");
        $div.addClass("bg-danger text-white");
    }
}

App.load = async function() {
    for(let i = 0; i < Orarend.requiredData.length; i++) {
        this.$loadingContent.html("Adatok betöltése...<br/>" + Orarend.requireDataTitle[i]);
        let result = await App.fetchData(location.origin + '/api/' + Orarend.requiredData[i]);
        if (result.data) {
            Orarend.load(Orarend.requiredData[i], result.data);
        }
        else {
            this.$loadingContent.html("Sikertelen betöltés: " + Orarend.requireDataTitle[i] + "<br/>Error: " + result.error);
            console.log("Sikertelen betoltes: " + Orarend.requiredDataTitle[i] + " error: " + result.error);
        }
    }
}

App.init = async function() {
    this.$loadingContent = $("#loadingDiv div");
    await this.load();
    await this.initOrarend();
    this.initUI();
}

App.initOrarend = async function() {
    this.$loadingContent.html("Órarend táblázat generálása...");
    await Orarend.init();

    this.$lastSaveDiv = $("#lastSaveDiv");
    this.$lastSaveDiv.html("Mentve: " + Orarend.utolsoMentes);
}

App.initUI = async function() {
    this.$loadingContent.html("UI eventek...");

    const w = $("#timetableHeaderDiv").width() - $("#timetableContentDiv")[0]['clientWidth'];
    $("#timetableHeaderDiv").css('padding-right', w);

    $(document).mousemove(function(e) { Orarend.mouseSlotController.mouseMove(e); });

    $("#btnMarkTeacher").click(function() {
        let color = Orarend.highlightTeacher($("#teacherSelect").val(), $("#highlightColor").val());
        $("#highlightColor").val(color);
    });

    $("#btnMarkLesson").click(function() {
        Orarend.toggleLessonHighlight();
    });


    $("#classSelect").change(function() { 
        Orarend.lessonBar.filter($(this).val(), $("#teacherSelect").val());
    });
    $("#teacherSelect").change(function() {
        $("#highlightColor").val(Orarend.getHighlightColorOfTeacher($(this).val()));
        Orarend.lessonBar.filter($("#classSelect").val(), $(this).val());
    });

    $("#btnSave").click(function() {
        $("#savingDiv").show();
        $("#afterSavingDiv").hide();
        
        let result = Orarend.prepareToSave();
        //console.log(result);
        
        let request = $.ajax({
            url: location.origin + '/main/save_timetable',
            method: 'post',
            timeout: 7000,
            data: {orarend: JSON.stringify(result.orarend)}
            // data: {tt: JSON.stringify(result)}
        });
        // Callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            Orarend.saveStatus.isSaved();
            App.showSaveResult(response);
        }); 
        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            App.showSaveResult({'error': 'Sikertelen mentes!', 'textStatus': textStatus, 'errorThrown': errorThrown});
        });
        // Callback handler that will be called regardless if the request failed or succeeded
        request.always(function () {
            $("#savingDiv").hide();
        });
        
    });
    /*
    $("#btnOk").click(function() {
        $("#savingDiv").hide();
    });
    */

    $("#loadingDiv").hide();
}

$(document).ready(function() {
    App.init();
});