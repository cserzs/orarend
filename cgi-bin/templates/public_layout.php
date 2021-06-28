<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>Orarend v2</title>
</head>
<body>
<?php if ($msg = $slim->flash->getFirstMessage('system_message', false)) : ?>
<div id="modalLayer">
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <?php echo $msg; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
</div>
<?php endif; ?>

<?php echo $_page; ?>    
</body>
</html>