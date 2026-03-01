'use client'

import { useState } from 'react'
import { signIn } from 'next-auth/react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { Footer } from '@/components/layout/footer'
import { motion } from 'framer-motion'
import { LogIn, Eye, EyeOff, User, Lock, ArrowRight } from 'lucide-react'

export default function LoginPage() {
    const router = useRouter()
    const [login, setLogin] = useState('')
    const [password, setPassword] = useState('')
    const [showPassword, setShowPassword] = useState(false)
    const [error, setError] = useState('')
    const [loading, setLoading] = useState(false)

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()
        setError('')
        setLoading(true)

        try {
            const res = await signIn('credentials', {
                login,
                password,
                redirect: false,
            })

            if (res?.error) {
                setError('Неверный логин или пароль')
            } else {
                router.push('/')
                router.refresh()
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
                    className="relative z-10 w-full max-w-md mx-4"
                >
                    <div className="bg-secondary/40 backdrop-blur-2xl rounded-[2.5rem] border border-white/10 shadow-2xl p-10 md:p-12">
                        <div className="text-center mb-8">
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ type: 'spring', stiffness: 200, delay: 0.2 }}
                                className="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-primary/20"
                            >
                                <LogIn className="w-10 h-10 text-primary" />
                            </motion.div>
                            <h1 className="text-4xl font-sans font-black tracking-tighter uppercase text-white mb-3">Вход</h1>
                            <p className="text-white/60">Войдите в виртуальный музей</p>
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

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label className="block text-white/70 text-sm mb-2">Логин</label>
                                <div className="relative">
                                    <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" />
                                    <input
                                        type="text"
                                        value={login}
                                        onChange={(e) => setLogin(e.target.value)}
                                        className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/20 transition-all"
                                        placeholder="Введите логин"
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
                                        className="w-full bg-white/10 border border-white/20 rounded-xl px-10 py-3 text-white placeholder-white/30 focus:outline-none focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/20 transition-all"
                                        placeholder="Введите пароль"
                                        required
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

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full bg-white text-black font-black uppercase tracking-[0.3em] py-5 rounded-full transition-all duration-500 flex items-center justify-center gap-3 disabled:opacity-50 shadow-xl hover:bg-neutral-200 magnetic-btn text-[10px]"
                            >
                                {loading ? (
                                    <div className="w-5 h-5 border-2 border-black/30 border-t-black rounded-full animate-spin" />
                                ) : (
                                    <>
                                        Войти <ArrowRight className="w-4 h-4" />
                                    </>
                                )}
                            </button>
                        </form>

                        <div className="mt-6 text-center">
                            <p className="text-white/50 text-sm">
                                Нет аккаунта?{' '}
                                <Link href="/register" className="text-violet-400 hover:text-violet-300 transition-colors font-medium">
                                    Зарегистрируйтесь
                                </Link>
                            </p>
                        </div>

                        <div className="mt-4 text-center text-white/30 text-xs">
                            Тестовые данные: admin / admin123
                        </div>
                    </div>
                </motion.div>
            </div>
            <Footer />
        </>
    )
}
