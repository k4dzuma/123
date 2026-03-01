'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import Link from 'next/link'
import { Footer } from '@/components/layout/footer'
import { motion, AnimatePresence } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { Gamepad2, Clock, Zap, ChevronRight, Loader2, Trophy, Star, Users, Award, Crown } from 'lucide-react'

interface Quest {
  id: number
  title: string
  description: string
  durationMinutes: number
  difficultyLevel: string
  stepCount: number
  totalSessions: number
  completedSessions: number
}

interface LeaderboardUser {
  rank: number
  id: number
  login: string
  avatar: string
  totalScore: number
  completedQuests: number
}

interface Stats {
  totalUsers: number
  totalPoints: number
  totalCompletions: number
}

const difficultyColors: Record<string, { bg: string; text: string; label: string }> = {
  easy: { bg: 'bg-green-500/20', text: 'text-green-400', label: 'Лёгкий' },
  medium: { bg: 'bg-yellow-500/20', text: 'text-yellow-400', label: 'Средний' },
  hard: { bg: 'bg-red-500/20', text: 'text-red-400', label: 'Сложный' },
}

const rankStyles = [
  'bg-primary text-black shadow-primary/20', // Rank 1 (Gold/Primary)
  'bg-neutral-300 text-black shadow-neutral-400/20',     // Rank 2 (Silver)
  'bg-orange-800 text-white shadow-orange-900/20', // Rank 3 (Bronze)
]

