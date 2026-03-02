import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const users = await prisma.user.findMany({
            where: { totalScore: { gt: 0 } },
            orderBy: { totalScore: 'desc' },
            take: 20,
            select: {
                id: true,
                login: true,
                avatar: true,
                totalScore: true,
                _count: { select: { sessions: { where: { status: 'completed' } } } },
            },
        })

        const leaderboard = users.map((u: any, i: any) => ({
            rank: i + 1,
            id: u.id,
            login: u.login,
            avatar: u.avatar,
            totalScore: u.totalScore,
            completedQuests: u._count.sessions,
        }))

        // Stats
        const totalUsers = await prisma.user.count()
        const totalPoints = await prisma.user.aggregate({ _sum: { totalScore: true } })
        const totalCompletions = await prisma.playerSession.count({ where: { status: 'completed' } })

        return NextResponse.json({
            leaderboard,
            stats: {
                totalUsers,
                totalPoints: totalPoints._sum.totalScore || 0,
                totalCompletions,
            },
        })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
