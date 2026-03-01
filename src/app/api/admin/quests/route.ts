import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { hashSync } from 'bcryptjs'

export async function GET() {
    try {
        const quests = await prisma.quest.findMany({
            orderBy: { createdAt: 'desc' },
            include: {
                _count: { select: { steps: true, sessions: true } },
                sessions: {
                    where: { status: 'completed' },
                    select: { id: true },
                },
            },
        })

        return NextResponse.json(
            quests.map((q) => ({
                id: q.id,
                title: q.title,
                description: q.description,
                durationMinutes: q.durationMinutes,
                difficultyLevel: q.difficultyLevel,
                isActive: q.isActive,
                createdAt: q.createdAt,
                stepCount: q._count.steps,
                sessionCount: q._count.sessions,
                completedCount: q.sessions.length,
            }))
        )
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}

export async function POST(request: Request) {
    try {
        const { action, ...data } = await request.json()

        if (action === 'create') {
            const quest = await prisma.quest.create({
                data: {
                    title: data.title,
                    description: data.description || '',
                    durationMinutes: data.durationMinutes || 30,
                    difficultyLevel: data.difficultyLevel || 'medium',
                    isActive: data.isActive ?? true,
                    createdBy: data.createdBy,
                },
            })
            return NextResponse.json(quest)
        }

        if (action === 'update') {
            const quest = await prisma.quest.update({
                where: { id: data.id },
                data: {
                    title: data.title,
                    description: data.description,
                    durationMinutes: data.durationMinutes,
                    difficultyLevel: data.difficultyLevel,
                    isActive: data.isActive,
                },
            })
            return NextResponse.json(quest)
        }

        if (action === 'delete') {
            await prisma.quest.delete({ where: { id: data.id } })
            return NextResponse.json({ success: true })
        }

        if (action === 'toggle') {
            const quest = await prisma.quest.findUnique({ where: { id: data.id } })
            if (quest) {
                await prisma.quest.update({
                    where: { id: data.id },
                    data: { isActive: !quest.isActive },
                })
            }
            return NextResponse.json({ success: true })
        }

        if (action === 'add_step') {
            const step = await prisma.questStep.create({
                data: {
                    questId: data.questId,
                    stepOrder: data.stepOrder || 1,
                    title: data.title,
                    description: data.description,
                    solutionHash: hashSync(data.solution.toLowerCase().trim(), 10),
                    hintText: data.hintText || null,
                    stepScore: data.stepScore || 100,
                    maxAttempts: data.maxAttempts || 3,
                },
            })
            return NextResponse.json(step)
        }

        if (action === 'update_step') {
            const updateData: Record<string, unknown> = {
                title: data.title,
                description: data.description,
                hintText: data.hintText,
                stepScore: data.stepScore,
                maxAttempts: data.maxAttempts,
                stepOrder: data.stepOrder,
            }

            if (data.solution) {
                updateData.solutionHash = hashSync(data.solution.toLowerCase().trim(), 10)
            }

            const step = await prisma.questStep.update({
                where: { id: data.stepId },
                data: updateData,
            })
            return NextResponse.json(step)
        }

        if (action === 'delete_step') {
            await prisma.questStep.delete({ where: { id: data.stepId } })
            return NextResponse.json({ success: true })
        }

        return NextResponse.json({ error: 'Неизвестное действие' }, { status: 400 })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