export default function QuestsPage() {
  const { theme } = useTheme()
  const { data: session } = useSession()
  const [tab, setTab] = useState<'quests' | 'leaderboard'>('quests')
  const [quests, setQuests] = useState<Quest[]>([])
  const [leaderboard, setLeaderboard] = useState<LeaderboardUser[]>([])
  const [stats, setStats] = useState<Stats | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      fetch('/api/quests').then(r => r.json()),
      fetch('/api/leaderboard').then(r => r.json()),
    ])
      .then(([questsData, lbData]) => {
        setQuests(questsData)
        setLeaderboard(lbData.leaderboard)
        setStats(lbData.stats)
      })
      .catch(console.error)
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-transparent">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    )
  }

  return (
    <>
      <main className="min-h-[100svh] relative pt-40 pb-32 overflow-hidden bg-transparent">
        <div className="container mx-auto px-8 lg:px-16 relative z-10">
          {/* Header */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center mb-16"
          >
            <h1 className="text-5xl md:text-7xl font-sans font-black tracking-tighter uppercase mb-6 text-white drop-shadow-2xl">
              Испытания <span className="font-serif italic text-primary lowercase tracking-normal">Памяти.</span>
            </h1>
            <p className="text-lg md:text-xl max-w-2xl mx-auto font-light tracking-wide mb-12 text-foreground/50">
              Проверьте знания, соревнуйтесь и пополняйте свою коллекцию цифровых достижений.
            </p>

            {/* Tabs */}
            <div className="flex justify-center gap-12 border-b border-white/10 pb-4">
              <button
                onClick={() => setTab('quests')}
                className={`flex items-center gap-3 text-xs tracking-[0.3em] font-bold uppercase transition-all duration-500 cursor-pointer ${tab === 'quests'
                  ? 'text-primary border-b-2 border-primary pb-4 -mb-[18px]'
                  : 'text-foreground/40 hover:text-primary transition-luxury transform hover:-translate-y-px'
                  }`}
              >
                <Gamepad2 className="w-4 h-4" />
                Квесты
              </button>
              <button
                onClick={() => setTab('leaderboard')}
                className={`flex items-center gap-3 text-xs tracking-[0.3em] font-bold uppercase transition-all duration-500 cursor-pointer ${tab === 'leaderboard'
                  ? 'text-primary border-b-2 border-primary pb-4 -mb-[18px]'
                  : 'text-foreground/40 hover:text-primary transition-luxury transform hover:-translate-y-px'
                  }`}
              >
                <Trophy className="w-4 h-4" />
                Рейтинг
              </button>
            </div>
          </motion.div>

          {/* Content */}
          <AnimatePresence mode="wait">
            {tab === 'quests' ? (
              <motion.div
                key="quests"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -20 }}
                transition={{ duration: 0.3 }}
              >
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {quests.map((quest, i) => {
                    const diff = difficultyColors[quest.difficultyLevel] || difficultyColors['medium']
                    return (
                      <motion.div
                        key={quest.id}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: i * 0.1 }}
                      >
                        <div className="group rounded-[2.5rem] border border-white/10 p-8 md:p-10 h-full flex flex-col transition-all duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)] bg-secondary/40 backdrop-blur-xl hover:border-primary/30 hover:bg-secondary/60 hover:-translate-y-2 shadow-2xl">
                          <div className="flex items-start justify-between mb-8">
                            <div className="p-4 rounded-2xl bg-primary/10">
                              <Gamepad2 className="w-8 h-8 text-primary" />
                            </div>
                            <span className={`px-4 py-1.5 rounded-full text-[10px] font-bold tracking-widest uppercase border ${diff.bg.replace('bg-', 'border-').replace('/20', '/30')} ${diff.text}`}>
                              {diff.label}
                            </span>
                          </div>

                          <h3 className="text-2xl font-bold tracking-tight uppercase text-white group-hover:text-primary transition-colors duration-500 mb-4">
                            {quest.title}
                          </h3>
                          <p className="text-sm font-light tracking-wide text-foreground/40 leading-relaxed mb-8 flex-grow">
                            {quest.description}
                          </p>

                          <div className="space-y-3 mb-5">
                            <div className="flex items-center gap-4 text-sm">
                              <div className={`flex items-center gap-1.5 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                <Clock className="w-4 h-4" />
                                {quest.durationMinutes} мин
                              </div>
                              <div className={`flex items-center gap-1.5 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                <Zap className="w-4 h-4" />
                                {quest.stepCount} этап{quest.stepCount !== 1 ? (quest.stepCount < 5 ? 'а' : 'ов') : ''}
                              </div>
                            </div>
                            <div className="flex items-center gap-4 text-sm">
                              <div className={`flex items-center gap-1.5 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                <Trophy className="w-4 h-4" />
                                {quest.completedSessions} прохождений
                              </div>
                              <div className={`flex items-center gap-1.5 ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                                <Star className="w-4 h-4" />
                                До {quest.stepCount * 150} баллов
                              </div>
                            </div>
                          </div>

                          {session ? (
                            <Link href={`/quests/${quest.id}/play`} className="w-full mt-8">
                              <button className="w-full py-5 tracking-[0.3em] uppercase font-black text-[10px] transition-all duration-500 flex items-center justify-center gap-2 rounded-full bg-white text-black hover:bg-neutral-200 magnetic-btn">
                                Испытать себя <ChevronRight className="w-4 h-4" />
                              </button>
                            </Link>
                          ) : (
                            <Link href="/login" className="w-full mt-4 magnetic-link">
                              <button className={`w-full py-4 tracking-widest uppercase font-bold text-sm transition-colors duration-300 flex items-center justify-center gap-2 border rounded-none ${theme === 'dark'
                                ? 'border-white/20 text-white hover:bg-white/10'
                                : 'border-black/20 text-black hover:bg-black/5'
                                }`}>
                                Войдите для квеста
                              </button>
                            </Link>
                          )}
                        </div>
                      </motion.div>
                    )
                  })}
                </div>
              </motion.div>
            ) : (
              <motion.div
                key="leaderboard"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -20 }}
                transition={{ duration: 0.3 }}
              >
                {/* Stats */}
                {stats && (
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                    {[
                      { icon: Users, label: 'Участников', value: stats.totalUsers, color: 'text-violet-500' },
                      { icon: Star, label: 'Всего баллов', value: stats.totalPoints, color: 'text-yellow-500' },
                      { icon: Award, label: 'Прохождений', value: stats.totalCompletions, color: 'text-green-500' },
                    ].map((stat, i) => (
                      <motion.div
                        key={i}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: i * 0.1 }}
                        className="rounded-[2.5rem] border border-white/10 p-8 text-center transition-all bg-secondary/40 backdrop-blur-xl hover:border-primary/20 hover:scale-[1.02]"
                      >
                        <stat.icon className="w-8 h-8 mx-auto mb-4 text-primary" />
                        <p className="text-4xl font-black tracking-tighter uppercase mb-2 text-white">{stat.value}</p>
                        <p className="text-[10px] font-bold tracking-[0.2em] uppercase text-foreground/40">{stat.label}</p>
                      </motion.div>
                    ))}
                  </div>
                )}

                {/* Leaderboard table */}
                <div className="max-w-2xl mx-auto">
                  {leaderboard.length === 0 ? (
                    <div className={`text-center py-12 rounded-2xl border ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                      }`}>
                      <Trophy className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                      <p className={theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}>Пока нет результатов</p>
                      <p className={`text-sm ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>Пройдите квест первым!</p>
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {leaderboard.map((user, i) => (
                        <motion.div
                          key={user.id}
                          initial={{ opacity: 0, x: -20 }}
                          animate={{ opacity: 1, x: 0 }}
                          transition={{ delay: i * 0.05 }}
                          className={`flex items-center gap-6 p-8 rounded-[2.5rem] border transition-all hover:scale-[1.01] ${user.rank <= 3
                            ? 'bg-primary/5 border-primary/20 shadow-2xl shadow-primary/5'
                            : 'bg-secondary/40 backdrop-blur-xl border-white/5'
                            }`}
                        >
                          <div className="w-12 flex-shrink-0 text-center">
                            {user.rank <= 3 ? (
                              <div className={`w-12 h-12 rounded-full ${user.rank === 1 ? (theme === 'dark' ? 'bg-primary text-white' : 'bg-yellow-500 text-white') :
                                user.rank === 2 ? (theme === 'dark' ? 'bg-gray-600 text-white' : 'bg-gray-300 text-gray-800') :
                                  (theme === 'dark' ? 'bg-amber-700 text-white' : 'bg-amber-600 text-white')
                                } flex items-center justify-center shadow-xl`}>
                                <Crown className="w-6 h-6" />
                              </div>
                            ) : (
                              <span className={`text-xl font-bold ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                {user.rank}
                              </span>
                            )}
                          </div>
                          <img
                            src={user.avatar || '/images/avatars/default_avatar.png'}
                            alt={user.login}
                            className={`w-14 h-14 rounded-full object-cover border-2 ${theme === 'dark' ? 'border-primary/50' : 'border-gray-300'}`}
                          />
                          <div className="flex-1 min-w-0">
                            <p className={`text-lg font-bold tracking-widest uppercase truncate ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                              {user.login}
                            </p>
                            <p className={`text-sm font-light tracking-wide ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                              {user.completedQuests} квест{user.completedQuests !== 1 ? (user.completedQuests < 5 ? 'а' : 'ов') : ''} пройдено
                            </p>
                          </div>
                          <div className="text-right">
                            <p className={`text-2xl font-black tracking-tighter ${user.rank === 1 ? (theme === 'dark' ? 'text-primary' : 'text-yellow-600') :
                              theme === 'dark' ? 'text-white' : 'text-gray-900'
                              }`}>
                              {user.totalScore}
                            </p>
                            <p className={`text-xs font-bold tracking-widest uppercase ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>баллов</p>
                          </div>
                        </motion.div>
                      ))}
                    </div>
                  )}
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </main>
      <Footer />
    </>
  )
}
