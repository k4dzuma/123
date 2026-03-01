import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET(request: Request) {
    const { searchParams } = new URL(request.url)
    const q = searchParams.get('q')?.trim()

    if (!q || q.length < 2) {
        return NextResponse.json({ sections: [], quests: [], exhibits: [] })
    }

    try {
        const [sections, quests, exhibits] = await Promise.all([
            prisma.section.findMany({
                where: {
                    OR: [
                        { name: { contains: q } },
                        { description: { contains: q } },
                    ],
                },
                take: 5,
                select: { id: true, name: true, description: true, image: true },
            }),
            prisma.quest.findMany({
                where: {
                    isActive: true,
                    OR: [
                        { title: { contains: q } },
                        { description: { contains: q } },
                    ],
                },
                take: 5,
                select: { id: true, title: true, description: true, difficultyLevel: true },
            }),
            prisma.exhibit.findMany({
                where: {
                    name: { contains: q },
                },
                take: 5,
                select: { id: true, name: true, image: true },
            }),
        ])

        return NextResponse.json({ sections, quests, exhibits })
    } catch {
        return NextResponse.json({ error: 'Search failed' }, { status: 500 })
    }
}
