'use client'

import { useState, useEffect } from 'react'
import { useTheme } from '@/components/theme/theme-provider'
import { motion, AnimatePresence } from 'framer-motion'
import { FileText, Plus, Trash2, Edit2, ChevronDown, ChevronUp, Loader2, Save, X, FolderPlus, FilePlus } from 'lucide-react'

interface ContentItem {
    id: number
    title: string
    text: string
    image: string
    sortOrder: number
}

interface SubSection {
    id: number
    name: string
    sortOrder: number
    _count: { items: number }
}

interface Section {
    id: number
    name: string
    description: string
    image: string
    sortOrder: number
    subSections: SubSection[]
}

export default function AdminContentPage() {
    const { theme } = useTheme()
    const [sections, setSections] = useState<Section[]>([])
    const [loading, setLoading] = useState(true)
    const [expandedSection, setExpandedSection] = useState<number | null>(null)

    // Section form
    const [showSectionForm, setShowSectionForm] = useState(false)
    const [editSection, setEditSection] = useState<Section | null>(null)
    const [sectionForm, setSectionForm] = useState({ name: '', description: '', image: '', sortOrder: 0 })

    // Sub-section form
    const [showSubForm, setShowSubForm] = useState(false)
    const [subFormSectionId, setSubFormSectionId] = useState<number | null>(null)
    const [subForm, setSubForm] = useState({ name: '', sortOrder: 0 })

    useEffect(() => { loadSections() }, [])

    const loadSections = async () => {
        try { setSections(await (await fetch('/api/admin/sections')).json()) }
        catch { } finally { setLoading(false) }
    }

    const submitSection = async () => {
        try {
            await fetch('/api/admin/sections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: editSection ? 'update_section' : 'create_section',
                    id: editSection?.id,
                    ...sectionForm,
                }),
            })
            setShowSectionForm(false)
            setEditSection(null)
            setSectionForm({ name: '', description: '', image: '', sortOrder: 0 })
            loadSections()
        } catch { }
    }

    const deleteSection = async (id: number) => {
        if (!confirm('Удалить раздел и все его содержимое?')) return
        try {
            await fetch('/api/admin/sections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_section', id }),
            })
            loadSections()
        } catch { }
    }

    const submitSubSection = async () => {
        if (!subFormSectionId) return
        try {
            await fetch('/api/admin/sections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create_subsection', sectionId: subFormSectionId, ...subForm }),
            })
            setShowSubForm(false)
            setSubForm({ name: '', sortOrder: 0 })
            loadSections()
        } catch { }
    }

    const deleteSubSection = async (id: number) => {
        if (!confirm('Удалить подраздел?')) return
        try {
            await fetch('/api/admin/sections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_subsection', id }),
            })
            loadSections()
        } catch { }
    }

    const openEditSection = (s: Section) => {
        setEditSection(s)
        setSectionForm({ name: s.name, description: s.description, image: s.image, sortOrder: s.sortOrder })
        setShowSectionForm(true)
    }

    const openNewSubSection = (sectionId: number) => {
        setSubFormSectionId(sectionId)
        setSubForm({ name: '', sortOrder: 0 })
        setShowSubForm(true)
    }

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    return (
        <div>
            <div className="flex items-center justify-between mb-8">
                <h1 className={`text-3xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>Контент</h1>
                <button
                    onClick={() => { setShowSectionForm(true); setEditSection(null); setSectionForm({ name: '', description: '', image: '', sortOrder: 0 }) }}
                    className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium flex items-center gap-2"
                >
                    <Plus className="w-5 h-5" /> Добавить раздел
                </button>
            </div>

            <div className="space-y-4">
                {sections.map((section) => (
                    <div key={section.id} className={`rounded-2xl border overflow-hidden ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                        }`}>
                        <div className="p-5 flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                {section.image && (
                                    <img src={section.image} alt="" className="w-14 h-14 rounded-xl object-cover" />
                                )}
                                <div>
                                    <h3 className={`font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{section.name}</h3>
                                    <p className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                        {section.subSections.length} подразделов
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <button onClick={() => openNewSubSection(section.id)} title="Добавить подраздел" className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                    }`}>
                                    <FolderPlus className="w-5 h-5" />
                                </button>
                                <button onClick={() => openEditSection(section)} className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                    }`}>
                                    <Edit2 className="w-5 h-5" />
                                </button>
                                <button onClick={() => setExpandedSection(expandedSection === section.id ? null : section.id)} className={`p-2 rounded-lg transition-colors ${theme === 'dark' ? 'hover:bg-white/10 text-gray-400' : 'hover:bg-gray-100 text-gray-500'
                                    }`}>
                                    {expandedSection === section.id ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
                                </button>
                                <button onClick={() => deleteSection(section.id)} className="p-2 rounded-lg hover:bg-red-500/10 text-red-400">
                                    <Trash2 className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <AnimatePresence>
                            {expandedSection === section.id && (
                                <motion.div
                                    initial={{ height: 0, opacity: 0 }}
                                    animate={{ height: 'auto', opacity: 1 }}
                                    exit={{ height: 0, opacity: 0 }}
                                    className={`border-t overflow-hidden ${theme === 'dark' ? 'border-white/10 bg-white/[0.02]' : 'border-gray-100 bg-gray-50/50'}`}
                                >
                                    <div className="p-5 space-y-3">
                                        {section.subSections.length === 0 ? (
                                            <p className={`text-sm text-center py-4 ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>Нет подразделов</p>
                                        ) : (
                                            section.subSections.map(sub => (
                                                <div key={sub.id} className={`flex items-center justify-between p-3 rounded-xl ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl' : 'bg-white'
                                                    }`}>
                                                    <div>
                                                        <span className={`font-medium ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{sub.name}</span>
                                                        <span className={`ml-2 text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                                            {sub._count.items} элементов
                                                        </span>
                                                    </div>
                                                    <button onClick={() => deleteSubSection(sub.id)} className="p-1.5 rounded-lg hover:bg-red-500/10 text-red-400">
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>
                ))}
            </div>

            {/* Section form modal */}
            <AnimatePresence>
                {showSectionForm && (
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
                                    {editSection ? 'Редактировать раздел' : 'Новый раздел'}
                                </h3>
                                <button onClick={() => setShowSectionForm(false)} className={`p-1 rounded-lg ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <div className="space-y-4">
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Название</label>
                                    <input value={sectionForm.name} onChange={(e) => setSectionForm({ ...sectionForm, name: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Описание</label>
                                    <textarea value={sectionForm.description} onChange={(e) => setSectionForm({ ...sectionForm, description: e.target.value })} rows={3}
                                        className={`w-full rounded-xl px-4 py-3 border resize-none focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} />
                                </div>
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Путь к изображению</label>
                                    <input value={sectionForm.image} onChange={(e) => setSectionForm({ ...sectionForm, image: e.target.value })}
                                        placeholder="/images/museum/example.jpg"
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} />
                                </div>
                            </div>
                            <div className="flex gap-3 mt-6">
                                <button onClick={() => setShowSectionForm(false)} className={`flex-1 py-3 rounded-xl border ${theme === 'dark' ? 'border-white/20 text-white' : 'border-black/5 text-gray-700'}`}>Отмена</button>
                                <button onClick={submitSection} disabled={!sectionForm.name.trim()} className="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                                    <Save className="w-5 h-5" /> Сохранить
                                </button>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>

            {/* Sub-section form modal */}
            <AnimatePresence>
                {showSubForm && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className={`w-full max-w-md rounded-2xl border p-6 ${theme === 'dark' ? 'bg-black/40 backdrop-blur-2xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                }`}
                        >
                            <h3 className={`text-xl font-bold mb-4 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>Новый подраздел</h3>
                            <div className="space-y-4">
                                <div>
                                    <label className={`block text-sm mb-1.5 font-medium ${theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}`}>Название</label>
                                    <input value={subForm.name} onChange={(e) => setSubForm({ ...subForm, name: e.target.value })}
                                        className={`w-full rounded-xl px-4 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white' : 'bg-gray-50 border-black/5'
                                            }`} />
                                </div>
                            </div>
                            <div className="flex gap-3 mt-6">
                                <button onClick={() => setShowSubForm(false)} className={`flex-1 py-3 rounded-xl border ${theme === 'dark' ? 'border-white/20 text-white' : 'border-black/5 text-gray-700'}`}>Отмена</button>
                                <button onClick={submitSubSection} disabled={!subForm.name.trim()} className="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-medium disabled:opacity-50 flex items-center justify-center gap-2">
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
