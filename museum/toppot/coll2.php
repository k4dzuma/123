<?php
require 'db_connection.php';
try {
	// Выполняем запрос
    $query = $db->query("SELECT * FROM col_obyvn");
    $info = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Ошибка подключения к базе данных: " . $e->getMessage();
    $info = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История обувного техникума</title>   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">     
    <link rel="stylesheet" href="style.css">   
</head>
<body class = "razdel"> 
    <div class="header">   
        <a href="index.php"><img src="logotip.png" alt="logo" class="logo"><a>  
        <div class="navigation">  
            <a href="index.php">Главная</a>  
            <a href="razdel.php">Разделы</a>  
        </div>  
    </div>   

    <div class="container mt-5"> 
        <div class="row"> 
            <div class="col-md-8"> 
                <div id="mainImage" class="carousel slide" data-bs-ride="carousel"> 
                    <div class="carousel-inner"> 
                        <?php foreach ($info as $index => $data): ?> 
                            <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>"> 
                                <img src="<?= $data['Img']; ?>" class="d-block w-100" alt="..."> 
                            </div> 
                        <?php endforeach; ?> 
                    </div> 
                    <button class="carousel-control-prev" type="button" data-bs-target="#mainImage" data-bs-slide="prev"> 
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span> 
                        <span class="visually-hidden">Предыдущий</span> 
                    </button> 
                    <button class="carousel-control-next" type="button" data-bs-target="#mainImage" data-bs-slide="next"> 
                        <span class="carousel-control-next-icon" aria-hidden="true"></span> 
                        <span class="visually-hidden">Следующий</span> 
                    </button> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div id="thumbnails" class="list-group"> 
                    <?php foreach ($info as $index => $data): ?> 
                        <a href="#" class="list-group-item list-group-item-action" data-bs-target="#mainImage" data-bs-slide-to="<?= $index; ?>" data-image-src="<?= $data['Img']; ?>"> 
                            <img src="<?= $data['Img']; ?>" class="img-thumbnail" alt="..."> 
                        </a> 
                    <?php endforeach; ?> 
                </div> 
            </div> 
        </div> 
    </div> 
    <div class="modal fade" id="fullscreenCarousel" tabindex="-1" aria-labelledby="fullscreenCarouselLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- Adjust size here -->
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> <div class="modal-body">
                    <div id="fullScreenCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($info as $index => $data): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?= $data['Img']; ?>" class="d-block w-100" alt="...">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#fullScreenCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#fullScreenCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>  
<script src="script.js"></script>  
</body> 
</html>
