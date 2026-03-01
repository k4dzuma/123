# 🏛️ Виртуальный музей "Человек и Время" - Next.js версия

Современный SPA (Single Page Application) виртуального музея, построенный на Next.js 16, TypeScript, Tailwind CSS и Prisma ORM.

## 🚀 Технологии

### Frontend
- **Next.js 16** - React framework с App Router
- **TypeScript** - Типизация для надежности кода
- **Tailwind CSS** - Утилитарный CSS фреймворк
- **Framer Motion** - Плавные анимации
- **shadcn/ui** - Современные UI компоненты на Radix UI
- **Lucide React** - Иконки

### Backend
- **Prisma ORM** - Типобезопасная работа с базой данных
- **SQLite** - Локальная база данных

## 🏃 Запуск

```bash
# Режим разработки
npm run dev

# Продакшн сборка
npm run build

# Запуск продакшн версии
npm start
```

Откройте [http://localhost:3000](http://localhost:3000) в браузере.

## 📁 Структура проекта

```
museum-nextjs/
├── prisma/              # Схема базы данных и конфигурация
│   ├── schema.prisma    # Модели базы данных
│   └── config.ts        # Конфигурация Prisma Client
├── src/
│   ├── app/            # App Router (Next.js 14)
│   ├── components/      # React компоненты
│   ├── lib/            # Утилиты и функции
│   └── types/          # TypeScript типы
└── public/             # Статические файлы
```

## 🎨 Дизайн

### Цветовая палитра

| Цвет | Hex | Использование |
|------|-----|-------------|
| Primary (Purple) | `#8B00FF` | Основной акцент |
| Accent (Cyan) | `#00D4FF` | Информационные элементы |
| Background | `#0F0F1A` | Основной фон |
| Card | `#16162A` | Фон карточек |

### Градиенты
- **Purple**: `linear-gradient(135deg, #8B00FF, #7C4DFF)`
- **Cyan**: `linear-gradient(135deg, #00D4FF, #0099CC)`
- **Dark**: `linear-gradient(135deg, #191919, #423189)`

## 🎯 Функционал

### Реализовано
- ✅ Главная страница с современным дизайном
- ✅ Навигация и футер
- ✅ Компоненты UI (Button, Card, Avatar)
- ✅ Темная тема с градиентами
- ✅ Framer Motion анимации
- ✅ Адаптивный дизайн
- ✅ TypeScript типизация
- ✅ Prisma ORM и база данных

## 📝 Скрипты

```bash
npm run dev          # Запуск dev сервера
npm run build        # Продакшн сборка
npm run start        # Запуск продакшн версии
npm run lint        # ESLint проверка
npx prisma db push  # Применение изменений в базе
npx prisma studio    # GUI для базы данных
```

## 🤝 Вклад в проект

1. Fork репозитория
2. Создайте feature branch
3. Commit изменения
4. Push в branch
5. Откройте Pull Request

## 📄 Лицензия

MIT License
