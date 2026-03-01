import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(
    _: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params
        const section = await prisma.section.findUnique({
            where: { id: parseInt(id) },
            include: {
                subSections: {
                    orderBy: { sortOrder: 'asc' },
                    include: {
                        items: { orderBy: { sortOrder: 'asc' } },
                    },
                },
            },
        })

        if (!section) {
            return NextResponse.json({ error: 'Раздел не найден' }, { status: 404 })
        }

        return NextResponse.json(section)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
