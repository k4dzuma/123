import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const quests = await prisma.quest.findMany({
            where: { isActive: true },
            orderBy: { createdAt: 'desc' },
            include: {
                _count: { select: { steps: true, sessions: true } },
                sessions: {
                    where: { status: 'completed' },
                    select: { id: true },
                },
            },
        })

        const result = quests.map((q) => ({
            id: q.id,
            title: q.title,
            description: q.description,
            durationMinutes: q.durationMinutes,
            difficultyLevel: q.difficultyLevel,
            stepCount: q._count.steps,
            totalSessions: q._count.sessions,
            completedSessions: q.sessions.length,
        }))

        return NextResponse.json(result)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
