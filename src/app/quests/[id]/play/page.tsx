'use client'

import { useState, useEffect } from 'react'
import { useParams, useRouter } from 'next/navigation'
import { useSession } from 'next-auth/react'
import { Footer } from '@/components/layout/footer'
import { motion, AnimatePresence } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { ArrowLeft, Send, Lightbulb, Trophy, Loader2, CheckCircle2, XCircle, AlertTriangle, Star, Target } from 'lucide-react'
import Link from 'next/link'

interface Step {
    id: number
    title: string
    description: string
    stepScore: number
    maxAttempts: number
    mediaPath?: string
}

export default function QuestPlayPage() {
    const { theme } = useTheme()
    const params = useParams()
    const router = useRouter()
    const { data: session } = useSession()
    const [currentStep, setCurrentStep] = useState<Step | null>(null)
    const [stepIndex, setStepIndex] = useState(0)
    const [totalSteps, setTotalSteps] = useState(0)
    const [score, setScore] = useState(0)
    const [answer, setAnswer] = useState('')
    const [hint, setHint] = useState('')
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' | 'info' } | null>(null)
    const [loading, setLoading] = useState(true)
    const [submitting, setSubmitting] = useState(false)
    const [completed, setCompleted] = useState(false)
    const [sessionId, setSessionId] = useState<number | null>(null)

    const userId = (session?.user as Record<string, unknown>)?.id

    useEffect(() => {
        if (!session) return
        startQuest()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [session])

    const startQuest = async () => {
        try {
            const res = await fetch(`/api/quests/${params.id}/play`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId, action: 'start' }),
            })
            const data = await res.json()

            if (data.status === 'completed') {
                setCompleted(true)
                setSessionId(data.sessionId)
            } else {
                setCurrentStep(data.currentStep)
                setStepIndex(data.stepIndex)
                setTotalSteps(data.totalSteps)
                setScore(data.score)
                setSessionId(data.sessionId)
            }
        } catch {
            setMessage({ text: 'Ошибка загрузки', type: 'error' })
        } finally {
            setLoading(false)
        }
    }

    const submitAnswer = async () => {
        if (!answer.trim() || !currentStep) return
        setSubmitting(true)
        setMessage(null)

        try {
            const res = await fetch(`/api/quests/${params.id}/play`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId, action: 'answer', stepId: currentStep.id, answer: answer.trim() }),
            })
            const data = await res.json()

            if (data.completed) {
                setCompleted(true)
                setScore(data.score)
                setSessionId(data.sessionId)
                setMessage({ text: data.message, type: 'success' })
            } else if (data.correct) {
                setMessage({ text: data.message, type: 'success' })
                setScore(data.score)
                setCurrentStep(data.nextStep)
                setStepIndex(data.stepIndex)
                setTotalSteps(data.totalSteps)
                setAnswer('')
                setHint('')
            } else if (data.exhausted) {
                setMessage({ text: data.message, type: 'info' })
                if (data.nextStep) {
                    setCurrentStep(data.nextStep)
                    setStepIndex(data.stepIndex)
                    setTotalSteps(data.totalSteps)
                    setAnswer('')
                    setHint('')
                }
            } else {
                setMessage({ text: data.message, type: 'error' })
            }
        } catch {
            setMessage({ text: 'Ошибка сервера', type: 'error' })
        } finally {
            setSubmitting(false)
        }
    }

    const requestHint = async () => {
        if (!currentStep) return
        try {
            const res = await fetch(`/api/quests/${params.id}/play`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId, action: 'hint', stepId: currentStep.id }),
            })
            const data = await res.json()
            setHint(data.hint)
            if (data.penalty > 0) {
                setMessage({ text: `Подсказка использована. Штраф: -${data.penalty} баллов`, type: 'info' })
            }
        } catch {
            setMessage({ text: 'Ошибка получения подсказки', type: 'error' })
        }
    }

    if (!session) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-transparent">
                <div className="text-center">
                    <p className="text-foreground/40 mb-6 font-light tracking-wide italic">Доступ ограничен. Требуется авторизация.</p>
                    <Link href="/login" className="text-primary hover:text-white transition-colors uppercase font-black tracking-widest text-[10px]">Войти в систему</Link>
                </div>
            </div>
        )
    }

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-transparent">
                <Loader2 className="w-8 h-8 animate-spin text-primary" />
            </div>
        )
    }

    if (completed) {
        return (
            <>
                <div className="min-h-screen flex items-center justify-center bg-transparent py-40">
                    <motion.div
                        initial={{ scale: 0.8, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        transition={{ type: 'spring', stiffness: 100 }}
                        className="text-center p-12 bg-secondary/40 backdrop-blur-2xl rounded-[3rem] border border-white/10 shadow-3xl max-w-lg w-full mx-4"
                    >
                        <div className="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-8 border border-primary/20 shadow-2xl shadow-primary/10">
                            <Trophy className="w-12 h-12 text-primary" />
                        </div>
                        <h1 className="text-4xl font-sans font-black tracking-tighter uppercase text-white mb-4">Миссия <span className="text-primary italic font-serif lowercase tracking-normal">выполнена.</span></h1>
                        <p className="text-foreground/40 font-light tracking-wide mb-10 leading-relaxed">Вы успешно завершили исследование цифровых архивов и пополнили свою коллекцию баллов.</p>

                        <div className="bg-white/5 rounded-3xl border border-white/5 p-10 mb-10 transition-luxury hover:bg-white/10">
                            <p className="text-foreground/40 text-[10px] font-black tracking-[0.3em] uppercase mb-4">Результат исследования</p>
                            <p className="text-7xl font-sans font-black tracking-tighter text-white">
                                {score}
                            </p>
                        </div>

                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/quests" className="flex-1">
                                <button className="w-full px-8 py-4 rounded-full bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-luxury uppercase font-black tracking-widest text-[10px] magnetic-btn">
                                    К архивам
                                </button>
                            </Link>
                            <Link href="/leaderboard" className="flex-1">
                                <button className="w-full px-8 py-4 rounded-full bg-primary text-white hover:bg-white hover:text-black transition-luxury uppercase font-black tracking-widest text-[10px] shadow-xl shadow-primary/20 magnetic-btn">
                                    Таблица лидеров
                                </button>
                            </Link>
                        </div>
                    </motion.div>
                </div>
                <Footer />
            </>
        )
    }

    return (
        <>
            <main className="min-h-screen bg-transparent pt-40 pb-32">
                <div className="container mx-auto px-8 lg:px-16 max-w-4xl">
                    {/* Header with progress */}
                    <div className="mb-8">
                        <Link href="/quests" className="inline-flex items-center gap-2 text-[10px] font-black tracking-[0.3em] uppercase text-foreground/40 hover:text-primary mb-6 transition-luxury">
                            <ArrowLeft className="w-4 h-4" /> Архивы
                        </Link>
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-xl font-black uppercase tracking-widest text-white">
                                ЭТАП <span className="text-primary">{stepIndex + 1}</span> / {totalSteps}
                            </h2>
                            <div className="flex items-center gap-3 px-6 py-3 bg-white/5 rounded-full border border-white/10 shadow-2xl">
                                <Trophy className="w-5 h-5 text-primary" />
                                <span className="font-sans font-black tracking-tighter text-2xl text-white">{score}</span>
                            </div>
                        </div>

                        {/* Progress bar */}
                        <div className={`h-2 rounded-full overflow-hidden ${theme === 'dark' ? 'bg-white/10' : 'bg-gray-200'}`}>
                            <motion.div
                                className="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 rounded-full"
                                initial={{ width: 0 }}
                                animate={{ width: `${((stepIndex) / totalSteps) * 100}%` }}
                                transition={{ duration: 0.5 }}
                            />
                        </div>
                    </div>

                    {/* Current step */}
                    {currentStep && (
                        <motion.div
                            key={currentStep.id}
                            initial={{ opacity: 0, y: 30 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="rounded-[3rem] border border-white/10 p-10 md:p-16 bg-secondary/40 backdrop-blur-xl shadow-3xl"
                        >
                            <div className="mb-12">
                                <h3 className="text-4xl font-sans font-black tracking-tighter uppercase text-white mb-6">
                                    {currentStep.title}
                                </h3>
                                <p className="text-xl font-light tracking-wide text-white/60 leading-relaxed italic border-l-2 border-primary/20 pl-8 ml-2">
                                    {currentStep.description}
                                </p>
                            </div>

                            <div className="flex items-center gap-6 text-[10px] font-black tracking-[0.3em] uppercase text-foreground/40 mb-12">
                                <div className="flex items-center gap-2">
                                    <Star className="w-4 h-4 text-primary" /> ДО {currentStep.stepScore} БАЛЛОВ
                                </div>
                                <div className="flex items-center gap-2 border-l border-white/10 pl-6">
                                    <Target className="w-4 h-4 text-primary" /> {currentStep.maxAttempts} ПОПЫТКИ
                                </div>
                            </div>

                            {/* Answer input */}
                            <div className="flex flex-col sm:flex-row gap-4 mb-8">
                                <input
                                    type="text"
                                    value={answer}
                                    onChange={(e) => setAnswer(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && submitAnswer()}
                                    placeholder="Ваше предположение..."
                                    className="flex-1 rounded-full px-8 py-5 border border-white/5 bg-white/5 text-white placeholder-foreground/20 focus:outline-none focus:border-primary/50 focus:bg-white/10 transition-luxury font-light tracking-wide"
                                />
                                <button
                                    onClick={submitAnswer}
                                    disabled={submitting || !answer.trim()}
                                    className="px-12 py-5 rounded-full bg-primary text-white font-black uppercase tracking-widest text-[10px] disabled:opacity-20 hover:bg-white hover:text-black transition-luxury shadow-xl shadow-primary/10 flex items-center justify-center gap-3 magnetic-btn"
                                >
                                    {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <>ОТПРАВИТЬ <Send className="w-4 h-4" /></>}
                                </button>
                            </div>

                            {/* Hint button */}
                            <button
                                onClick={requestHint}
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-[10px] font-black tracking-[0.2em] uppercase text-primary/40 hover:text-primary hover:bg-white/5 transition-luxury bg-transparent"
                            >
                                <Lightbulb className="w-4 h-4" />
                                ПОЛУЧИТЬ ПОДСКАЗКУ (-20% БАЛЛОВ)
                            </button>

                            {/* Hint display */}
                            <AnimatePresence>
                                {hint && (
                                    <motion.div
                                        initial={{ opacity: 0, height: 0 }}
                                        animate={{ opacity: 1, height: 'auto' }}
                                        exit={{ opacity: 0, height: 0 }}
                                        className={`mt-4 p-4 rounded-xl border ${theme === 'dark'
                                            ? 'bg-yellow-500/10 border-yellow-500/20 text-yellow-200'
                                            : 'bg-yellow-50 border-yellow-200 text-yellow-800'
                                            }`}
                                    >
                                        <div className="flex items-start gap-2">
                                            <Lightbulb className="w-5 h-5 flex-shrink-0 mt-0.5" />
                                            <p>{hint}</p>
                                        </div>
                                    </motion.div>
                                )}
                            </AnimatePresence>

                            {/* Message */}
                            <AnimatePresence>
                                {message && (
                                    <motion.div
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        exit={{ opacity: 0, y: -10 }}
                                        className={`mt-4 p-4 rounded-xl border flex items-start gap-2 ${message.type === 'success'
                                            ? theme === 'dark' ? 'bg-green-500/10 border-green-500/20 text-green-200' : 'bg-green-50 border-green-200 text-green-800'
                                            : message.type === 'error'
                                                ? theme === 'dark' ? 'bg-red-500/10 border-red-500/20 text-red-200' : 'bg-red-50 border-red-200 text-red-800'
                                                : theme === 'dark' ? 'bg-fuchsia-500/10 border-fuchsia-500/20 text-blue-200' : 'bg-blue-50 border-blue-200 text-blue-800'
                                            }`}
                                    >
                                        {message.type === 'success' ? <CheckCircle2 className="w-5 h-5 flex-shrink-0" /> :
                                            message.type === 'error' ? <XCircle className="w-5 h-5 flex-shrink-0" /> :
                                                <AlertTriangle className="w-5 h-5 flex-shrink-0" />}
                                        <p>{message.text}</p>
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </motion.div>
                    )}
                </div>
            </main>
            <Footer />
        </>
    )
}
