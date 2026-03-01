'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useTheme } from '@/components/theme/theme-provider'
import { motion, AnimatePresence } from 'framer-motion'
import { Gamepad2, Plus, Trash2, Edit2, Loader2, Power, Save, X, ChevronDown, ChevronUp, Eye, Hash, Award } from 'lucide-react'
import Link from 'next/link'

interface Step {
    id: number
    stepOrder: number
    title: string
    description: string
    hintText: string | null
    stepScore: number
    maxAttempts: number
}

interface QuestAdmin {
    id: number
    title: string
    description: string
    durationMinutes: number
    difficultyLevel: string
    isActive: boolean
    stepCount: number
    sessionCount: number
    completedCount: number
}

export default function AdminQuestsPage() {
    const { theme } = useTheme()
    const { data: session } = useSession()
    const [quests, setQuests] = useState<QuestAdmin[]>([])
    const [loading, setLoading] = useState(true)
    const [showForm, setShowForm] = useState(false)
    const [editQuest, setEditQuest] = useState<QuestAdmin | null>(null)
    const [form, setForm] = useState({ title: '', description: '', durationMinutes: 30, difficultyLevel: 'medium' })

    // Step management
    const [expandedQuest, setExpandedQuest] = useState<number | null>(null)
    const [questSteps, setQuestSteps] = useState<Step[]>([])
    const [loadingSteps, setLoadingSteps] = useState(false)
    const [showStepForm, setShowStepForm] = useState(false)
    const [editStep, setEditStep] = useState<Step | null>(null)
    const [stepForm, setStepForm] = useState({ title: '', description: '', solution: '', hintText: '', stepScore: 100, maxAttempts: 3, stepOrder: 1 })

    const adminId = (session?.user as Record<string, unknown>)?.id as string | undefined

    useEffect(() => { loadQuests() }, [])

    const loadQuests = async () => {
        try { setQuests(await (await fetch('/api/admin/quests')).json()) }
        catch { } finally { setLoading(false) }
    }

    const loadSteps = async (questId: number) => {
        setLoadingSteps(true)
        try {
            const data = await (await fetch(`/api/admin/quests/${questId}`)).json()
            setQuestSteps(data.steps || [])
        } catch { } finally { setLoadingSteps(false) }
    }

    const toggleExpand = async (questId: number) => {
        if (expandedQuest === questId) {
            setExpandedQuest(null)
        } else {
            setExpandedQuest(questId)
            await loadSteps(questId)
        }
    }

    const submitQuest = async () => {
        try {
            await fetch('/api/admin/quests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: editQuest ? 'update' : 'create',
                    id: editQuest?.id,
                    ...form,
                    createdBy: adminId ? parseInt(adminId) : undefined,
                }),
            })
            setShowForm(false)
            setEditQuest(null)
            setForm({ title: '', description: '', durationMinutes: 30, difficultyLevel: 'medium' })
            loadQuests()
        } catch { }
    }

    const deleteQuest = async (id: number) => {
        if (!confirm('Удалить квест и все его шаги?')) return
        try {
            await fetch('/api/admin/quests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id }),
            })
            if (expandedQuest === id) setExpandedQuest(null)
            loadQuests()
        } catch { }
    }

    const toggleQuest = async (id: number) => {
        try {
            await fetch('/api/admin/quests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle', id }),
            })
            loadQuests()
        } catch { }
    }

    const openEditQuest = (quest: QuestAdmin) => {
        setEditQuest(quest)
        setForm({ title: quest.title, description: quest.description, durationMinutes: quest.durationMinutes, difficultyLevel: quest.difficultyLevel })
        setShowForm(true)
    }

    // Step actions
    const submitStep = async () => {
        if (!expandedQuest) return
        try {
            await fetch('/api/admin/quests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: editStep ? 'update_step' : 'add_step',
                    questId: expandedQuest,
                    stepId: editStep?.id,
                    ...stepForm,
                }),
            })
            setShowStepForm(false)
            setEditStep(null)
            setStepForm({ title: '', description: '', solution: '', hintText: '', stepScore: 100, maxAttempts: 3, stepOrder: questSteps.length + 1 })
            loadSteps(expandedQuest)
            loadQuests()
        } catch { }
    }

    const deleteStep = async (stepId: number) => {
        if (!expandedQuest || !confirm('Удалить шаг?')) return
        try {
            await fetch('/api/admin/quests', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_step', stepId }),
            })
            loadSteps(expandedQuest)
            loadQuests()
        } catch { }
    }

    const openEditStep = (step: Step) => {
        setEditStep(step)
        setStepForm({
            title: step.title,
            description: step.description,
            solution: '',
            hintText: step.hintText || '',
            stepScore: step.stepScore,
            maxAttempts: step.maxAttempts,
            stepOrder: step.stepOrder,
        })
        setShowStepForm(true)
    }

    const openNewStep = () => {
        setEditStep(null)
        setStepForm({ title: '', description: '', solution: '', hintText: '', stepScore: 100, maxAttempts: 3, stepOrder: questSteps.length + 1 })
        setShowStepForm(true)
    }

    const diffMap: Record<string, string> = { easy: 'Лёгкий', medium: 'Средний', hard: 'Сложный' }

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    return (
        <div>
            <div className="flex items-center justify-between mb-8">
                <h1 className={`text-3xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>Квесты</h1>
                <button
                    onClick={() => { setShowForm(true); setEditQuest(null); setForm({ title: '', description: '', durationMinutes: 30, difficultyLevel: 'medium' }) }}
                    className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium flex items-center gap-2 hover:from-purple-700 hover:to-blue-700 transition-all"
                >
                    <Plus className="w-5 h-5" /> Создать квест
                </button>
            </div>

            <div className="space-y-4">
                {quests.map((quest) => (
                    <div key={quest.id} className={`rounded-2xl border overflow-hidden ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                        }`}>
                        {/* Quest header */}
                        <div className="p-5">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <div className="flex items-center gap-3 mb-2">
                                        <h3 className={`text-lg font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{quest.title}</h3>
                                        <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${quest.isActive ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'
                                            }`}>{quest.isActive ? 'Активен' : 'Неактивен'}</span>
                                        <span className={`px-2 py-0.5 rounded-full text-xs ${theme === 'dark' ? 'bg-white/10 text-gray-300' : 'bg-gray-100 text-gray-600'}`}>
                                            {diffMap[quest.difficultyLevel] || quest.difficultyLevel}
                                        </span>
                                    </div>
                                    <p className={`text-sm mb-3 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{quest.description}</p>
                                    <div className={`flex items-center gap-4 text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                        <span>⏱ {quest.durationMinutes} мин</span>
                                        <span>📝 {quest.stepCount} этапов</span>
                                        <span>👥 {quest.sessionCount} сессий</span>
                                        <span>✅ {quest.completedCount} прохождений</span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2 ml-4">
                                    <Link href={`/admin/quests/${quest.id}/sessions`}>
                                        <button title="Ответы участников" className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                            }`}>
                                            <Eye className="w-5 h-5" />
                                        </button>
                                    </Link>
                                    <button onClick={() => toggleQuest(quest.id)} title={quest.isActive ? 'Деактивировать' : 'Активировать'} className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                        }`}>
                                        <Power className="w-5 h-5" />
                                    </button>
                                    <button onClick={() => openEditQuest(quest)} className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                        }`}>
                                        <Edit2 className="w-5 h-5" />
                                    </button>
                                    <button onClick={() => deleteQuest(quest.id)} className="p-2 rounded-lg hover:bg-red-500/10 text-red-400 transition-colors">
                                        <Trash2 className="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            {/* Expand steps */}
                            <button
                                onClick={() => toggleExpand(quest.id)}
                                className={`mt-3 text-sm flex items-center gap-1 transition-colors ${theme === 'dark' ? 'text-violet-400 hover:text-violet-300' : 'text-violet-600 hover:text-purple-700'
                                    }`}
                            >
                                {expandedQuest === quest.id ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                                {expandedQuest === quest.id ? 'Скрыть шаги' : 'Показать шаги'}
                            </button>
                        </div>

                        {/* Steps panel */}
                        <AnimatePresence>
                            {expandedQuest === quest.id && (
                                <motion.div
                                    initial={{ height: 0, opacity: 0 }}
                                    animate={{ height: 'auto', opacity: 1 }}
                                    exit={{ height: 0, opacity: 0 }}
                                    className={`border-t overflow-hidden ${theme === 'dark' ? 'border-white/10 bg-white/[0.02]' : 'border-gray-100 bg-gray-50/50'}`}
                                >
                                    <div className="p-5">
                                        {loadingSteps ? (
                                            <div className="flex items-center justify-center py-8">
                                                <Loader2 className="w-6 h-6 animate-spin text-violet-500" />
                                            </div>
                                        ) : (
                                            <>
                                                <div className="flex items-center justify-between mb-4">
                                                    <h4 className={`font-semibold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                                        Шаги квеста ({questSteps.length})
                                                    </h4>
                                                    <button
                                                        onClick={openNewStep}
                                                        className="px-3 py-1.5 rounded-lg bg-violet-600 text-white text-sm font-medium flex items-center gap-1"
                                                    >
                                                        <Plus className="w-4 h-4" /> Добавить шаг
                                                    </button>
                                                </div>

                                                {questSteps.length === 0 ? (
                                                    <p className={`text-center py-6 text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                                        Нет шагов. Добавьте первый шаг квеста.
                                                    </p>
                                                ) : (
                                                    <div className="space-y-3">
                                                        {questSteps.map((step) => (
                                                            <div key={step.id} className={`rounded-xl p-4 border ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                                                }`}>
                                                                <div className="flex items-start justify-between">
                                                                    <div className="flex-1">
                                                                        <div className="flex items-center gap-2 mb-1">
                                                                            <span className={`w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold ${theme === 'dark' ? 'bg-violet-500/20 text-violet-400' : 'bg-purple-50 text-violet-600'
                                                                                }`}>
                                                                                {step.stepOrder}
                                                                            </span>
                                                                            <span className={`font-semibold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                                                                {step.title}
                                                                            </span>
                                                                        </div>
                                                                        <p className={`text-sm mt-1 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                                                            {step.description}
                                                                        </p>
                                                                        <div className={`flex items-center gap-3 mt-2 text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                                                            <span className="flex items-center gap-1"><Award className="w-3 h-3" /> {step.stepScore} баллов</span>
                                                                            <span className="flex items-center gap-1"><Hash className="w-3 h-3" /> {step.maxAttempts} попытки</span>
                                                                            {step.hintText && <span>💡 Есть подсказка</span>}
                                                                        </div>
                                                                    </div>
                                                                    <div className="flex gap-1 ml-3">
                                                                        <button onClick={() => openEditStep(step)} className={`p-1.5 rounded-lg ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                                                            }`}>
                                                                            <Edit2 className="w-4 h-4" />
                                                                        </button>
                                                                        <button onClick={() => deleteStep(step.id)} className="p-1.5 rounded-lg hover:bg-red-500/10 text-red-400">
                                                                            <Trash2 className="w-4 h-4" />
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </>
                                        )}
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>
                ))}
            </div>

            {/* Quest form modal */}
            <AnimatePresence>
                {showForm && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className={`w-full max-w-lg rounded-2xl border p-6 ${theme === 'dark' ? 'bg-black/40 backdrop-blur-2xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                }`}
                        >
                            <div className="flex items-center justify-between mb-6">
                                <h3 className={`text-xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                    {editQuest ? 'Редактировать квест' : 'Новый квест'}
                                </h3>
                                <button onClick={() => setShowForm(false)} className={`p-1 rounded-lg ${theme === 'dark' ? 'text-gray-400 hover:bg-white/10' : 'text-gray-500 hover:bg-gray-100'}`}>
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <div className="space-y-4">
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Название *</label>
                                    <input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Например: История колледжа" />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Описание</label>
                                    <textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={3}
                                        className={`w-full rounded-xl px-4 py-3 border resize-none focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Краткое описание квеста..." />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Время (мин)</label>
                                        <input type="number" value={form.durationMinutes} onChange={(e) => setForm({ ...form, durationMinutes: parseInt(e.target.value) || 30 })}
                                            className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                                }`} />
                                    </div>
                                    <div>
                                        <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Сложность</label>
                                        <select value={form.difficultyLevel} onChange={(e) => setForm({ ...form, difficultyLevel: e.target.value })}
                                            className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                                }`}>
                                            <option value="easy">Лёгкий</option>
                                            <option value="medium">Средний</option>
                                            <option value="hard">Сложный</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div className="flex gap-3 mt-6">
                                <button onClick={() => setShowForm(false)} className={`flex-1 py-3 rounded-xl border ${theme === 'dark' ? 'border-white/20 text-white' : 'border-black/5 text-gray-700'
                                    }`}>Отмена</button>
                                <button onClick={submitQuest} disabled={!form.title.trim()} className="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                                    <Save className="w-5 h-5" /> Сохранить
                                </button>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>

            {/* Step form modal */}
            <AnimatePresence>
                {showStepForm && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className={`w-full max-w-lg rounded-2xl border p-6 max-h-[85vh] overflow-y-auto ${theme === 'dark' ? 'bg-black/40 backdrop-blur-2xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                }`}
                        >
                            <div className="flex items-center justify-between mb-6">
                                <h3 className={`text-xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                    {editStep ? 'Редактировать шаг' : 'Новый шаг'}
                                </h3>
                                <button onClick={() => setShowStepForm(false)} className={`p-1 rounded-lg ${theme === 'dark' ? 'text-gray-400 hover:bg-white/10' : 'text-gray-500 hover:bg-gray-100'}`}>
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <div className="space-y-4">
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Название шага *</label>
                                    <input value={stepForm.title} onChange={(e) => setStepForm({ ...stepForm, title: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Например: Найдите основателя колледжа" />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Описание / Вопрос *</label>
                                    <textarea value={stepForm.description} onChange={(e) => setStepForm({ ...stepForm, description: e.target.value })} rows={3}
                                        className={`w-full rounded-xl px-4 py-3 border resize-none focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Подробное описание задания или вопрос..." />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>
                                        Правильный ответ * {editStep && <span className="text-gray-500 font-normal">(оставьте пустым чтобы не менять)</span>}
                                    </label>
                                    <input value={stepForm.solution} onChange={(e) => setStepForm({ ...stepForm, solution: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Точный ответ (регистр не учитывается)" />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Подсказка</label>
                                    <input value={stepForm.hintText} onChange={(e) => setStepForm({ ...stepForm, hintText: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} placeholder="Необязательная подсказка (-20% баллов)" />
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Порядок</label>
                                        <input type="number" value={stepForm.stepOrder} onChange={(e) => setStepForm({ ...stepForm, stepOrder: parseInt(e.target.value) || 1 })}
                                            className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                                }`} />
                                    </div>
                                    <div>
                                        <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Баллы</label>
                                        <input type="number" value={stepForm.stepScore} onChange={(e) => setStepForm({ ...stepForm, stepScore: parseInt(e.target.value) || 100 })}
                                            className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                                }`} />
                                    </div>
                                    <div>
                                        <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Попытки</label>
                                        <input type="number" value={stepForm.maxAttempts} onChange={(e) => setStepForm({ ...stepForm, maxAttempts: parseInt(e.target.value) || 3 })}
                                            className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                                }`} />
                                    </div>
                                </div>
                            </div>
                            <div className="flex gap-3 mt-6">
                                <button onClick={() => setShowStepForm(false)} className={`flex-1 py-3 rounded-xl border ${theme === 'dark' ? 'border-white/20 text-white' : 'border-black/5 text-gray-700'
                                    }`}>Отмена</button>
                                <button onClick={submitStep} disabled={!stepForm.title.trim() || (!editStep && !stepForm.solution.trim())} className="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                                    <Save className="w-5 h-5" /> Сохранить
                                </button>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </div>
    )
}
