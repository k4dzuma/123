import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const sections = await prisma.section.findMany({
            orderBy: { sortOrder: 'asc' },
            include: {
                subSections: {
                    orderBy: { sortOrder: 'asc' },
                    include: { _count: { select: { items: true } } },
                },
            },
        })

        return NextResponse.json(sections)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
