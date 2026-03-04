<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}
$current_theme = $_COOKIE['admin_theme'] ?? 'light';
$table = $_GET['table'] ?? '';
$allowed_tables = [
    'Section', 'Eksponat',
    'col_text', 'col_obyvn', 'col_leg_promish', 'col_py14', 'col_istorya',
    'sport_izvest', 'sport_str_istor', 'sport_yspex',
    'voina_istor', 'voina_sotrudniki', 'voina_SVO',
    'teatr_kollekz', 'teatr_nashi_vipusk',
    'stroit_akt', 'stroit_foto', 'stroit_liter',
    'znamenitosti',
    'meropriyatiya', 'vystavki',
    'studsovet_dostijenya', 'studsovet_lideri'
];

if (!in_array($table, $allowed_tables)) {
    die("Недопустимая таблица");
}

// Получаем имя первичного ключа (SQLite)
try {
    $stmt = $db->query("PRAGMA table_info($table)");
    $columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $primaryKey = 'id';
    foreach ($columns_info as $col) {
        if ($col['pk'] == 1) {
            $primaryKey = $col['name'];
            break;
        }
    }
} catch (PDOException $e) {
    $primaryKey = 'id';
}

// Обработка данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    
    // Удаление записи
    if (isset($_POST['delete'])) {
        try {
            $stmt = $db->prepare("DELETE FROM $table WHERE $primaryKey = ?");
            $stmt->execute([$_POST[$primaryKey]]);
            $_SESSION['success'] = "Запись удалена";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка удаления: " . $e->getMessage();
        }
        header("Location: admin_edit.php?table=$table");
        exit();
    }

    // Загрузка изображения
    $newImage = null;
    if (!empty($_FILES['Img']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['Img']['type'];
        
        if (in_array($fileType, $allowed)) {
            $fileName = uniqid() . '_' . basename($_FILES['Img']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['Img']['tmp_name'], $targetPath)) {
                $newImage = $targetPath;
            }
        } else {
            $_SESSION['error'] = "Допустимы только JPG, PNG и WEBP";
        }
    }

    // Подготовка данных
    $fields = [];
    $values = [];
    foreach ($_POST as $key => $value) {
        if ($key !== $primaryKey && $key !== 'Img' && $key !== 'delete') {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
    }

    if ($newImage) {
        $fields[] = "Img = ?";
        $values[] = $newImage;
    }

    if (isset($_POST[$primaryKey])) {
        $values[] = $_POST[$primaryKey];
        $query = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $primaryKey = ?";
        $message = "Запись обновлена";
    } else {
        $query = "INSERT INTO $table SET " . implode(', ', $fields);
        $message = "Запись добавлена";
    }

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($values);
        $_SESSION['success'] = $message;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка: " . $e->getMessage();
    }
    
    header("Location: admin_edit.php?table=$table");
    exit();
}

