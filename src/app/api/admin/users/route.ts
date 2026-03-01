import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const users = await prisma.user.findMany({
            orderBy: { createdAt: 'desc' },
            select: {
                id: true,
                login: true,
                email: true,
                avatar: true,
                role: true,
                totalScore: true,
                createdAt: true,
                _count: {
                    select: {
                        warningsReceived: { where: { expiresAt: { gt: new Date() } } },
                    },
                },
            },
        })

        return NextResponse.json(
            users.map((u: any) => ({
                ...u,
                warningCount: u._count.warningsReceived,
                _count: undefined,
            }))
        )
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}

export async function POST(request: Request) {
    try {
        const { action, userId, reason, adminId, role } = await request.json()

        if (action === 'warn') {
            const expiresAt = new Date()
            expiresAt.setDate(expiresAt.getDate() + 7)

            await prisma.userWarning.create({
                data: {
                    userId: parseInt(userId),
                    adminId: parseInt(adminId),
                    reason,
                    expiresAt,
                },
            })

            await prisma.adminLog.create({
                data: {
                    userId: parseInt(adminId),
                    action: 'Предупреждение пользователю',
                    details: `User ID: ${userId}, Причина: ${reason}`,
                },
            })

            return NextResponse.json({ success: true })
        } else if (action === 'change_role') {
            await prisma.user.update({
                where: { id: parseInt(userId) },
                data: { role: role === 'ADMIN' ? 'ADMIN' : 'USER' }
            })
            await prisma.adminLog.create({
                data: {
                    userId: parseInt(adminId),
                    action: 'Изменение роли',
                    details: `User ID: ${userId}, Новая роль: ${role}`
                }
            })
            return NextResponse.json({ success: true })
        } else if (action === 'toggle_ban') {
            const user = await prisma.user.findUnique({ where: { id: parseInt(userId) }, select: { role: true } })
            if (user) {
                const newRole = user.role === 'BANNED' ? 'USER' : 'BANNED'
                await prisma.user.update({
                    where: { id: parseInt(userId) },
                    data: { role: newRole }
                })
                await prisma.adminLog.create({
                    data: {
                        userId: parseInt(adminId),
                        action: newRole === 'BANNED' ? 'Бан' : 'Разбан',
                        details: `User ID: ${userId}`
                    }
                })
            }
            return NextResponse.json({ success: true })
        }

        return NextResponse.json({ error: 'Неизвестное действие' }, { status: 400 })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
