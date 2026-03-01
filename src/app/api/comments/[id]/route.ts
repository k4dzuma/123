import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function DELETE(
    _: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params
        // Delete replies first
        await prisma.comment.deleteMany({ where: { parentId: parseInt(id) } })
        await prisma.comment.delete({ where: { id: parseInt(id) } })
        return NextResponse.json({ success: true })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}

export async function PATCH(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params
        const { content, imagePath } = await request.json()

        const updateData: Record<string, unknown> = {}
        if (content !== undefined) updateData.content = content
        if (imagePath !== undefined) updateData.imagePath = imagePath

        const comment = await prisma.comment.update({
            where: { id: parseInt(id) },
            data: updateData,
            include: {
                user: { select: { id: true, login: true, avatar: true } },
            },
        })

        return NextResponse.json(comment)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
