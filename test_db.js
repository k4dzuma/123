const { PrismaClient } = require('@prisma/client');
const { PrismaBetterSqlite3 } = require('@prisma/adapter-better-sqlite3');

async function test() {
    try {
        const adapter = new PrismaBetterSqlite3({
            url: process.env.DATABASE_URL || 'file:./dev.db',
        });
        const prisma = new PrismaClient({ adapter });
        const sections = await prisma.section.findMany({
            orderBy: { sortOrder: 'asc' },
            include: {
                subSections: {
                    orderBy: { sortOrder: 'asc' },
                    include: { _count: { select: { items: true } } },
                },
            },
        });
        console.log('SUCCESS:', sections.length, 'sections found.');
    } catch (e) {
        console.error('ERROR:', e);
    }
}

test();
