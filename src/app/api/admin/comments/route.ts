import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const comments = await prisma.comment.findMany({
            orderBy: { createdAt: 'desc' },
            include: {
                user: { select: { id: true, login: true, avatar: true } },
                replies: {
                    orderBy: { createdAt: 'asc' },
                    include: {
                        user: { select: { id: true, login: true, avatar: true } },
                    },
                },
            },
        })
        return NextResponse.json(comments)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}

export async function POST(request: Request) {
    try {
        const { action, commentId, adminId } = await request.json()

        if (action === 'delete') {
            await prisma.comment.deleteMany({ where: { parentId: parseInt(commentId) } })
            await prisma.comment.delete({ where: { id: parseInt(commentId) } })

            await prisma.adminLog.create({
                data: {
                    userId: parseInt(adminId),
                    action: 'Удаление комментария',
                    details: `Comment ID: ${commentId}`,
                },
            })
            return NextResponse.json({ success: true })
        }

        return NextResponse.json({ error: 'Неизвестное действие' }, { status: 400 })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
