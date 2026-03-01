'use client'

import Link from 'next/link'
import { Facebook, Twitter, Youtube, Instagram } from 'lucide-react'
import { useTheme } from '@/components/theme/theme-provider'

export function Footer() {
  const { theme } = useTheme()
  const isDark = theme === 'dark'

  return (
    <footer className="relative bg-transparent rounded-t-[4rem] pt-32 pb-16 overflow-hidden">
      {/* Footer background bridge */}
      <div className="absolute inset-0 bg-secondary/90 backdrop-blur-md z-[-1]" />

      <div className="container mx-auto px-8 lg:px-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-16 mb-24">
          <div className="md:col-span-2 space-y-8">
            <Link href="/" className="text-3xl font-black tracking-tighter uppercase flex items-center gap-3">
              <div className="w-3 h-3 rounded-full bg-primary" />
              Человек и Время
            </Link>
            <p className="text-foreground/40 text-sm max-w-xs font-light leading-relaxed tracking-wide">
              Кинематографичный портал в коллективную память. Пиксельное совершенство, верность истории, неизменность.
            </p>
            <div className="flex space-x-4">
              {[Facebook, Twitter, Youtube, Instagram].map((Icon, idx) => (
                <Link
                  key={idx}
                  href="#"
                  className="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-neutral-400 hover:text-white hover:border-primary/40 hover:bg-white/5 transition-all duration-300"
                >
                  <Icon className="h-4 w-4" />
                </Link>
              ))}
            </div>
          </div>

          <div className="space-y-6">
            <h4 className="text-[10px] uppercase tracking-[0.4em] font-bold text-primary">Навигация</h4>
            <div className="flex flex-col gap-4 text-sm font-light text-foreground/60">
              <Link href="/sections" className="hover:text-primary transition-colors">Экспозиции</Link>
              <Link href="/quests" className="hover:text-primary transition-colors">Квесты</Link>
              <Link href="/comments" className="hover:text-primary transition-colors">Отзывы</Link>
            </div>
          </div>

        </div>

        <div className="flex flex-col md:flex-row items-center justify-between pt-16 border-t border-white/5 gap-8">
          <div className="flex items-center gap-3">
            <div className="w-2 h-2 rounded-full bg-[#10b981] animate-pulse shadow-lg shadow-[#10b981]/40" />
            <span className="font-mono text-[10px] uppercase tracking-widest text-[#10b981]/80">Система активна // Связь установлена</span>
          </div>
          <p className="text-muted-foreground text-[10px] uppercase tracking-[0.2em]">© 2026 ВИРТУАЛЬНЫЙ МУЗЕЙ. ВСЕ ПРАВА ЗАЩИЩЕНЫ.</p>
        </div>
      </div>
    </footer>
  )
}
