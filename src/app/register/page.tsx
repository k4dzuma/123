'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { Footer } from '@/components/layout/footer'
import { motion } from 'framer-motion'
import { UserPlus, Eye, EyeOff, User, Mail, Lock, ArrowRight, Check } from 'lucide-react'

export default function RegisterPage() {
    const router = useRouter()
    const [login, setLogin] = useState('')
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [confirmPassword, setConfirmPassword] = useState('')
    const [showPassword, setShowPassword] = useState(false)
    const [error, setError] = useState('')
    const [success, setSuccess] = useState(false)
    const [loading, setLoading] = useState(false)

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()
        setError('')

        if (password !== confirmPassword) {
            setError('Пароли не совпадают')
            return
        }

        setLoading(true)

        try {
            const res = await fetch('/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ login, email, password }),
            })

            const data = await res.json()

            if (!res.ok) {
                setError(data.error || 'Ошибка регистрации')
            } else {
                setSuccess(true)
                setTimeout(() => router.push('/login'), 2000)
            }
        } catch {
            setError('Ошибка подключения к серверу')
        } finally {
            setLoading(false)
        }
    }

    return (
        <>
            <div className="min-h-screen flex items-center justify-center relative bg-transparent py-40">

                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6 }}
                    className="relative z-10 w-full max-w-md mx-4 my-20"
                >
                    <div className="bg-secondary/40 backdrop-blur-2xl rounded-[2.5rem] border border-white/10 shadow-2xl p-10 md:p-12">
                        {success ? (
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                className="text-center py-8"
                            >
                                <div className="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Check className="w-10 h-10 text-white" />
                                </div>
                                <h2 className="text-2xl font-bold text-white mb-2">Успешно!</h2>
                                <p className="text-white/60">Перенаправляем на страницу входа...</p>
                            </motion.div>
                        ) : (
                            <>
                                <div className="text-center mb-8">
                                    <motion.div
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        transition={{ type: 'spring', stiffness: 200, delay: 0.2 }}
                                        className="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-primary/20"
                                    >
                                        <UserPlus className="w-10 h-10 text-primary" />
                                    </motion.div>
                                    <h1 className="text-4xl font-sans font-black tracking-tighter uppercase text-white mb-3">Регистрация</h1>
                                    <p className="text-foreground/40 text-sm font-light tracking-wide">Создайте аккаунт в вечном архиве</p>
                                </div>

                                {error && (
                                    <motion.div
                                        initial={{ opacity: 0, x: -20 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        className="bg-red-500/20 border border-red-500/30 rounded-xl p-3 mb-6 text-red-200 text-sm"
                                    >
                                        {error}
                                    </motion.div>
                                )}

                                <form onSubmit={handleSubmit} className="space-y-4">
                                    <div>
                                        <label className="block text-white/70 text-sm mb-2">Логин</label>
                                        <div className="relative">
                                            <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" />
                                            <input
                                                type="text"
                                                value={login}
                                                onChange={(e) => setLogin(e.target.value)}
                                                className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                                placeholder="Минимум 3 символа"
                                                required
                                                minLength={3}
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-white/70 text-sm mb-2">Email</label>
                                        <div className="relative">
                                            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" />
                                            <input
                                                type="email"
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                                placeholder="example@mail.com"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-white/70 text-sm mb-2">Пароль</label>
                                        <div className="relative">
                                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" />
                                            <input
                                                type={showPassword ? 'text' : 'password'}
                                                value={password}
                                                onChange={(e) => setPassword(e.target.value)}
                                                className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                                placeholder="Минимум 6 символов"
                                                required
                                                minLength={6}
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowPassword(!showPassword)}
                                                className="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition-colors"
                                            >
                                                {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-white/70 text-sm mb-2">Подтвердите пароль</label>
                                        <div className="relative">
                                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" />
                                            <input
                                                type="password"
                                                value={confirmPassword}
                                                onChange={(e) => setConfirmPassword(e.target.value)}
                                                className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                                                placeholder="Повторите пароль"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={loading}
                                        className="w-full bg-white text-black font-black uppercase tracking-[0.3em] py-5 rounded-full transition-all duration-500 flex items-center justify-center gap-3 disabled:opacity-50 shadow-xl hover:bg-neutral-200 magnetic-btn text-[10px]"
                                    >
                                        {loading ? (
                                            <div className="w-5 h-5 border-2 border-black/30 border-t-black rounded-full animate-spin" />
                                        ) : (
                                            <>
                                                Присоединиться <ArrowRight className="w-4 h-4" />
                                            </>
                                        )}
                                    </button>
                                </form>

                                <div className="mt-6 text-center">
                                    <p className="text-white/50 text-sm">
                                        Уже есть аккаунт?{' '}
                                        <Link href="/login" className="text-emerald-400 hover:text-emerald-300 transition-colors font-medium">
                                            Войдите
                                        </Link>
                                    </p>
                                </div>
                            </>
                        )}
                    </div>
                </motion.div>
            </div>
            <Footer />
        </>
    )
}
