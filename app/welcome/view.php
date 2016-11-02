<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="<?php echo $this->resUrl; ?>css/bootstrap.min.css" rel="stylesheet">
    
    <title>tiny mvc sandbox</title>
</head>

<body>
    <div class="container">
        <div class="jumbotron">
            <?php echo $text; ?>
        </div>
        
        <?php new controller('welcome', 'second'); ?>
        
    </div>    
    
     <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo $this->resUrl; ?>js/bootstrap.min.js"></script>
</body>