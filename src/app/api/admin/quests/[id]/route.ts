import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params
        const quest = await prisma.quest.findUnique({
            where: { id: parseInt(id) },
            include: {
                steps: { orderBy: { stepOrder: 'asc' } },
                sessions: {
                    orderBy: { startTime: 'desc' },
                    include: {
                        player: { select: { id: true, login: true, avatar: true } },
                        events: {
                            orderBy: { createdAt: 'asc' },
                            include: {
                                step: { select: { id: true, title: true, stepOrder: true } },
                            },
                        },
                    },
                },
            },
        })

        if (!quest) {
            return NextResponse.json({ error: 'Квест не найден' }, { status: 404 })
        }

        return NextResponse.json(quest)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
