import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(request: Request) {
    const { searchParams } = new URL(request.url)
    const userId = searchParams.get('userId')

    if (!userId) {
        return NextResponse.json({ error: 'userId required' }, { status: 400 })
    }

    try {
        const user = await prisma.user.findUnique({
            where: { id: parseInt(userId) },
            select: {
                id: true, login: true, email: true, avatar: true, role: true,
                totalScore: true, createdAt: true,
            },
        })

        if (!user) {
            return NextResponse.json({ error: 'User not found' }, { status: 404 })
        }

        // Quest stats
        const sessions = await prisma.playerSession.findMany({
            where: { playerId: parseInt(userId) },
            include: { quest: { select: { title: true } } },
            orderBy: { startTime: 'desc' },
        })

        const completedSessions = sessions.filter((s: { status: string }) => s.status === 'completed')
        const totalPoints = sessions.reduce((sum: number, s: { sessionScore: number }) => sum + s.sessionScore, 0)
        const avgScore = completedSessions.length > 0
            ? Math.round(totalPoints / completedSessions.length)
            : 0

        // Rank
        const usersAbove = await prisma.user.count({
            where: { totalScore: { gt: user.totalScore } },
        })
        const rank = usersAbove + 1

        // Comments count
        const commentsCount = await prisma.comment.count({
            where: { userId: parseInt(userId) },
        })

        // Achievements
        const achievements = []
        if (completedSessions.length >= 1) achievements.push({ id: 'first_quest', name: 'Первый квест', icon: '🎯', desc: 'Пройти первый квест' })
        if (completedSessions.length >= 5) achievements.push({ id: 'veteran', name: 'Ветеран', icon: '⭐', desc: 'Пройти 5 квестов' })
        if (completedSessions.length >= 10) achievements.push({ id: 'master', name: 'Мастер', icon: '👑', desc: 'Пройти 10 квестов' })
        if (totalPoints >= 500) achievements.push({ id: 'scholar', name: 'Эрудит', icon: '📚', desc: 'Набрать 500 баллов' })
        if (totalPoints >= 1000) achievements.push({ id: 'genius', name: 'Гений', icon: '🧠', desc: 'Набрать 1000 баллов' })
        if (commentsCount >= 1) achievements.push({ id: 'commentator', name: 'Комментатор', icon: '💬', desc: 'Оставить первый отзыв' })
        if (commentsCount >= 10) achievements.push({ id: 'active', name: 'Активист', icon: '🔥', desc: 'Оставить 10 отзывов' })
        if (rank === 1) achievements.push({ id: 'champion', name: 'Чемпион', icon: '🏆', desc: 'Занять первое место' })
        if (rank <= 3) achievements.push({ id: 'top3', name: 'Топ-3', icon: '🥇', desc: 'Войти в тройку лучших' })

        return NextResponse.json({
            user,
            stats: {
                totalQuests: completedSessions.length,
                totalPoints,
                avgScore,
                rank,
                commentsCount,
            },
            achievements,
            recentSessions: sessions.slice(0, 10).map((s: any) => ({
                id: s.id,
                questTitle: s.quest.title,
                score: s.sessionScore,
                status: s.status,
                startTime: s.startTime,
                endTime: s.endTime,
            })),
        })
    } catch {
        return NextResponse.json({ error: 'Server error' }, { status: 500 })
    }
}
