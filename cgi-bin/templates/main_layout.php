<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $_pagetitle; ?></title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/stilusok.css">
</head>
<body>
  
<script src="/js/jquery-3.3.1.min.js"></script>

<div class="container-fluid">
    <div class="row">
        <nav class="col-2 bg-light sidebar">
            <h2 class="text-center">Órarend</h2>
            <p class="small text-center"><b><?=$season_name;?></b><br/>
                <?=$season_date;?>
            </p>
            <p>
                <ul class="nav flex-column">
                    <li class="text-center"><a href="/main/index">Kezdőlap</a></li>
                    <li class="text-center"><a href="/seasons/index">Időszakok</a></li>
                    <li class="text-center"><a href="/main/maintenance">Karbantartás</a></li>
                    <li class="text-center"><a href="/main/general">Aktuális időszak</a></li>
                    <li class="text-center"><a href="/subject/index">Tantárgyak</a></li>
                    <li class="text-center"><a href="/teacher/index">Tanárok</a></li>
                    <li class="text-center"><a href="/class/index">Osztályok</a></li>
                    <li class="text-center"><a href="/lesson/index">Tanórák</a></li>
                    <li class="text-center"><a href="/export/index">Export</a></li>
                    <li class="text-center"><a href="/main/edit_timetable">Órarend szerkesztés</a></li>
                </ul>
            </p>
            <p class="text-center">
                <a href="/main/logout">Kijelentkezés</a>
            </p>
        </nav>
        <main class="col-10">
            <?php if ($msg = $slim->flash->getFirstMessage('system_message', false)) : ?>
                <div id="alertDiv" class="alert alert-warning alert-dismissible fade show" role="alert">
                  <?php echo $msg; ?>
                  <button id="btnAlertClose" type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
        
            <?php echo $_page; ?>            
        </main>
    </div>
</div>
        
   
<script type="text/javascript">
$("#btnAlertClose").click(function() {
    $("#alertDiv").hide();
});
</script>
</body>
</html>