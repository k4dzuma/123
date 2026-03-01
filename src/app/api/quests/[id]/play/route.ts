import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { compareSync } from 'bcryptjs'

export async function POST(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params
        const questId = parseInt(id)
        const { userId, action, stepId, answer } = await request.json()

        if (!userId) {
            return NextResponse.json({ error: 'Необходима авторизация' }, { status: 401 })
        }

        const quest = await prisma.quest.findUnique({
            where: { id: questId },
            include: { steps: { orderBy: { stepOrder: 'asc' } } },
        })

        if (!quest) {
            return NextResponse.json({ error: 'Квест не найден' }, { status: 404 })
        }

        // Start or get existing session
        if (action === 'start') {
            let session = await prisma.playerSession.findFirst({
                where: { playerId: parseInt(userId), questId, status: 'in_progress' },
            })

            if (!session) {
                // Check if already completed
                const completed = await prisma.playerSession.findFirst({
                    where: { playerId: parseInt(userId), questId, status: 'completed' },
                })

                if (completed) {
                    return NextResponse.json({
                        status: 'completed',
                        sessionId: completed.id,
                        message: 'Вы уже прошли этот квест',
                    })
                }

                session = await prisma.playerSession.create({
                    data: {
                        playerId: parseInt(userId),
                        questId,
                        currentStepId: quest.steps[0]?.id,
                    },
                })

                if (quest.steps[0]) {
                    await prisma.sessionEvent.create({
                        data: {
                            sessionId: session.id,
                            eventType: 'step_started',
                            relatedStepId: quest.steps[0].id,
                        },
                    })
                }
            }

            const currentStep = quest.steps.find((s) => s.id === session!.currentStepId)
            const stepIndex = quest.steps.findIndex((s) => s.id === session!.currentStepId)

            return NextResponse.json({
                status: 'in_progress',
                sessionId: session.id,
                score: session.sessionScore,
                currentStep: currentStep
                    ? {
                        id: currentStep.id,
                        title: currentStep.title,
                        description: currentStep.description,
                        stepScore: currentStep.stepScore,
                        maxAttempts: currentStep.maxAttempts,
                        mediaPath: currentStep.mediaPath,
                    }
                    : null,
                stepIndex,
                totalSteps: quest.steps.length,
            })
        }

        // Answer a step
        if (action === 'answer') {
            const session = await prisma.playerSession.findFirst({
                where: { playerId: parseInt(userId), questId, status: 'in_progress' },
            })

            if (!session) {
                return NextResponse.json({ error: 'Сессия не найдена' }, { status: 404 })
            }

            const step = quest.steps.find((s) => s.id === parseInt(stepId))
            if (!step) {
                return NextResponse.json({ error: 'Шаг не найден' }, { status: 404 })
            }

            // Count attempts
            const attemptCount = await prisma.sessionEvent.count({
                where: {
                    sessionId: session.id,
                    relatedStepId: step.id,
                    eventType: 'solution_attempt',
                },
            })

            if (attemptCount >= step.maxAttempts) {
                // Move to next step
                const stepIndex = quest.steps.findIndex((s) => s.id === step.id)
                const nextStep = quest.steps[stepIndex + 1]

                if (nextStep) {
                    await prisma.playerSession.update({
                        where: { id: session.id },
                        data: { currentStepId: nextStep.id },
                    })

                    return NextResponse.json({
                        correct: false,
                        message: 'Попытки исчерпаны. Переход к следующему этапу.',
                        exhausted: true,
                        nextStep: { id: nextStep.id, title: nextStep.title, description: nextStep.description, stepScore: nextStep.stepScore, maxAttempts: nextStep.maxAttempts },
                        stepIndex: stepIndex + 1,
                        totalSteps: quest.steps.length,
                        score: session.sessionScore,
                    })
                } else {
                    // Quest completed
                    await prisma.playerSession.update({
                        where: { id: session.id },
                        data: { status: 'completed', endTime: new Date() },
                    })
                    await prisma.user.update({
                        where: { id: parseInt(userId) },
                        data: { totalScore: { increment: session.sessionScore } },
                    })

                    return NextResponse.json({
                        correct: false,
                        message: 'Квест завершён!',
                        completed: true,
                        sessionId: session.id,
                        score: session.sessionScore,
                    })
                }
            }

            // Record attempt
            await prisma.sessionEvent.create({
                data: {
                    sessionId: session.id,
                    eventType: 'solution_attempt',
                    relatedStepId: step.id,
                    eventData: answer,
                },
            })

            const normalizedAnswer = answer.toLowerCase().trim()
            const isCorrect = compareSync(normalizedAnswer, step.solutionHash)

            if (isCorrect) {
                let score = step.stepScore
                if (attemptCount > 0) {
                    score = Math.max(Math.floor(score * (1 - attemptCount * 0.2)), Math.floor(score * 0.3))
                }

                await prisma.sessionEvent.create({
                    data: {
                        sessionId: session.id,
                        eventType: 'step_completed',
                        relatedStepId: step.id,
                        scoreDelta: score,
                    },
                })

                const newScore = session.sessionScore + score
                const stepIndex = quest.steps.findIndex((s) => s.id === step.id)
                const nextStep = quest.steps[stepIndex + 1]

                if (nextStep) {
                    await prisma.playerSession.update({
                        where: { id: session.id },
                        data: { currentStepId: nextStep.id, sessionScore: newScore },
                    })

                    await prisma.sessionEvent.create({
                        data: {
                            sessionId: session.id,
                            eventType: 'step_started',
                            relatedStepId: nextStep.id,
                        },
                    })

                    return NextResponse.json({
                        correct: true,
                        message: `Правильно! +${score} баллов`,
                        score: newScore,
                        nextStep: { id: nextStep.id, title: nextStep.title, description: nextStep.description, stepScore: nextStep.stepScore, maxAttempts: nextStep.maxAttempts },
                        stepIndex: stepIndex + 1,
                        totalSteps: quest.steps.length,
                    })
                } else {
                    // Quest completed!
                    await prisma.playerSession.update({
                        where: { id: session.id },
                        data: { status: 'completed', endTime: new Date(), sessionScore: newScore },
                    })
                    await prisma.user.update({
                        where: { id: parseInt(userId) },
                        data: { totalScore: { increment: newScore } },
                    })

                    return NextResponse.json({
                        correct: true,
                        message: `Правильно! +${score} баллов. Квест завершён!`,
                        completed: true,
                        sessionId: session.id,
                        score: newScore,
                    })
                }
            } else {
                const remaining = step.maxAttempts - attemptCount - 1

                return NextResponse.json({
                    correct: false,
                    message: `Неверный ответ. Осталось попыток: ${remaining}`,
                    remaining,
                    score: session.sessionScore,
                })
            }
        }

        // Use hint
        if (action === 'hint') {
            const session = await prisma.playerSession.findFirst({
                where: { playerId: parseInt(userId), questId, status: 'in_progress' },
            })

            if (!session) {
                return NextResponse.json({ error: 'Сессия не найдена' }, { status: 404 })
            }

            const step = quest.steps.find((s) => s.id === parseInt(stepId))
            if (!step || !step.hintText) {
                return NextResponse.json({ error: 'Подсказка недоступна' }, { status: 404 })
            }

            // Check if hint already used
            const hintUsed = await prisma.sessionEvent.findFirst({
                where: {
                    sessionId: session.id,
                    relatedStepId: step.id,
                    eventType: 'hint_used',
                },
            })

            if (!hintUsed) {
                const penalty = -Math.floor(step.stepScore * 0.2)
                await prisma.sessionEvent.create({
                    data: {
                        sessionId: session.id,
                        eventType: 'hint_used',
                        relatedStepId: step.id,
                        scoreDelta: penalty,
                    },
                })
                await prisma.playerSession.update({
                    where: { id: session.id },
                    data: { sessionScore: { increment: penalty } },
                })
            }

            return NextResponse.json({
                hint: step.hintText,
                penalty: hintUsed ? 0 : Math.floor(step.stepScore * 0.2),
            })
        }

        return NextResponse.json({ error: 'Неизвестное действие' }, { status: 400 })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
