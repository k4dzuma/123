const fs = require('fs');
const path = require('path');

const sourceDir = path.join(__dirname, '../../museum/toppot');
const targetDir = path.join(__dirname, '../public/images');

// Создаем целевые папки
const dirs = ['images', 'images/sections', 'images/exhibits', 'images/avatars'];

dirs.forEach(dir => {
  const targetPath = path.join(__dirname, dir);
  if (!fs.existsSync(targetPath)) {
    fs.mkdirSync(targetPath, { recursive: true });
    console.log(`Создана папка: ${targetPath}`);
  }
});

// Копируем все PNG файлы
const copyImages = () => {
  const files = fs.readdirSync(sourceDir).filter(file => 
    file.match(/\.(png|jpg|jpeg|gif|webp)$/i)
  );
  
  files.forEach(file => {
    const sourcePath = path.join(sourceDir, file);
    const targetPath = path.join(__dirname, '../public/images', file);
    
    try {
      fs.copyFileSync(sourcePath, targetPath);
      console.log(`Скопирован: ${file}`);
    } catch (err) {
      console.error(`Ошибка копирования ${file}:`, err);
    }
  });
};

// Копируем подпапки
const copySubfolders = () => {
  const subdirs = ['Разделы', 'Экспонаты', 'Содержимое_подразделов'];
  
  subdirs.forEach(subdir => {
    const sourcePath = path.join(sourceDir, subdir);
    const targetPath = path.join(__dirname, '../public/images', subdir);
    
    if (fs.existsSync(sourcePath)) {
      if (!fs.existsSync(targetPath)) {
        fs.mkdirSync(targetPath, { recursive: true });
      }
      
      const files = fs.readdirSync(sourcePath).filter(file => 
        file.match(/\.(png|jpg|jpeg|gif|webp)$/i)
      );
      
      files.forEach(file => {
        const sourceFile = path.join(sourcePath, file);
        const targetFile = path.join(targetPath, file);
        try {
          fs.copyFileSync(sourceFile, targetFile);
          console.log(`Скопирован: ${subdir}/${file}`);
        } catch (err) {
          console.error(`Ошибка копирования ${subdir}/${file}:`, err);
        }
      });
    } else {
      console.log(`Папка не существует: ${subdir}`);
    }
  });
};

copyImages();
copySubfolders();

console.log('Копирование завершено!');
