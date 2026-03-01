'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useRouter } from 'next/navigation'
import { Footer } from '@/components/layout/footer'
import { motion } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { ProfileSkeleton } from '@/components/ui/skeleton'
import {
    User, Trophy, Gamepad2, MessageSquare, Shield, Star, Award,
    TrendingUp, Clock, CheckCircle, XCircle, Target
} from 'lucide-react'

interface ProfileData {
    user: {
        id: number; login: string; email: string; avatar: string;
        role: string; totalScore: number; createdAt: string
    }
    stats: { totalQuests: number; totalPoints: number; avgScore: number; rank: number; commentsCount: number }
    achievements: { id: string; name: string; icon: string; desc: string }[]
    recentSessions: { id: number; questTitle: string; score: number; status: string; startTime: string; endTime: string | null }[]
}

export default function ProfilePage() {
    const { theme } = useTheme()
    const { data: session, status } = useSession()
    const router = useRouter()
    const [profile, setProfile] = useState<ProfileData | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        if (status === 'unauthenticated') router.push('/login')
        if (status === 'authenticated') {
            const userId = (session?.user as Record<string, unknown>)?.id
            if (userId) {
                fetch(`/api/profile?userId=${userId}`)
                    .then(r => r.json())
                    .then(setProfile)
                    .catch(console.error)
                    .finally(() => setLoading(false))
            }
        }
    }, [status, session, router])

    if (status === 'loading' || loading) {
        return (
            <main className="min-h-screen bg-transparent">
                <div className="container mx-auto px-4 py-32 max-w-3xl">
                    <ProfileSkeleton />
                </div>
            </main>
        )
    }

    if (!profile) return null

    const { user, stats, achievements, recentSessions } = profile
    const statusIcons: Record<string, { icon: typeof CheckCircle; color: string; label: string }> = {
        completed: { icon: CheckCircle, color: 'text-green-400', label: 'Завершён' },
        active: { icon: Clock, color: 'text-blue-400', label: 'В процессе' },
        abandoned: { icon: XCircle, color: 'text-red-400', label: 'Брошен' },
    }

    return (
        <>
            <main className="min-h-screen relative bg-transparent pt-40 pb-32">
                <div className="container mx-auto px-8 lg:px-16 max-w-4xl relative z-10">
                    {/* Profile card */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="rounded-[2.5rem] border border-white/10 p-10 mb-8 bg-secondary/40 backdrop-blur-xl shadow-2xl"
                    >
                        <div className="flex items-center gap-6 mb-6">
                            <div className="relative">
                                <img
                                    src={user.avatar || '/images/avatars/default_avatar.png'}
                                    alt={user.login}
                                    className="w-24 h-24 rounded-2xl object-cover border-2 border-violet-500/30"
                                />
                                {user.role === 'ADMIN' && (
                                    <div className="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center">
                                        <Shield className="w-4 h-4 text-white" />
                                    </div>
                                )}
                            </div>
                            <div>
                                <h1 className={`text-3xl font-bold mb-1 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{user.login}</h1>
                                <p className={`${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{user.email}</p>
                                <div className="flex items-center gap-3 mt-2">
                                    <span className="px-4 py-1.5 rounded-full text-[10px] font-bold tracking-widest uppercase bg-primary/10 text-primary border border-primary/20">
                                        {user.role === 'ADMIN' ? 'Администратор' : 'Резидент'}
                                    </span>
                                    <span className={`text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                        с {new Date(user.createdAt).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long' })}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mt-12">
                            {[
                                { icon: Trophy, label: 'Место', value: `#${stats.rank}`, color: 'from-primary to-blue-600' },
                                { icon: Star, label: 'Баллы', value: stats.totalPoints.toString(), color: 'from-blue-500 to-indigo-600' },
                                { icon: Gamepad2, label: 'Квестов', value: stats.totalQuests.toString(), color: 'from-indigo-600 to-primary' },
                                { icon: TrendingUp, label: 'Ср. балл', value: stats.avgScore.toString(), color: 'from-emerald-600 to-primary' },
                                { icon: MessageSquare, label: 'Отзывы', value: stats.commentsCount.toString(), color: 'from-primary to-emerald-600' },
                            ].map((item, i) => (
                                <motion.div
                                    key={i}
                                    initial={{ opacity: 0, scale: 0.9 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    transition={{ delay: 0.1 + i * 0.05 }}
                                    className="rounded-2xl p-6 text-center bg-white/5 border border-white/10 hover:border-primary/20 transition-all duration-500 transform hover:-translate-y-px"
                                >
                                    <div className={`w-10 h-10 mx-auto mb-3 rounded-xl bg-gradient-to-br ${item.color} flex items-center justify-center shadow-lg shadow-black/20`}>
                                        <item.icon className="w-4 h-4 text-white" />
                                    </div>
                                    <p className="text-2xl font-black tracking-tighter text-white">{item.value}</p>
                                    <p className="text-[10px] font-bold tracking-[0.2em] uppercase text-foreground/40">{item.label}</p>
                                </motion.div>
                            ))}
                        </div>
                    </motion.div>

                    {/* Achievements */}
                    {achievements.length > 0 && (
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.2 }}
                            className="rounded-[2.5rem] border border-white/10 p-10 mb-8 bg-secondary/40 backdrop-blur-xl"
                        >
                            <h2 className="text-xl font-black uppercase tracking-widest mb-8 flex items-center gap-3 text-white">
                                <Award className="w-6 h-6 text-primary" /> Достижения ({achievements.length})
                            </h2>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-6">
                                {achievements.map((ach, i) => (
                                    <motion.div
                                        key={ach.id}
                                        initial={{ opacity: 0, scale: 0.8 }}
                                        animate={{ opacity: 1, scale: 1 }}
                                        transition={{ delay: 0.3 + i * 0.05 }}
                                        className="rounded-2xl p-6 text-center border border-white/5 transition-all duration-500 hover:scale-105 bg-white/5 hover:border-primary/20"
                                    >
                                        <span className="text-3xl block mb-3">{ach.icon}</span>
                                        <p className="font-black text-[10px] uppercase tracking-widest text-white mb-1">{ach.name}</p>
                                        <p className="text-xs font-light text-foreground/40">{ach.desc}</p>
                                    </motion.div>
                                ))}
                            </div>
                        </motion.div>
                    )}

                    {/* Recent sessions */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3 }}
                        className="rounded-[2.5rem] border border-white/10 overflow-hidden bg-secondary/40 backdrop-blur-xl"
                    >
                        <div className="p-10 pb-4">
                            <h2 className="text-xl font-black uppercase tracking-widest flex items-center gap-3 text-white">
                                <Target className="w-6 h-6 text-primary" /> История квестов
                            </h2>
                        </div>
                        {recentSessions.length === 0 ? (
                            <div className="p-12 text-center">
                                <Gamepad2 className="w-12 h-12 mx-auto mb-4 text-foreground/20" />
                                <p className="text-foreground/40 font-light tracking-wide">Вы ещё не проходили квесты</p>
                            </div>
                        ) : (
                            <div className="p-6 space-y-2">
                                {recentSessions.map((session, i) => {
                                    const si = statusIcons[session.status] || statusIcons['active']
                                    const Icon = si.icon
                                    return (
                                        <motion.div
                                            key={session.id}
                                            initial={{ opacity: 0, x: -10 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            transition={{ delay: 0.4 + i * 0.05 }}
                                            className="flex items-center gap-6 p-6 rounded-2xl hover:bg-white/5 transition-all duration-300"
                                        >
                                            <Icon className={`w-5 h-5 flex-shrink-0 ${si.color}`} />
                                            <div className="flex-1 min-w-0">
                                                <p className="font-bold text-white tracking-tight truncate">
                                                    {session.questTitle}
                                                </p>
                                                <p className="text-[10px] font-bold tracking-widest uppercase text-foreground/40">
                                                    {new Date(session.startTime).toLocaleDateString('ru-RU')} • {si.label}
                                                </p>
                                            </div>
                                            <span className="text-2xl font-black tracking-tighter text-white">
                                                {session.score}
                                            </span>
                                        </motion.div>
                                    )
                                })}
                            </div>
                        )}
                    </motion.div>
                </div>
            </main>
            <Footer />
        </>
    )
}
