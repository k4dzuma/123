'use client'

import { useState, useEffect } from 'react'
import { useParams, useRouter } from 'next/navigation'
import { useTheme } from '@/components/theme/theme-provider'
import { motion } from 'framer-motion'
import { ArrowLeft, Loader2, User, Clock, CheckCircle, XCircle, Award, Eye, Lightbulb } from 'lucide-react'

interface SessionEvent {
    id: number
    eventType: string
    eventData: string
    createdAt: string
    step: { id: number; title: string; stepOrder: number } | null
}

interface Session {
    id: number
    status: string
    score: number
    currentStep: number
    startTime: string
    endTime: string | null
    player: { id: number; login: string; avatar: string }
    events: SessionEvent[]
}

interface QuestDetail {
    id: number
    title: string
    description: string
    steps: { id: number; stepOrder: number; title: string }[]
    sessions: Session[]
}

export default function QuestSessionsPage() {
    const { theme } = useTheme()
    const params = useParams()
    const router = useRouter()
    const [quest, setQuest] = useState<QuestDetail | null>(null)
    const [loading, setLoading] = useState(true)
    const [expandedSession, setExpandedSession] = useState<number | null>(null)

    useEffect(() => {
        if (params.id) {
            fetch(`/api/admin/quests/${params.id}`)
                .then(r => r.json())
                .then(setQuest)
                .catch(console.error)
                .finally(() => setLoading(false))
        }
    }, [params.id])

    const statusMap: Record<string, { label: string; color: string; icon: typeof CheckCircle }> = {
        completed: { label: 'Завершён', color: 'text-green-400 bg-green-500/20', icon: CheckCircle },
        active: { label: 'В процессе', color: 'text-blue-400 bg-fuchsia-500/20', icon: Clock },
        abandoned: { label: 'Брошен', color: 'text-red-400 bg-red-500/20', icon: XCircle },
    }

    const eventIcons: Record<string, string> = {
        answer_correct: '✅',
        answer_wrong: '❌',
        hint_used: '💡',
        step_completed: '🎯',
        quest_started: '🚀',
        quest_completed: '🏆',
    }

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    if (!quest) {
        return <div className={`text-center py-12 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Квест не найден</div>
    }

    return (
        <div>
            <button
                onClick={() => router.push('/admin/quests')}
                className={`mb-6 flex items-center gap-2 text-sm transition-colors ${theme === 'dark' ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900'
                    }`}
            >
                <ArrowLeft className="w-4 h-4" /> Назад к квестам
            </button>

            <h1 className={`text-3xl font-bold mb-2 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                Ответы участников
            </h1>
            <p className={`mb-8 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                Квест: {quest.title} • {quest.sessions.length} сессий
            </p>

            {quest.sessions.length === 0 ? (
                <div className={`text-center py-12 rounded-2xl border ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                    }`}>
                    <Eye className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                    <p className={theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}>Пока никто не проходил этот квест</p>
                </div>
            ) : (
                <div className="space-y-4">
                    {quest.sessions.map((s) => {
                        const st = statusMap[s.status] || statusMap['active']
                        return (
                            <div key={s.id} className={`rounded-2xl border overflow-hidden ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                }`}>
                                <button
                                    onClick={() => setExpandedSession(expandedSession === s.id ? null : s.id)}
                                    className="w-full p-5 flex items-center gap-4 text-left"
                                >
                                    <img src={s.player.avatar || '/images/avatars/default_avatar.png'} alt="" className="w-10 h-10 rounded-full object-cover border border-violet-500/30" />
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <span className={`font-semibold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{s.player.login}</span>
                                            <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${st.color}`}>{st.label}</span>
                                        </div>
                                        <p className={`text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                            {new Date(s.startTime).toLocaleString('ru-RU')}
                                            {s.endTime && ` — ${new Date(s.endTime).toLocaleString('ru-RU')}`}
                                        </p>
                                    </div>
                                    <div className="text-right flex-shrink-0">
                                        <p className={`text-2xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{s.score}</p>
                                        <p className={`text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>баллов</p>
                                    </div>
                                </button>

                                {expandedSession === s.id && (
                                    <motion.div
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        className={`border-t p-5 ${theme === 'dark' ? 'border-white/10 bg-white/[0.02]' : 'border-gray-100 bg-gray-50/50'}`}
                                    >
                                        <h4 className={`font-semibold mb-4 text-sm ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>
                                            Хронология событий ({s.events.length})
                                        </h4>
                                        {s.events.length === 0 ? (
                                            <p className={`text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>Нет событий</p>
                                        ) : (
                                            <div className="space-y-2">
                                                {s.events.map((ev) => (
                                                    <div key={ev.id} className={`flex items-start gap-3 p-3 rounded-xl ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl' : 'bg-white'
                                                        }`}>
                                                        <span className="text-lg flex-shrink-0 mt-0.5">{eventIcons[ev.eventType] || '📌'}</span>
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center gap-2">
                                                                <span className={`font-medium text-sm ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                                                    {ev.eventType.replace(/_/g, ' ')}
                                                                </span>
                                                                {ev.step && (
                                                                    <span className={`text-xs px-2 py-0.5 rounded-full ${theme === 'dark' ? 'bg-violet-500/20 text-violet-400' : 'bg-purple-50 text-violet-600'
                                                                        }`}>
                                                                        Шаг {ev.step.stepOrder}: {ev.step.title}
                                                                    </span>
                                                                )}
                                                            </div>
                                                            {ev.eventData && (
                                                                <p className={`text-sm mt-1 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                                                    {ev.eventData}
                                                                </p>
                                                            )}
                                                            <p className={`text-xs mt-1 ${theme === 'dark' ? 'text-gray-600' : 'text-gray-400'}`}>
                                                                {new Date(ev.createdAt).toLocaleString('ru-RU')}
                                                            </p>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </motion.div>
                                )}
                            </div>
                        )
                    })}
                </div>
            )}
        </div>
    )
}
