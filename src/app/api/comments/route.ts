import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const comments = await prisma.comment.findMany({
            where: { parentId: null },
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
        const { userId, content, parentId, imagePath } = await request.json()

        if (!userId || !content) {
            return NextResponse.json({ error: 'Необходимо указать userId и content' }, { status: 400 })
        }

        if (content.trim().length < 3) {
            return NextResponse.json({ error: 'Комментарий слишком короткий' }, { status: 400 })
        }

        // Check user warnings
        const warningCount = await prisma.userWarning.count({
            where: {
                userId: parseInt(userId),
                expiresAt: { gt: new Date() },
            },
        })

        if (warningCount >= 3) {
            return NextResponse.json({ error: 'Ваш аккаунт заблокирован для комментирования' }, { status: 403 })
        }

        const comment = await prisma.comment.create({
            data: {
                userId: parseInt(userId),
                content: content.trim(),
                parentId: parentId ? parseInt(parentId) : null,
                imagePath: imagePath || null,
            },
            include: {
                user: { select: { id: true, login: true, avatar: true } },
            },
        })

        return NextResponse.json(comment)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
