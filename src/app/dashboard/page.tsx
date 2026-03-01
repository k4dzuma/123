'use client'

import { useSession } from 'next-auth/react'
import { useRouter } from 'next/navigation'
import { useEffect } from 'react'
import { Navbar } from '@/components/layout/navbar'
import { Footer } from '@/components/layout/footer'
import { motion } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { User, Trophy, Gamepad2, MessageSquare, Shield, Loader2 } from 'lucide-react'

export default function DashboardPage() {
    const { theme } = useTheme()
    const { data: session, status } = useSession()
    const router = useRouter()

    useEffect(() => {
        if (status === 'unauthenticated') router.push('/login')
    }, [status, router])

    if (status === 'loading') {
        return (
            <>
                <Navbar />
                <div className="min-h-screen flex items-center justify-center">
                    <Loader2 className="w-8 h-8 animate-spin text-violet-500" />
                </div>
                <Footer />
            </>
        )
    }

    if (!session) return null

    const user = session.user
    const role = (user as Record<string, unknown>)?.role as string

    return (
        <>
            <Navbar />
            <main className={`min-h-screen ${theme === 'dark' ? 'bg-black/20 backdrop-blur-xl border-t border-white/10' : 'bg-gray-50'}`}>
                <div className="container mx-auto px-4 py-16 max-w-3xl">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                    >
                        {/* Profile card */}
                        <div className={`rounded-2xl border p-8 mb-8 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5 shadow-sm'
                            }`}>
                            <div className="flex items-center gap-6 mb-6">
                                <div className="relative">
                                    <img
                                        src={user?.image || '/images/avatars/default_avatar.png'}
                                        alt={user?.name || ''}
                                        className="w-24 h-24 rounded-2xl object-cover border-2 border-violet-500/30"
                                    />
                                    {role === 'ADMIN' && (
                                        <div className="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center">
                                            <Shield className="w-4 h-4 text-white" />
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <h1 className={`text-3xl font-bold mb-1 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                        {user?.name}
                                    </h1>
                                    <p className={`${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{user?.email}</p>
                                    <span className={`inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium ${role === 'ADMIN' ? 'bg-violet-500/20 text-violet-400' : 'bg-fuchsia-500/20 text-blue-400'
                                        }`}>
                                        {role === 'ADMIN' ? 'Администратор' : 'Пользователь'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Quick links */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {[
                                { href: '/quests', icon: Gamepad2, label: 'Квесты', desc: 'Пройти квесты', color: 'from-violet-500 to-fuchsia-600' },
                                { href: '/leaderboard', icon: Trophy, label: 'Рейтинг', desc: 'Таблица лидеров', color: 'from-yellow-500 to-fuchsia-600' },
                                { href: '/comments', icon: MessageSquare, label: 'Отзывы', desc: 'Оставить отзыв', color: 'from-fuchsia-500 to-cyan-600' },
                            ].map((item, i) => (
                                <motion.a
                                    key={i}
                                    href={item.href}
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: 0.2 + i * 0.1 }}
                                    className={`rounded-2xl border p-5 transition-all hover:scale-[1.02] ${theme === 'dark'
                                            ? 'bg-white/5 backdrop-blur-xl border-white/10 hover:border-violet-500/30'
                                            : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5 hover:border-violet-300 hover:shadow-md'
                                        }`}
                                >
                                    <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${item.color} flex items-center justify-center mb-3`}>
                                        <item.icon className="w-6 h-6 text-white" />
                                    </div>
                                    <h3 className={`font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{item.label}</h3>
                                    <p className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{item.desc}</p>
                                </motion.a>
                            ))}
                        </div>
                    </motion.div>
                </div>
            </main>
            <Footer />
        </>
    )
}
