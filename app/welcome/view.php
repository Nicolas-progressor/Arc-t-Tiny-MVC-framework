<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="<?php echo $this->resUrl; ?>css/bootstrap.min.css" rel="stylesheet">
    
    <title>Arc-t Tiny MVC Framework</title>
</head>

<body>
    <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="index">          
                        <div class="brand_name">Arc-t Tiny MVC Framework</div>
                    </a>   
                </div>                
            </div>            
    </nav>
    <div class="container">      
        <div class="jumbotron">
            <p>
                <?php echo $text; ?>
            </p>            
        </div>
        
        <?php new controller('welcome', 'second'); ?>
        
        <?php new controller('second', 'index'); ?>
        
    </div>    
    
     <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo $this->resUrl; ?>js/bootstrap.min.js"></script>
</body>