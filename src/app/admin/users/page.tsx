'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useTheme } from '@/components/theme/theme-provider'
import { motion } from 'framer-motion'
import { Users, AlertTriangle, Search, Loader2, Download, Shield, ShieldOff, Ban } from 'lucide-react'

interface AdminUser {
    id: number
    login: string
    email: string
    avatar: string
    role: string
    totalScore: number
    createdAt: string
    warningCount: number
}

export default function AdminUsersPage() {
    const { theme } = useTheme()
    const { data: session } = useSession()
    const [users, setUsers] = useState<AdminUser[]>([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState('')
    const [warnUserId, setWarnUserId] = useState<number | null>(null)
    const [warnReason, setWarnReason] = useState('')

    useEffect(() => {
        fetch('/api/admin/users')
            .then((r) => r.json())
            .then(setUsers)
            .catch(console.error)
            .finally(() => setLoading(false))
    }, [])

    const adminId = (session?.user as Record<string, unknown>)?.id

    const warnUser = async () => {
        if (!warnReason.trim() || !warnUserId) return
        try {
            await fetch('/api/admin/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'warn', userId: warnUserId, reason: warnReason, adminId }),
            })
            setWarnUserId(null)
            setWarnReason('')
            // Reload
            const res = await fetch('/api/admin/users')
            setUsers(await res.json())
        } catch {
            console.error('Failed to warn user')
        }
    }

    const filtered = users.filter((u) =>
        u.login.toLowerCase().includes(search.toLowerCase()) ||
        u.email.toLowerCase().includes(search.toLowerCase())
    )

    const changeRole = async (userId: number, role: string) => {
        if (!confirm(`Изменить роль пользователя на ${role}?`)) return
        try {
            await fetch('/api/admin/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_role', userId, adminId, role }),
            })
            const res = await fetch('/api/admin/users')
            setUsers(await res.json())
        } catch {
            console.error('Failed to change role')
        }
    }

    const toggleBan = async (userId: number) => {
        if (!confirm('Изменить статус блокировки пользователя?')) return
        try {
            await fetch('/api/admin/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_ban', userId, adminId }),
            })
            const res = await fetch('/api/admin/users')
            setUsers(await res.json())
        } catch {
            console.error('Failed to toggle ban')
        }
    }

    const exportCSV = () => {
        const headers = ['ID', 'Логин', 'Email', 'Роль', 'Баллы', 'Предупреждения', 'Зарегистрирован']
        const csvContent = [
            headers.join(','),
            ...filtered.map(u => [
                u.id,
                `"${u.login}"`,
                `"${u.email}"`,
                u.role,
                u.totalScore,
                u.warningCount,
                `"${new Date(u.createdAt).toLocaleDateString('ru-RU')}"`
            ].join(','))
        ].join('\n')

        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `users_export_${new Date().toISOString().split('T')[0]}.csv`
        link.click()
    }

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    return (
        <div>
            <div className="flex items-center justify-between mb-8">
                <div>
                    <h1 className={`text-3xl font-bold mb-2 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                        Пользователи
                    </h1>
                    <span className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                        Всего: {users.length}
                    </span>
                </div>
                <button
                    onClick={exportCSV}
                    className="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white font-medium hover:bg-purple-700 transition-colors"
                >
                    <Download className="w-4 h-4" /> Экспорт CSV
                </button>
            </div>

            {/* Search */}
            <div className="relative mb-6">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Поиск по логину или email..."
                    className={`w-full rounded-xl px-10 py-3 border focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark'
                        ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white placeholder-white/30'
                        : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5 text-gray-900'
                        }`}
                />
            </div>

            {/* Users table */}
            <div className={`rounded-2xl border overflow-hidden ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                }`}>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead>
                            <tr className={`border-b ${theme === 'dark' ? 'border-white/10' : 'border-gray-100'}`}>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Пользователь</th>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Email</th>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Роль</th>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Баллы</th>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>⚠️</th>
                                <th className={`text-left px-5 py-4 text-sm font-medium ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.map((user) => (
                                <tr key={user.id} className={`border-b last:border-0 ${theme === 'dark' ? 'border-white/5 hover:bg-white/5 backdrop-blur-xl' : 'border-gray-50 hover:bg-gray-50'
                                    }`}>
                                    <td className="px-5 py-4">
                                        <div className="flex items-center gap-3">
                                            <img src={user.avatar || '/images/avatars/default_avatar.png'} alt="" className="w-9 h-9 rounded-full object-cover" />
                                            <span className={`font-medium ${theme === 'dark' ? 'text-white' : 'text-gray-900'} ${user.role === 'BANNED' ? 'line-through opacity-50' : ''}`}>
                                                {user.login}
                                            </span>
                                        </div>
                                    </td>
                                    <td className={`px-5 py-4 text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{user.email}</td>
                                    <td className="px-5 py-4">
                                        <span className={`px-2 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors ${user.role === 'ADMIN' ? 'bg-violet-500/20 text-violet-400 hover:bg-violet-500/30' :
                                                user.role === 'BANNED' ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' :
                                                    'bg-fuchsia-500/20 text-blue-400 hover:bg-fuchsia-500/30'
                                            }`}
                                            onClick={() => changeRole(user.id, user.role === 'ADMIN' ? 'USER' : 'ADMIN')}
                                            title="Изменить роль"
                                        >
                                            {user.role}
                                        </span>
                                    </td>
                                    <td className={`px-5 py-4 font-medium ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{user.totalScore}</td>
                                    <td className="px-5 py-4">
                                        {user.warningCount > 0 && (
                                            <span className="text-yellow-500 font-medium">{user.warningCount}</span>
                                        )}
                                    </td>
                                    <td className="px-5 py-4">
                                        <div className="flex items-center gap-3">
                                            <button
                                                onClick={() => toggleBan(user.id)}
                                                className={`text-sm transition-colors flex items-center gap-1 ${user.role === 'BANNED' ? 'text-green-500 hover:text-green-400' : 'text-red-500 hover:text-red-400'
                                                    }`}
                                                title={user.role === 'BANNED' ? "Разблокировать" : "Заблокировать"}
                                            >
                                                <Ban className="w-4 h-4" />
                                            </button>
                                            <button
                                                onClick={() => setWarnUserId(user.id)}
                                                className="text-sm text-yellow-500 hover:text-yellow-400 transition-colors flex items-center gap-1"
                                                title="Выдать предупреждение"
                                            >
                                                <AlertTriangle className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Warning modal */}
            {warnUserId && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <motion.div
                        initial={{ scale: 0.9, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        className={`w-full max-w-md rounded-2xl border p-6 ${theme === 'dark' ? 'bg-black/40 backdrop-blur-2xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                            }`}
                    >
                        <h3 className={`text-lg font-bold mb-4 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                            Предупреждение пользователю
                        </h3>
                        <textarea
                            value={warnReason}
                            onChange={(e) => setWarnReason(e.target.value)}
                            placeholder="Причина предупреждения..."
                            rows={3}
                            className={`w-full rounded-xl px-4 py-3 border mb-4 resize-none focus:outline-none focus:ring-2 focus:ring-yellow-500/30 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white placeholder-white/30' : 'bg-gray-50 border-black/5'
                                }`}
                        />
                        <div className="flex gap-3">
                            <button
                                onClick={() => { setWarnUserId(null); setWarnReason('') }}
                                className={`flex-1 py-2.5 rounded-xl border ${theme === 'dark' ? 'border-white/20 text-white' : 'border-black/5 text-gray-700'
                                    }`}
                            >
                                Отмена
                            </button>
                            <button
                                onClick={warnUser}
                                disabled={!warnReason.trim()}
                                className="flex-1 py-2.5 rounded-xl bg-yellow-500 text-black font-medium disabled:opacity-50"
                            >
                                Предупредить
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </div>
    )
}
