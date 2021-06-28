<?php

$slim->get('/', \App\Controller\PublicController::class . ':index')->setName('public.index');
$slim->post('/login', \App\Controller\PublicController::class . ':processLogin')->setName('public.processLogin');

$slim->group('/main', function() use ($slim) {
    
    $slim->get('/index', \App\Controller\MainController::class . ':index')->setName('main.index');
    $slim->get('/maintenance', \App\Controller\MainController::class . ':maintenance')->setName('main.maintenance');
    $slim->post('/cleartt', \App\Controller\MainController::class . ':cleartt')->setName('main.cleartt');
    //$slim->post('/saveexcercise', \App\Controller\MainController::class . ':saveExcercise')->setName('main.saveexcercise');
    $slim->any('/general', \App\Controller\MainController::class . ':general')->setName('main.general');
    $slim->any('/renameseason', \App\Controller\MainController::class . ':renameSeason')->setName('main.renameSeason');
    $slim->post('/import2season', \App\Controller\MainController::class . ':importToSeason')->setName('main.importToSeason');
    $slim->get('/edit_timetable', \App\Controller\MainController::class . ':edit_timetable')->setName('main.edit_timetable');
    $slim->post('/save_timetable', \App\Controller\MainController::class . ':save_timetable')->setName('main.save_timetable');    
    $slim->post('/change_settings', \App\Controller\MainController::class . ':changeSettings')->setName('main.change_settngs');    
    $slim->get('/logout', \App\Controller\MainController::class . ':logout')->setName('main.logout');

});//->add( new App\AuthMiddleware($slim) );

$slim->group('/seasons', function() use ($slim) {
    $slim->get('/index', \App\Controller\SeasonController::class . ':index')->setName('seasons.index');
    $slim->get('/delete/{id}', \App\Controller\SeasonController::class . ':delete')->setName('seasons.delete');
    $slim->any('/create', \App\Controller\SeasonController::class . ':create')->setName('seasons.create');
    $slim->post('/clone', \App\Controller\SeasonController::class . ':cloneSeason')->setName('seasons.cloneSeason');
    $slim->get('/activate/{id}', \App\Controller\SeasonController::class . ':activate')->setName('seasons.activate');
});

$slim->group('/lesson', function() use ($slim) {
    $slim->get('/index', \App\Controller\LessonController::class . ':index')->setName('lesson.index');
    $slim->any('/edit[/{id}]', \App\Controller\LessonController::class . ':edit')->setName('lesson.edit');
    $slim->any('/showClass[/{classid}]', \App\Controller\LessonController::class . ':showClass')->setName('lesson.showClass');
    $slim->any('/showTeacher[/{teacherid}]', \App\Controller\LessonController::class . ':showTeacher')->setName('lesson.showTeacher');
    $slim->get('/delete/{id}', \App\Controller\LessonController::class . ':delete')->setName('lesson.delete');
    $slim->any('/addToClass[/{id}]', \App\Controller\LessonController::class . ':addToClass')->setName('lesson.addToClass');
});
    
$slim->group('/class', function() use ($slim) {
    $slim->get('/index', App\Controller\ClassController::class . ':index')->setName('class.index');
    $slim->any('/edit[/{id}]', App\Controller\ClassController::class . ':edit')->setName('class.edit');
    $slim->post('/delete', App\Controller\ClassController::class . ':delete')->setName('class.delete');
});

$slim->group('/subject', function() use ($slim) {
    $slim->get('/index', App\Controller\SubjectController::class . ':index')->setName('subject.index');    
    $slim->any('/edit[/{id}]', App\Controller\SubjectController::class . ':edit')->setName('subject.edit');    
    $slim->post('/delete', App\Controller\SubjectController::class . ':delete')->setName('subject.delete');    
});

$slim->group('/teacher', function() use ($slim) {
    $slim->get('/index', App\Controller\TeacherController::class . ':index')->setName('teacher.index');    
    $slim->any('/edit[/{id}]', App\Controller\TeacherController::class . ':edit')->setName('teacher.edit');    
    $slim->post('/delete', App\Controller\TeacherController::class . ':delete')->setName('teacher.delete');    
});

$slim->group('/export', function() use ($slim) {
    $slim->get('/index', \App\Controller\ExportController::class . ':index')->setName('export.index'); 
    $slim->post('/toKreta', \App\Controller\ExportController::class . ':toKreta')->setName('export.toKreta'); 
    $slim->get('/toPrint/{type}', \App\Controller\ExportController::class . ':toPrint')->setName('export.toPrint');    
    $slim->get('/webview', \App\Controller\ExportController::class . ':webview')->setName('export.webview');    
});

$slim->group('/api', function() use ($slim) {
    $slim->get('/class', \App\Controller\ApiController::class . ':class')->setName('api.class'); 
    $slim->get('/lesson', \App\Controller\ApiController::class . ':lesson')->setName('api.lesson'); 
    $slim->get('/teacher', \App\Controller\ApiController::class . ':teacher')->setName('api.teacher'); 
    $slim->get('/tt', \App\Controller\ApiController::class . ':tt')->setName('api.tt');    
    $slim->get('/basedata', \App\Controller\ApiController::class . ':basedata')->setName('api.basedata');    
});
