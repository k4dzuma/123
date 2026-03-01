import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export async function GET() {
    try {
        const sections = await prisma.section.findMany({
            orderBy: { sortOrder: 'asc' },
            include: {
                subSections: {
                    orderBy: { sortOrder: 'asc' },
                    include: { _count: { select: { items: true } } },
                },
            },
        })
        return NextResponse.json(sections)
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}

export async function POST(request: Request) {
    try {
        const { action, ...data } = await request.json()

        if (action === 'create_section') {
            const section = await prisma.section.create({
                data: {
                    name: data.name,
                    description: data.description || '',
                    image: data.image || '/images/museum/default.jpg',
                    sortOrder: data.sortOrder || 0,
                },
            })
            return NextResponse.json(section)
        }

        if (action === 'update_section') {
            const section = await prisma.section.update({
                where: { id: data.id },
                data: {
                    name: data.name,
                    description: data.description,
                    image: data.image,
                    sortOrder: data.sortOrder,
                },
            })
            return NextResponse.json(section)
        }

        if (action === 'delete_section') {
            await prisma.section.delete({ where: { id: data.id } })
            return NextResponse.json({ success: true })
        }

        if (action === 'create_subsection') {
            const sub = await prisma.subSection.create({
                data: {
                    sectionId: data.sectionId,
                    name: data.name,
                    sortOrder: data.sortOrder || 0,
                },
            })
            return NextResponse.json(sub)
        }

        if (action === 'delete_subsection') {
            await prisma.subSection.delete({ where: { id: data.id } })
            return NextResponse.json({ success: true })
        }

        if (action === 'create_item') {
            const item = await prisma.contentItem.create({
                data: {
                    subSectionId: data.subSectionId,
                    title: data.title,
                    text: data.text || '',
                    image: data.image || '',
                    sortOrder: data.sortOrder || 0,
                },
            })
            return NextResponse.json(item)
        }

        if (action === 'delete_item') {
            await prisma.contentItem.delete({ where: { id: data.id } })
            return NextResponse.json({ success: true })
        }

        return NextResponse.json({ error: 'Неизвестное действие' }, { status: 400 })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
