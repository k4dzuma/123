import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const [
            totalUsers,
            totalComments,
            totalQuests,
            totalSessions,
            totalCompletions,
            totalSections,
            totalExhibits,
        ] = await Promise.all([
            prisma.user.count(),
            prisma.comment.count(),
            prisma.quest.count(),
            prisma.playerSession.count(),
            prisma.playerSession.count({ where: { status: 'completed' } }),
            prisma.section.count(),
            prisma.exhibit.count(),
        ])

        return NextResponse.json({
            totalUsers,
            totalComments,
            totalQuests,
            totalSessions,
            totalCompletions,
            totalSections,
            totalExhibits,
        })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
