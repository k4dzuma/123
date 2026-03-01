'use client'

import Link from 'next/link'
import { motion } from 'framer-motion'
import { Home, Search, Terminal } from 'lucide-react'

export default function NotFound() {
    return (
        <main className="min-h-screen bg-transparent flex items-center justify-center pt-20">
            <div className="container mx-auto px-8 py-16 text-center max-w-4xl relative">
                {/* Decorative background element */}
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-[500px] bg-primary/5 rounded-[50%] blur-[120px] -z-10" />

                <motion.div
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 1, ease: [0.16, 1, 0.3, 1] }}
                >
                    <div className="inline-flex items-center gap-3 px-6 py-2 rounded-full bg-white/5 border border-white/10 mb-8 transition-luxury hover:bg-white/10">
                        <Terminal className="w-4 h-4 text-primary" />
                        <span className="text-[10px] font-black tracking-[0.4em] uppercase text-white/40">ОШИБКА 404 // СИСТЕМА</span>
                    </div>

                    <h1 className="text-[12rem] md:text-[18rem] font-sans font-black leading-none tracking-tighter text-white mb-4 select-none opacity-10">
                        404
                    </h1>

                    <div className="relative -mt-32 md:-mt-48 mb-16">
                        <h2 className="text-5xl md:text-8xl font-sans font-black tracking-tighter uppercase text-white mb-6">
                            Экспонат <span className="text-primary italic font-serif lowercase tracking-normal">не найден.</span>
                        </h2>
                        <p className="text-lg md:text-xl font-light tracking-wide text-white/40 max-w-2xl mx-auto leading-relaxed">
                            Кажется, этот экспонат был перемещён в закрытые архивы или еще не был оцифрован. Попробуйте вернуться к основному каталогу.
                        </p>
                    </div>

                    <div className="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <Link href="/" className="w-full sm:w-auto">
                            <button className="w-full px-12 py-5 rounded-full bg-primary text-white font-black uppercase tracking-widest text-[10px] hover:bg-white hover:text-black transition-luxury shadow-xl shadow-primary/20 magnetic-btn">
                                <div className="flex items-center justify-center gap-2">
                                    <Home className="w-4 h-4" /> В начало
                                </div>
                            </button>
                        </Link>
                        <Link href="/sections" className="w-full sm:w-auto">
                            <button className="w-full px-12 py-5 rounded-full bg-white/5 border border-white/10 text-white font-black uppercase tracking-widest text-[10px] hover:bg-white/10 transition-luxury magnetic-btn">
                                <div className="flex items-center justify-center gap-2">
                                    <Search className="w-4 h-4" /> В архивы
                                </div>
                            </button>
                        </Link>
                    </div>

                    {/* Minimalist footer hint */}
                    <div className="mt-24 pt-12 border-t border-white/5 opacity-20">
                        <p className="text-[10px] font-black tracking-[0.5em] uppercase text-white">
                            SISTEMA DIGITALIS // VER. 12.4.0
                        </p>
                    </div>
                </motion.div>
            </div>
        </main>
    )
}
