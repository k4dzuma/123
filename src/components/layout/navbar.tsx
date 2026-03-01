'use client'

import React, { useEffect, useState } from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { useSession, signOut } from 'next-auth/react'
import { motion, AnimatePresence } from 'framer-motion'
import { Menu, X, User, Shield, LogOut } from 'lucide-react'
import { useTheme } from '@/components/theme/theme-provider'

export function Navbar() {
  const [isScrolled, setIsScrolled] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const pathname = usePathname()
  const { data: session } = useSession()

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50)
    }
    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  const isAdmin = (session?.user as Record<string, unknown>)?.role === 'ADMIN'

  const navLinks = [
    { name: 'Экспозиция', href: '/sections' },
    { name: 'Квесты', href: '/quests' },
    { name: 'Отзывы', href: '/comments' },
  ]

  return (
    <nav
      className={`fixed top-8 left-1/2 -translate-x-1/2 z-[1000] transition-all duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)] ${isScrolled
        ? 'w-[95%] md:w-[850px] bg-background/60 backdrop-blur-xl border border-white/10 rounded-full px-8 py-3 shadow-2xl'
        : 'w-full max-w-7xl px-8 py-6 bg-transparent rounded-none'
        }`}
    >
      <div className="flex items-center justify-between gap-4">
        <Link href="/" className="text-lg font-bold tracking-tighter uppercase group flex items-center gap-2 shrink-0">
          <div className="w-2 h-2 rounded-full bg-primary animate-pulse" />
          <span className="group-hover:text-primary transition-colors italic font-serif whitespace-nowrap">Человек и Время</span>
        </Link>

        {/* Desktop Links */}
        <div className="hidden md:flex items-center justify-center flex-1 gap-12">
          {navLinks.map((item) => (
            <Link
              key={item.name}
              href={item.href}
              className={`text-[10px] uppercase tracking-[0.3em] font-bold transition-luxury transform hover:-translate-y-px whitespace-nowrap ${pathname === item.href ? 'text-primary' : 'text-foreground/60 hover:text-primary'
                }`}
            >
              {item.name}
            </Link>
          ))}
          {isAdmin && (
            <Link
              href="/admin"
              className="text-[10px] uppercase tracking-[0.3em] font-bold text-foreground/40 hover:text-primary transition-luxury transform hover:-translate-y-px whitespace-nowrap"
            >
              Админ
            </Link>
          )}
        </div>

        <div className="flex items-center gap-4 shrink-0">
          {session ? (
            <div className="flex items-center gap-4">
              <Link href="/profile" className="relative z-10 hidden sm:flex items-center gap-2 text-[10px] uppercase tracking-widest font-bold text-foreground/60 hover:text-primary transition-colors">
                {session.user?.image ? (
                  <img src={session.user.image} alt="" className="w-6 h-6 rounded-full object-cover border border-white/20" />
                ) : (
                  <User size={14} />
                )}
                <span className="hidden lg:block">{session.user?.name}</span>
              </Link>
              <button
                onClick={() => signOut({ callbackUrl: '/' })}
                className="bg-white/5 border border-white/10 text-white p-2.5 rounded-full hover:bg-white/10 transition-colors"
                title="Выйти"
              >
                <LogOut size={14} />
              </button>
            </div>
          ) : (
            <Link href="/login" className="relative z-10">
              <button className="bg-primary text-primary-foreground px-8 py-2.5 rounded-full text-[10px] uppercase tracking-widest font-black pointer-events-auto magnetic-btn shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-95 transition-all">
                Войти
              </button>
            </Link>
          )}

          {/* Mobile Toggle */}
          <button
            className="md:hidden p-2 text-foreground/60 pointer-events-auto"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            {mobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      <AnimatePresence>
        {mobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, scale: 0.95, y: -20 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.95, y: -20 }}
            className="absolute top-full left-0 w-full mt-4 bg-background/95 backdrop-blur-2xl border border-white/10 rounded-[2rem] p-8 shadow-2xl flex flex-col gap-6 md:hidden"
          >
            {navLinks.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                onClick={() => setMobileMenuOpen(false)}
                className="text-xl font-light uppercase tracking-widest text-foreground/80"
              >
                {item.name}
              </Link>
            ))}
            {session ? (
              <Link href="/profile" onClick={() => setMobileMenuOpen(false)}>
                <button className="w-full bg-white/5 text-white py-4 rounded-2xl text-xs uppercase tracking-widest font-bold border border-white/10">
                  Профиль
                </button>
              </Link>
            ) : (
              <Link href="/login" onClick={() => setMobileMenuOpen(false)}>
                <button className="w-full bg-primary text-primary-foreground py-4 rounded-2xl text-xs uppercase tracking-widest font-bold">
                  Войти
                </button>
              </Link>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </nav>
  )
}
