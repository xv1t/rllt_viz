<html>
    <head>
        <title><?= $file_name ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!--
        <link rel="stylesheet" type="text/css" href="../libs/font-awesome/css/font-awesome.min.css" />
        
        <script src="../libs/jquery-1.11.3.min.js"></script>
        <script src="../libs/underscore-min.js"></script>
        <script src="../libs/bootstrap/js/bootstrap.min.js"></script>
        -->
    </head>
    <?= $this->element('style') ?>
    <body>
        <div class="container-fluid">
            <?= $this->element('content') ?>
        </div>
    </body>
    <script>
    setTimeout(function(){
        location.reload(false)
    }, 10000)
    </script>
</html>
