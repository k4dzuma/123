'use client'

import { useState, useEffect } from 'react'
import { useTheme } from '@/components/theme/theme-provider'
import { Loader2, ScrollText } from 'lucide-react'

interface LogEntry {
    id: number
    action: string
    details: string | null
    ipAddress: string
    createdAt: string
    user: { login: string }
}

export default function AdminLogsPage() {
    const { theme } = useTheme()
    const [logs, setLogs] = useState<LogEntry[]>([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetch('/api/admin/logs')
            .then((r) => r.json())
            .then(setLogs)
            .catch(console.error)
            .finally(() => setLoading(false))
    }, [])

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    return (
        <div>
            <h1 className={`text-3xl font-bold mb-8 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                Журнал действий
            </h1>

            <div className={`rounded-2xl border overflow-hidden ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                }`}>
                {logs.length === 0 ? (
                    <div className="text-center py-12">
                        <ScrollText className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                        <p className={theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}>Нет записей</p>
                    </div>
                ) : (
                    <div className="divide-y divide-white/5">
                        {logs.map((log) => (
                            <div key={log.id} className={`p-4 flex items-center gap-4 ${theme === 'dark' ? 'hover:bg-white/5 backdrop-blur-xl' : 'hover:bg-gray-50'
                                }`}>
                                <div className="w-10 h-10 rounded-full bg-violet-500/10 flex items-center justify-center flex-shrink-0">
                                    <ScrollText className="w-5 h-5 text-violet-400" />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className={`font-medium ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{log.action}</p>
                                    {log.details && (
                                        <p className={`text-sm truncate ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{log.details}</p>
                                    )}
                                </div>
                                <div className="text-right flex-shrink-0">
                                    <p className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{log.user.login}</p>
                                    <p className={`text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                        {new Date(log.createdAt).toLocaleString('ru-RU')}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    )
}
