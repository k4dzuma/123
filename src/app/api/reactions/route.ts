import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function POST(request: Request) {
    try {
        const { commentId, userId, type } = await request.json()

        if (!commentId || !userId || !['like', 'dislike'].includes(type)) {
            return NextResponse.json({ error: 'Invalid request' }, { status: 400 })
        }

        // Check if user already reacted - toggle or change
        // We store reactions as  simple JSON in eventData on a special model
        // For simplicity, let's use a direct approach with raw SQL since we don't have a Reaction model
        // Instead, we'll create the reaction directly in comments API
        // Let's check existing and toggle

        // Since we don't have a Reaction model yet, let's use a simple approach:
        // Store as Comment metadata - we'll add reactions as a separate simple storage

        // For now, use a pragmatic approach: store in Setting model as JSON
        const key = `reaction_${commentId}_${userId}`
        const existing = await prisma.setting.findFirst({
            where: { key },
        })

        if (existing) {
            if (existing.value === type) {
                // Remove reaction (toggle off)
                await prisma.setting.delete({ where: { key: existing.key } })
            } else {
                // Change reaction
                await prisma.setting.update({
                    where: { key: existing.key },
                    data: { value: type },
                })
            }
        } else {
            // New reaction
            await prisma.setting.create({
                data: { key, value: type },
            })
        }

        // Get counts
        const likes = await prisma.setting.count({
            where: { key: { startsWith: `reaction_${commentId}_` }, value: 'like' },
        })
        const dislikes = await prisma.setting.count({
            where: { key: { startsWith: `reaction_${commentId}_` }, value: 'dislike' },
        })

        // Get user's current reaction
        const userReaction = await prisma.setting.findFirst({
            where: { key },
        })

        return NextResponse.json({
            likes,
            dislikes,
            userReaction: userReaction?.value || null,
        })
    } catch {
        return NextResponse.json({ error: 'Server error' }, { status: 500 })
    }
}

export async function GET(request: Request) {
    const { searchParams } = new URL(request.url)
    const commentIds = searchParams.get('ids')?.split(',').map(Number).filter(Boolean) || []
    const userId = searchParams.get('userId')

    if (commentIds.length === 0) {
        return NextResponse.json({})
    }

    try {
        const reactions: Record<number, { likes: number; dislikes: number; userReaction: string | null }> = {}

        for (const commentId of commentIds) {
            const likes = await prisma.setting.count({
                where: { key: { startsWith: `reaction_${commentId}_` }, value: 'like' },
            })
            const dislikes = await prisma.setting.count({
                where: { key: { startsWith: `reaction_${commentId}_` }, value: 'dislike' },
            })

            let userReaction = null
            if (userId) {
                const ur = await prisma.setting.findFirst({
                    where: { key: `reaction_${commentId}_${userId}` },
                })
                userReaction = ur?.value || null
            }

            reactions[commentId] = { likes, dislikes, userReaction }
        }

        return NextResponse.json(reactions)
    } catch {
        return NextResponse.json({ error: 'Server error' }, { status: 500 })
    }
}
