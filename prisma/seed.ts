import 'dotenv/config'
import { PrismaClient } from '@prisma/client'
import { PrismaBetterSqlite3 } from '@prisma/adapter-better-sqlite3'
import { hashSync } from 'bcryptjs'

const adapter = new PrismaBetterSqlite3({
    url: process.env.DATABASE_URL || 'file:./prisma/dev.db',
})
const prisma = new PrismaClient({ adapter })

async function main() {
    console.log('🌱 Seeding database...')

    // ==================== SETTINGS ====================
    const settings = [
        { key: 'site_name', value: 'Виртуальный музей "Человек и Время"' },
        { key: 'site_email', value: 'info@museum.edu' },
        { key: 'timezone', value: 'Europe/Moscow' },
        { key: 'records_per_page', value: '10' },
        { key: 'login_attempts', value: '5' },
        { key: 'session_lifetime', value: '120' },
        { key: 'maintenance_mode', value: '0' },
        { key: 'debug_mode', value: '0' },
    ]

    for (const s of settings) {
        await prisma.setting.upsert({
            where: { key: s.key },
            update: { value: s.value },
            create: s,
        })
    }

    // ==================== USERS ====================
    const adminPassword = hashSync('admin123', 10)
    const userPassword = hashSync('user123', 10)

    const admin = await prisma.user.upsert({
        where: { login: 'admin' },
        update: {},
        create: {
            login: 'admin',
            email: 'admin@museum.edu',
            password: adminPassword,
            role: 'ADMIN',
            avatar: '/images/avatars/avatar_8_1744554711.png',
            totalScore: 0,
        },
    })

    await prisma.adminSecurity.upsert({
        where: { userId: admin.id },
        update: {},
        create: {
            userId: admin.id,
            pin: hashSync('1234', 10),
        },
    })

    const users = [
        { login: 'kamisik', email: 'kamisik@gmail.com', avatar: '/images/avatars/avatar_9_1744541950.png', score: 1250 },
        { login: 'nolik', email: 'nolik@gmail.com', avatar: '/images/avatars/avatar_11_1744571320.jpg', score: 980 },
        { login: 'elena', email: 'elena@gmail.com', avatar: '/images/avatars/avatar_12_1744640721.jpg', score: 890 },
        { login: 'simka', email: 'simka@gmail.com', avatar: '/images/avatars/avatar_13_1744648329.jpg', score: 780 },
        { login: 'dedus', email: 'dedus@gmail.com', avatar: '/images/avatars/avatar_15_1744666884.jpg', score: 650 },
    ]

    for (const u of users) {
        await prisma.user.upsert({
            where: { login: u.login },
            update: {},
            create: {
                login: u.login,
                email: u.email,
                password: userPassword,
                avatar: u.avatar,
                totalScore: u.score,
            },
        })
    }

    // ==================== CELEBRITIES ====================
    await prisma.celebrity.deleteMany()
    await prisma.celebrity.createMany({
        data: [
            {
                name: 'Валентина Терешкова',
                fullName: 'Валентина Владимировна Терешкова',
                description: 'Первая женщина в мире, совершившая космический полет, который состоялся 16 июня 1963 года на корабле "Восток-6".',
                role: 'Космонавт',
                image: '/images/tereshkova.jpg',
                modalImage: '/images/tereshkova2.jpeg',
            },
            {
                name: 'Власов',
                fullName: 'Алексей Власов',
                description: 'Талантливый дизайнер из Ярославля, известный своими креативными и уникальными решениями в области дизайна.',
                role: 'Дизайнер',
                image: '/images/vlasov.jpg',
                modalImage: '/images/vlasov2.png',
            },
        ],
    })

    // ==================== SECTIONS ====================
    await prisma.section.deleteMany()

    const sectionsData = [
        { name: 'Исторические этапы развития учебного заведения', image: '/images/01.png', description: 'Развитие учебного заведения с 1930 года', sortOrder: 1 },
        { name: 'Спортивная жизнь колледжа', image: '/images/02.png', description: 'Достижения и победы нашего колледжа', sortOrder: 2 },
        { name: 'История Театра моды', image: '/images/03.png', description: 'Уникальные коллекции и показы', sortOrder: 3 },
        { name: 'История студенческого совета', image: '/images/04.png', description: 'Лидерство и достижения', sortOrder: 4 },
        { name: 'Страницы архива, опаленные войной', image: '/images/05.png', description: 'Страницы архива, опаленные войной', sortOrder: 5 },
        { name: 'Мероприятия и выставки музея', image: '/images/06.png', description: 'Выставки и события', sortOrder: 6 },
        { name: 'Строительство новых корпусов на Тутаевском шоссе', image: '/images/07.png', description: 'Новые корпуса и здания', sortOrder: 7 },
        { name: 'Знаменитые выпускники', image: '/images/08.png', description: 'Люди, прославившие колледж', sortOrder: 8 },
    ]

    const sections: { id: number }[] = []
    for (const s of sectionsData) {
        const section = await prisma.section.create({ data: s })
        sections.push(section)
    }

    // Sub-sections for "Исторические этапы" (section 1)
    const subSectionsMap: Record<number, { name: string; items: { title: string; image: string; text: string }[] }[]> = {
        0: [ // Исторические этапы
            {
                name: 'Первые страницы истории текстильного техникума', items: [
                    { title: 'История колледжа', image: '/images/01_Исторические_этапы_развития_учебного_заведения/01_01.png', text: 'История нашего колледжа начинается в 1930 году, когда были организованы фабрично-заводские курсы при фабрике «Красный перекоп».' },
                ]
            },
            {
                name: 'История обувного техникума', items: [
                    { title: 'История обувного техникума', image: '/images/02_История_обувного_техникума/01_02_02.png', text: 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года был открыт обувной техникум с целью подготовки техников-технологов.' },
                ]
            },
            {
                name: 'История техникума легкой промышленности', items: [
                    { title: 'История техникума легкой промышленности', image: '/images/03_История_техникума_легкой_промылшенности/01_03.jpg', text: 'Техникум лёгкой промышленности — важный этап в истории учебного заведения.' },
                ]
            },
            {
                name: 'История ПУ №14', items: [
                    { title: 'История ПУ №14', image: '/images/04_История_ПУ_№14/01_04.png', text: 'Профессиональное училище №14 было создано для подготовки квалифицированных рабочих кадров.' },
                ]
            },
            {
                name: 'История колледжа', items: [
                    { title: 'История колледжа', image: '/images/05_История_колледжа/01_05.png', text: 'Современный этап развития учебного заведения — Ярославский колледж управления и профессиональных технологий.' },
                ]
            },
        ],
        1: [ // Спортивная жизнь
            {
                name: 'Страницы истории спорта', items: [
                    { title: 'Спортивная история', image: '/images/06_Страницы_истории/02_06.png', text: 'Спортивные традиции нашего учебного заведения.' },
                ]
            },
            {
                name: 'Известные спортсмены — наши выпускники', items: [
                    { title: 'Известные спортсмены', image: '/images/07_Известные_спортсмены_наши_выпускники/02_07.png', text: 'Выпускники, прославившие колледж в спорте.' },
                ]
            },
            {
                name: 'Спортивные успехи', items: [
                    { title: 'Спортивные успехи', image: '/images/08_Спортивные_успехи/02_08.png', text: 'Достижения и победы в спортивных соревнованиях.' },
                ]
            },
        ],
        2: [ // Театр моды
            {
                name: 'Коллекции', items: [
                    { title: 'Коллекции Театра моды', image: '/images/09_Коллекции/03_09.png', text: 'Уникальные коллекции Театра моды колледжа.' },
                ]
            },
            {
                name: 'Известные модельеры — наши выпускники', items: [
                    { title: 'Известные модельеры', image: '/images/10_Известные_модельеры_наши_выпускники/03_10.png', text: 'Модельеры, получившие образование в нашем колледже.' },
                ]
            },
        ],
        3: [ // Студсовет
            {
                name: 'Достижения', items: [
                    { title: 'Достижения студсовета', image: '/images/11_Достижения/04_11.png', text: 'Достижения студенческого самоуправления.' },
                ]
            },
            {
                name: 'Лидеры', items: [
                    { title: 'Лидеры студсовета', image: '/images/12_Лидеры/04_12.png', text: 'Лидеры студенческого совета разных лет.' },
                ]
            },
        ],
        4: [ // Военная история
            {
                name: 'Сотрудники колледжа — участники СВО', items: [
                    { title: 'Участники СВО', image: '/images/13_Сотрудники_колледжа_участники_СВО/05_13.png', text: 'Сотрудники и выпускники колледжа — участники специальной военной операции.' },
                ]
            },
            {
                name: 'Страницы военной истории', items: [
                    { title: 'Военная история', image: '/images/14_СВО/05_14.png', text: 'Страницы истории учебного заведения в годы войны.' },
                ]
            },
            {
                name: 'История учебного заведения в годы войны', items: [
                    { title: 'В годы войны', image: '/images/15_История_учебного_заведения_в_годы_войны/05_15.png', text: 'Как работало учебное заведение в военные годы.' },
                ]
            },
        ],
        5: [ // Мероприятия
            {
                name: 'Мероприятия', items: [
                    { title: 'Мероприятия музея', image: '/images/06.png', text: 'Мероприятия и события, организованные музеем.' },
                ]
            },
            {
                name: 'Выставки', items: [
                    { title: 'Выставки музея', image: '/images/06.png', text: 'Выставки и экспозиции музея.' },
                ]
            },
        ],
        6: [ // Строительство
            {
                name: 'Документы акта приёмки', items: [
                    { title: 'Документы', image: '/images/16_Документ_акта_приемки/07_16.png', text: 'Документы акта приёмки новых корпусов.' },
                ]
            },
            {
                name: 'Фотографии строительства', items: [
                    { title: 'Фотографии', image: '/images/17_Фотографии/07_17.png', text: 'Фотографии строительства новых корпусов на Тутаевском шоссе.' },
                ]
            },
        ],
        7: [ // Знаменитые выпускники
            {
                name: 'Знаменитые выпускники', items: [
                    { title: 'Валентина Терешкова', image: '/images/tereshkova.jpg', text: 'Первая женщина в мире, совершившая космический полет.' },
                ]
            },
        ],
    }

    for (let i = 0; i < sections.length; i++) {
        const subs = subSectionsMap[i] || []
        for (let j = 0; j < subs.length; j++) {
            const sub = await prisma.subSection.create({
                data: {
                    sectionId: sections[i].id,
                    name: subs[j].name,
                    sortOrder: j + 1,
                },
            })
            for (let k = 0; k < subs[j].items.length; k++) {
                await prisma.contentItem.create({
                    data: {
                        subSectionId: sub.id,
                        title: subs[j].items[k].title,
                        image: subs[j].items[k].image,
                        text: subs[j].items[k].text,
                        sortOrder: k + 1,
                    },
                })
            }
        }
    }

    // ==================== EXHIBITS ====================
    await prisma.exhibit.deleteMany()
    await prisma.exhibit.createMany({
        data: [
            { name: 'Печатная машинка', image: '/images/Экспонаты/00_00_01.png' },
            { name: 'Ручной трудъ', image: '/images/Экспонаты/00_00_02.png' },
            { name: 'Производственный альбом', image: '/images/Экспонаты/00_00_03.png' },
            { name: 'Паспорт техникума', image: '/images/Экспонаты/00_00_04.png' },
            { name: 'Чернила', image: '/images/Экспонаты/00_00_05.png' },
            { name: 'Книга Михайлов', image: '/images/Экспонаты/00_00_06.png' },
            { name: 'Подстаканники', image: '/images/Экспонаты/00_00_07.png' },
            { name: 'Плакат', image: '/images/Экспонаты/00_00_08.png' },
        ],
    })

    // ==================== COMMENTS ====================  
    await prisma.comment.deleteMany()

    const allUsers = await prisma.user.findMany()
    const userMap = Object.fromEntries(allUsers.map(u => [u.login, u.id]))

    const c1 = await prisma.comment.create({
        data: { userId: userMap['kamisik'], content: 'Отличный виртуальный музей! Очень понравилось оформление и контент. Особенно запомнился раздел о знаменитых выпускниках. Прошёл все квесты с удовольствием!' },
    })
    await prisma.comment.create({
        data: { userId: userMap['nolik'], content: 'Согласен! Раздел со спортивными достижениями очень интересный.', parentId: c1.id },
    })
    await prisma.comment.create({
        data: { userId: userMap['elena'], content: 'Хотел бы предложить добавить больше интерактивных элементов. Может быть виртуальная экскурсия в реальном времени?' },
    })
    await prisma.comment.create({
        data: { userId: userMap['simka'], content: 'Прекрасный проект! Градиенты и анимации на высоте.', imagePath: '/images/comments/feedback.png' },
    })
    await prisma.comment.create({
        data: { userId: userMap['dedus'], content: 'Было бы здорово добавить возможность сохранять экспонаты в избранное.' },
    })

    // ==================== QUESTS ====================
    await prisma.quest.deleteMany()

    const quest1 = await prisma.quest.create({
        data: {
            title: 'История колледжа',
            description: 'Проверьте свои знания об истории Ярославского колледжа управления и профессиональных технологий. Пройдите все этапы и узнайте интересные факты!',
            durationMinutes: 15,
            difficultyLevel: 'easy',
            isActive: true,
            createdBy: admin.id,
        },
    })

    const quest2 = await prisma.quest.create({
        data: {
            title: 'Знаменитые выпускники',
            description: 'Квест о знаменитых выпускниках нашего колледжа. Узнайте об их достижениях и вкладе в историю.',
            durationMinutes: 20,
            difficultyLevel: 'medium',
            isActive: true,
            createdBy: admin.id,
        },
    })

    const quest3 = await prisma.quest.create({
        data: {
            title: 'Спортивные достижения',
            description: 'Узнайте о спортивных победах и достижениях колледжа. Сложный квест для настоящих знатоков!',
            durationMinutes: 30,
            difficultyLevel: 'hard',
            isActive: true,
            createdBy: admin.id,
        },
    })

    // Quest 1 steps
    await prisma.questStep.createMany({
        data: [
            { questId: quest1.id, stepOrder: 1, title: 'Основание', description: 'В каком году были организованы фабрично-заводские курсы при фабрике «Красный перекоп», положившие начало нашему учебному заведению?', solutionHash: hashSync('1930', 10), hintText: 'Это было в начале 1930-х годов', stepScore: 100, maxAttempts: 3 },
            { questId: quest1.id, stepOrder: 2, title: 'Текстильный техникум', description: 'В каком году было открыто дневное отделение Ярославского Текстильного Техникума?', solutionHash: hashSync('1933', 10), hintText: 'Через 3 года после основания курсов', stepScore: 100, maxAttempts: 3 },
            { questId: quest1.id, stepOrder: 3, title: 'Обувной техникум', description: 'На основании какого документа в 1944 году был открыт обувной техникум в Ярославле? Назовите тип документа (одно слово).', solutionHash: hashSync('постановление', 10), hintText: 'Это документ высшего государственного органа', stepScore: 150, maxAttempts: 3 },
        ],
    })

    // Quest 2 steps
    await prisma.questStep.createMany({
        data: [
            { questId: quest2.id, stepOrder: 1, title: 'Первая женщина-космонавт', description: 'Назовите фамилию первой женщины в мире, совершившей космический полёт, которая является выпускницей нашего колледжа.', solutionHash: hashSync('терешкова', 10), hintText: 'Она совершила полёт 16 июня 1963 года', stepScore: 100, maxAttempts: 3 },
            { questId: quest2.id, stepOrder: 2, title: 'Космический полёт', description: 'Как назывался космический корабль, на котором она совершила свой полёт? (Напишите название серии)', solutionHash: hashSync('восток', 10), hintText: 'Это шестой корабль в серии', stepScore: 150, maxAttempts: 3 },
        ],
    })

    // Quest 3 steps
    await prisma.questStep.createMany({
        data: [
            { questId: quest3.id, stepOrder: 1, title: 'Спортивные традиции', description: 'Какой вид спорта является одним из самых популярных в колледже? (одно слово)', solutionHash: hashSync('волейбол', 10), hintText: 'Командный вид спорта с мячом через сетку', stepScore: 100, maxAttempts: 3 },
            { questId: quest3.id, stepOrder: 2, title: 'Спортивные залы', description: 'Сколько спортивных залов находится в колледже?', solutionHash: hashSync('2', 10), hintText: 'Больше одного, но меньше трёх', stepScore: 150, maxAttempts: 3 },
        ],
    })

    // ==================== ADMIN LOG ====================
    await prisma.adminLog.deleteMany()
    await prisma.adminLog.create({
        data: {
            userId: admin.id,
            action: 'Инициализация системы',
            details: 'Первичная настройка базы данных',
        },
    })

    console.log('✅ Database seeded successfully!')
}

main()
    .catch((e) => {
        console.error('❌ Seed error:', e)
        process.exit(1)
    })
    .finally(async () => {
        await prisma.$disconnect()
    })
