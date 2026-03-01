'use client'

import { useState, useRef, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { useTheme } from '@/components/theme/theme-provider'
import { Search, X, Loader2, LayoutGrid, Gamepad2, Package } from 'lucide-react'
import { motion, AnimatePresence } from 'framer-motion'

interface SearchResult {
    sections: { id: number; name: string; description: string }[]
    quests: { id: number; title: string; description: string }[]
    exhibits: { id: number; name: string; description: string }[]
}

export function SearchDialog() {
    const { theme } = useTheme()
    const router = useRouter()
    const [open, setOpen] = useState(false)
    const [query, setQuery] = useState('')
    const [results, setResults] = useState<SearchResult | null>(null)
    const [loading, setLoading] = useState(false)
    const inputRef = useRef<HTMLInputElement>(null)
    const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null)

    useEffect(() => {
        if (open && inputRef.current) inputRef.current.focus()
    }, [open])

    useEffect(() => {
        const handler = (e: KeyboardEvent) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault()
                setOpen(true)
            }
            if (e.key === 'Escape') setOpen(false)
        }
        window.addEventListener('keydown', handler)
        return () => window.removeEventListener('keydown', handler)
    }, [])

    const search = (q: string) => {
        setQuery(q)
        if (timeoutRef.current) clearTimeout(timeoutRef.current)
        if (q.trim().length < 2) { setResults(null); return }

        timeoutRef.current = setTimeout(async () => {
            setLoading(true)
            try {
                const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`)
                setResults(await res.json())
            } catch { }
            setLoading(false)
        }, 300)
    }

    const navigate = (path: string) => {
        setOpen(false)
        setQuery('')
        setResults(null)
        router.push(path)
    }

    const totalResults = results
        ? results.sections.length + results.quests.length + results.exhibits.length
        : 0

    return (
        <>
            <button
                onClick={() => setOpen(true)}
                className={`flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-colors ${theme === 'dark'
                        ? 'bg-white/5 backdrop-blur-xl text-gray-400 hover:bg-white/10 border border-white/10'
                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-black/5'
                    }`}
            >
                <Search className="w-4 h-4" />
                <span className="hidden md:inline">Поиск...</span>
                <kbd className={`hidden md:inline text-xs px-1.5 py-0.5 rounded ${theme === 'dark' ? 'bg-white/10 text-gray-500' : 'bg-gray-200 text-gray-400'
                    }`}>⌘K</kbd>
            </button>

            <AnimatePresence>
                {open && (
                    <div className="fixed inset-0 z-[100]">
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                            onClick={() => setOpen(false)}
                        />
                        <div className="relative flex justify-center pt-[15vh] px-4">
                            <motion.div
                                initial={{ opacity: 0, scale: 0.95, y: -10 }}
                                animate={{ opacity: 1, scale: 1, y: 0 }}
                                exit={{ opacity: 0, scale: 0.95, y: -10 }}
                                className={`w-full max-w-xl rounded-2xl border overflow-hidden shadow-2xl ${theme === 'dark' ? 'bg-black/40 backdrop-blur-2xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                                    }`}
                            >
                                {/* Search input */}
                                <div className={`flex items-center gap-3 px-5 py-4 border-b ${theme === 'dark' ? 'border-white/10' : 'border-gray-100'
                                    }`}>
                                    <Search className="w-5 h-5 text-violet-500" />
                                    <input
                                        ref={inputRef}
                                        value={query}
                                        onChange={(e) => search(e.target.value)}
                                        placeholder="Поиск по музею..."
                                        className={`flex-1 bg-transparent outline-none text-lg ${theme === 'dark' ? 'text-white placeholder-gray-500' : 'text-gray-900 placeholder-gray-400'
                                            }`}
                                    />
                                    {loading && <Loader2 className="w-5 h-5 animate-spin text-violet-500" />}
                                    <button onClick={() => setOpen(false)} className="p-1">
                                        <X className={`w-5 h-5 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`} />
                                    </button>
                                </div>

                                {/* Results */}
                                <div className="max-h-[50vh] overflow-y-auto p-2">
                                    {query.length >= 2 && results && totalResults === 0 && (
                                        <p className={`text-center py-8 ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                            Ничего не найдено
                                        </p>
                                    )}

                                    {results && results.sections.length > 0 && (
                                        <div className="mb-2">
                                            <p className={`px-3 py-1.5 text-xs font-semibold uppercase ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'
                                                }`}>Разделы</p>
                                            {results.sections.map(s => (
                                                <button key={s.id} onClick={() => navigate(`/sections/${s.id}`)}
                                                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors ${theme === 'dark' ? 'hover:bg-white/5 backdrop-blur-xl text-white' : 'hover:bg-gray-50 text-gray-900'
                                                        }`}>
                                                    <LayoutGrid className="w-5 h-5 text-violet-500 flex-shrink-0" />
                                                    <div className="min-w-0">
                                                        <p className="font-medium truncate">{s.name}</p>
                                                        <p className={`text-xs truncate ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>{s.description}</p>
                                                    </div>
                                                </button>
                                            ))}
                                        </div>
                                    )}

                                    {results && results.quests.length > 0 && (
                                        <div className="mb-2">
                                            <p className={`px-3 py-1.5 text-xs font-semibold uppercase ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'
                                                }`}>Квесты</p>
                                            {results.quests.map(q => (
                                                <button key={q.id} onClick={() => navigate(`/quests/${q.id}/play`)}
                                                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors ${theme === 'dark' ? 'hover:bg-white/5 backdrop-blur-xl text-white' : 'hover:bg-gray-50 text-gray-900'
                                                        }`}>
                                                    <Gamepad2 className="w-5 h-5 text-fuchsia-500 flex-shrink-0" />
                                                    <div className="min-w-0">
                                                        <p className="font-medium truncate">{q.title}</p>
                                                        <p className={`text-xs truncate ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>{q.description}</p>
                                                    </div>
                                                </button>
                                            ))}
                                        </div>
                                    )}

                                    {results && results.exhibits.length > 0 && (
                                        <div>
                                            <p className={`px-3 py-1.5 text-xs font-semibold uppercase ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'
                                                }`}>Экспонаты</p>
                                            {results.exhibits.map(e => (
                                                <button key={e.id} onClick={() => navigate('/sections')}
                                                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors ${theme === 'dark' ? 'hover:bg-white/5 backdrop-blur-xl text-white' : 'hover:bg-gray-50 text-gray-900'
                                                        }`}>
                                                    <Package className="w-5 h-5 text-green-500 flex-shrink-0" />
                                                    <div className="min-w-0">
                                                        <p className="font-medium truncate">{e.name}</p>
                                                        <p className={`text-xs truncate ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>{e.description}</p>
                                                    </div>
                                                </button>
                                            ))}
                                        </div>
                                    )}

                                    {!results && query.length < 2 && (
                                        <p className={`text-center py-8 text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                            Введите минимум 2 символа для поиска
                                        </p>
                                    )}
                                </div>
                            </motion.div>
                        </div>
                    </div>
                )}
            </AnimatePresence>
        </>
    )
}
