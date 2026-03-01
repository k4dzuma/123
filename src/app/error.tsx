'use client'

import { useEffect } from 'react'
import Link from 'next/link'
import { motion } from 'framer-motion'
import { AlertCircle, RefreshCcw, Home } from 'lucide-react'
import { Navbar } from '@/components/layout/navbar'
import { Footer } from '@/components/layout/footer'

export default function Error({
    error,
    reset,
}: {
    error: Error & { digest?: string }
    reset: () => void
}) {
    useEffect(() => {
        console.error('App Error:', error)
    }, [error])

    return (
        <>
            <Navbar />
            <main className="min-h-screen bg-black/20 backdrop-blur-xl border-t border-white/10 flex items-center justify-center py-20 px-4 relative overflow-hidden">
                {/* Animated background */}
                <div className="absolute inset-0">
                    <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-red-600/10 rounded-full blur-3xl animate-pulse" />
                    <div className="absolute bottom-1/4 left-1/4 w-96 h-96 bg-fuchsia-600/10 rounded-full blur-3xl animate-pulse delay-1000" />
                </div>

                <motion.div
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="relative z-10 w-full max-w-lg rounded-2xl border border-white/10 bg-black/40 backdrop-blur-2xl border-t border-white/10/80 backdrop-blur-xl p-8 md:p-12 text-center"
                >
                    <div className="w-20 h-20 mx-auto rounded-full bg-red-500/20 flex items-center justify-center mb-6 border border-red-500/30">
                        <AlertCircle className="w-10 h-10 text-red-500" />
                    </div>

                    <h2 className="text-3xl font-bold text-white mb-4">Что-то пошло не так</h2>
                    <p className="text-gray-400 mb-8">
                        Произошла непредвиденная ошибка при загрузке страницы. Мы уже знаем о проблеме и работаем над её устранением.
                    </p>

                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <button
                            onClick={() => reset()}
                            className="flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 text-white font-semibold hover:from-red-700 hover:to-orange-700 transition-all shadow-lg hover:shadow-red-500/20"
                        >
                            <RefreshCcw className="w-5 h-5" /> Попробовать снова
                        </button>
                        <Link href="/">
                            <button className="flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-white/20 text-white font-semibold hover:bg-white/5 backdrop-blur-xl transition-all w-full sm:w-auto">
                                <Home className="w-5 h-5" /> На главную
                            </button>
                        </Link>
                    </div>
                </motion.div>
            </main>
            <Footer />
        </>
    )
}
