'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useRouter, usePathname } from 'next/navigation'
import Link from 'next/link'
import { useTheme } from '@/components/theme/theme-provider'
import {
    LayoutDashboard,
    Users,
    Gamepad2,
    FileText,
    ScrollText,
    Shield,
    Loader2,
    MessageSquare,
} from 'lucide-react'

const adminNav = [
    { href: '/admin', label: 'Дашборд', icon: LayoutDashboard },
    { href: '/admin/users', label: 'Пользователи', icon: Users },
    { href: '/admin/quests', label: 'Квесты', icon: Gamepad2 },
    { href: '/admin/comments', label: 'Комментарии', icon: MessageSquare },
    { href: '/admin/content', label: 'Контент', icon: FileText },
    { href: '/admin/logs', label: 'Логи', icon: ScrollText },
]

export default function AdminLayout({ children }: { children: React.ReactNode }) {
    const { theme } = useTheme()
    const { data: session, status } = useSession()
    const router = useRouter()
    const pathname = usePathname()

    useEffect(() => {
        if (status === 'unauthenticated') {
            router.push('/login')
        } else if (status === 'authenticated') {
            const role = (session?.user as Record<string, unknown>)?.role
            if (role !== 'ADMIN') {
                router.push('/')
            }
        }
    }, [status, session, router])

    if (status === 'loading') {
        return (
            <div className="min-h-screen flex items-center justify-center bg-transparent">
                <Loader2 className="w-8 h-8 animate-spin text-primary" />
            </div>
        )
    }

    const isAdmin = (session?.user as Record<string, unknown>)?.role === 'ADMIN'
    if (!isAdmin) return null

    return (
        <>
            <div className="min-h-screen flex bg-transparent pt-32">
                {/* Sidebar */}
                <aside className="w-72 flex-shrink-0 border-r border-white/10 hidden md:block bg-secondary/20 backdrop-blur-3xl">
                    <div className="p-6">
                        <div className="flex items-center gap-4 mb-12">
                            <div className="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-2xl shadow-primary/10">
                                <Shield className="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h2 className="font-sans font-black tracking-widest text-[10px] uppercase text-white/40">Контроль</h2>
                                <h3 className="font-sans font-black tracking-tighter text-white uppercase text-lg">Центр Упр.</h3>
                            </div>
                        </div>

                        <nav className="space-y-2">
                            {adminNav.map((item) => {
                                const Icon = item.icon
                                const isActive = item.href === '/admin' ? pathname === '/admin' : pathname.startsWith(item.href)
                                return (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={`flex items-center gap-4 px-6 py-4 rounded-2xl text-[10px] font-black tracking-[0.2em] uppercase transition-luxury ${isActive
                                            ? 'bg-primary text-white shadow-2xl shadow-primary/20'
                                            : 'text-foreground/40 hover:text-white hover:bg-white/5'
                                            }`}
                                    >
                                        <Icon className="w-5 h-5" />
                                        {item.label}
                                    </Link>
                                )
                            })}
                        </nav>
                    </div>
                </aside>

                {/* Mobile nav */}
                <div className={`md:hidden fixed bottom-0 left-0 right-0 z-50 flex border-t p-2 ${theme === 'dark' ? 'bg-black/20 backdrop-blur-xl border-t border-white/10 border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                    }`}>
                    {adminNav.map((item) => {
                        const Icon = item.icon
                        const isActive = pathname === item.href
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex-1 flex flex-col items-center gap-1 py-2 rounded-lg text-xs transition-colors ${isActive
                                    ? 'text-violet-500'
                                    : theme === 'dark' ? 'text-gray-400' : 'text-gray-500'
                                    }`}
                            >
                                <Icon className="w-5 h-5" />
                                {item.label}
                            </Link>
                        )
                    })}
                </div>

                {/* Content */}
                <main className="flex-1 p-6 md:p-8 overflow-auto pb-24 md:pb-8">
                    {children}
                </main>
            </div>
        </>
    )
}