// Получение данных таблицы
try {
    $stmt = $db->query("SELECT * FROM $table LIMIT 1");
    $columns = $stmt->fetch(PDO::FETCH_ASSOC);
    $data = $db->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление <?= $table ?> | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 300px;
            --topbar-height: 70px;
            --primary-color: #4e73df;
            --light-color: #f8f9fc;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
        }
        
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: var(--topbar-height);
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 100;
            transition: all 0.3s ease;
            color: white;
        }
        
        #topbar {
            height: var(--topbar-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 110;
            padding: 0 20px;
        }
        
        #content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 30px;
            transition: all 0.3s ease;
            min-height: calc(100vh - var(--topbar-height));
            background-color: var(--light-color);
        }
        
        .sidebar-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            margin: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }
        
        .sidebar-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .image-preview { 
            width: 150px; 
            height: 150px; 
            border: 2px dashed #ccc; 
            background-size: cover; 
            background-position: center;
            border-radius: 8px;
        }
        
        .current-image { 
            max-width: 200px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .table th { 
            white-space: nowrap; 
            background-color: #f8f9fa;
        }
        
        .form-control:focus { 
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25); 
            border-color: rgba(78, 115, 223, 0.5);
        }
        
        .card-header { 
            font-weight: 600; 
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .table-responsive { 
            overflow-x: auto; 
        }
        
        textarea.form-control { 
            min-height: 100px; 
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <button class="btn btn-link d-md-none" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <a class="navbar-brand" href="admin_panel.php">
                <i class="bi bi-arrow-left me-2"></i>Назад
            </a>
            
            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
                       id="userDropdown" data-bs-toggle="dropdown">
                        <div class="me-2 d-none d-lg-inline text-end">
                            <span class="fw-bold"><?= htmlspecialchars($_SESSION['login']) ?></span>
                            <small class="d-block text-muted">Администратор</small>
                        </div>
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePinModal">
                            <i class="bi bi-shield-lock me-2"></i>Сменить PIN</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Выйти</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar">
        <div class="px-3 py-4">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Логотип" style="height: 50px;">
                <div class="mt-2 fw-bold">Админ-панель</div>
            </div>
            
            <ul class="list-unstyled">
                <li>
                    <a href="admin_panel.php" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>Главная
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Управление</div>
                
                <li>
                    <a href="admin_users.php" class="sidebar-link">
                        <i class="bi bi-people"></i>Пользователи
                    </a>
                </li>
                <li>
                    <a href="admin_content.php" class="sidebar-link">
                        <i class="bi bi-collection"></i>Контент
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Система</div>
                
                <li>
                    <a href="admin_backup.php" class="sidebar-link">
                        <i class="bi bi-database"></i>Резервные копии
                    </a>
                </li>
                <li>
                    <a href="admin_logs.php" class="sidebar-link">
                        <i class="bi bi-journal-text"></i>Логи
                    </a>
                </li>
              
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div id="content">
        <div class="container py-4">
            <!-- Сообщения об ошибках и успехе -->
            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <!-- Заголовок и кнопки -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-gear me-2"></i> Управление: <?= $table ?></h2>
                <div>
                    <a href="admin_panel.php" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-left me-1"></i> На главную
                    </a>
                    <a href="admin_edit.php?table=<?= $table ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>

            <!-- Форма добавления -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-plus-circle me-2"></i> Добавить запись
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <?php foreach ($columns as $col => $val): ?>
                            <?php if ($col === $primaryKey) continue; ?>
                            <div class="col-md-6">
                                <label class="form-label"><?= ucfirst($col) ?></label>
                                <?php if($col === 'Img'): ?>
                                    <div class="image-preview mb-3" id="imagePreview"></div>
                                    <input type="file" name="Img" class="form-control" 
                                           accept="image/*" onchange="previewImage(event)">
                                <?php elseif(strpos($col, 'opisanie') !== false || strpos($col, 'Text') !== false || strpos($col, 'Opisanie') !== false): ?>
                                    <textarea name="<?= $col ?>" class="form-control" rows="3" required></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?= $col ?>" class="form-control" required>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save me-1"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица данных -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-table me-2"></i> Редактирование записей
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach ($columns as $col => $val): ?>
                                        <th><?= $col ?></th>
                                    <?php endforeach; ?>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <form method="POST" enctype="multipart/form-data">
                                        <?php foreach ($row as $col => $val): ?>
                                            <td>
                                                <?php if($col === $primaryKey): ?>
                                                    <input type="hidden" name="<?= $primaryKey ?>" value="<?= $val ?>">
                                                    <?= $val ?>
                                                <?php elseif($col === 'Img'): ?>
                                                    <input type="file" name="Img" class="form-control" 
                                                           onchange="previewEditImage(event, this)">
                                                    <?php if($val): ?>
                                                        <img src="<?= $val ?>" 
                                                             class="current-image mt-2">
                                                    <?php endif; ?>
                                                <?php elseif(strpos($col, 'opisanie') !== false || strpos($col, 'Text') !== false || strpos($col, 'Opisanie') !== false): ?>
                                                    <textarea name="<?= $col ?>" class="form-control" rows="2"><?= htmlspecialchars($val) ?></textarea>
                                                <?php else: ?>
                                                    <input type="text" name="<?= $col ?>" 
                                                           value="<?= htmlspecialchars($val) ?>" 
                                                           class="form-control">
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-nowrap">
                                            <button type="submit" class="action-btn btn btn-success me-2">
                                                <i class="bi bi-save"></i>
                                            </button>
                                            <button type="submit" name="delete" 
                                                    class="action-btn btn btn-danger"
                                                    onclick="return confirm('Удалить запись?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Функции для превью изображений
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    preview.style.backgroundSize = 'cover';
                    preview.style.backgroundPosition = 'center';
                };
                reader.readAsDataURL(file);
            }
        }

        function previewEditImage(event, input) {
            const img = input.parentElement.querySelector('img');
            const file = event.target.files[0];
            if (file && img) {
                const reader = new FileReader();
                reader.onload = (e) => img.src = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>