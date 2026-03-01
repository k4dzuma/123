import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(request: Request) {
    try {
        // Basic stats
        const totalUsers = await prisma.user.count()
        const totalQuests = await prisma.quest.count()
        const totalComments = await prisma.comment.count()

        const now = new Date()
        const lastWeek = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)

        const newUsersLastWeek = await prisma.user.count({
            where: { createdAt: { gte: lastWeek } }
        })

        // Activity over time (last 7 days)
        // For SQLite, we group by date string
        const activityData = []

        for (let i = 6; i >= 0; i--) {
            const d = new Date()
            d.setDate(d.getDate() - i)
            const start = new Date(d.setHours(0, 0, 0, 0))
            const end = new Date(d.setHours(23, 59, 59, 999))

            const dayName = start.toLocaleDateString('ru-RU', { weekday: 'short', day: 'numeric' })

            const regs = await prisma.user.count({ where: { createdAt: { gte: start, lte: end } } })
            const sess = await prisma.playerSession.count({ where: { startTime: { gte: start, lte: end } } })
            const comms = await prisma.comment.count({ where: { createdAt: { gte: start, lte: end } } })

            activityData.push({
                name: dayName,
                Пользователи: regs,
                Сессии: sess,
                Комментарии: comms
            })
        }

        // Role distribution
        const roleDistribution = await prisma.user.groupBy({
            by: ['role'],
            _count: { id: true }
        }).then((res: Array<{ role: string; _count: { id: number } }>) => res.map((r) => ({ name: r.role, value: r._count.id })))


        return NextResponse.json({
            stats: {
                totalUsers,
                totalQuests,
                totalComments,
                newUsersLastWeek
            },
            activityData,
            roleDistribution
        })
    } catch (error) {
        console.error(error)
        return NextResponse.json({ error: 'Server error' }, { status: 500 })
    }
}
