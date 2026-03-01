'use client'

import { useTheme } from '@/components/theme/theme-provider'
import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'

export function GlassBackground() {
    const { theme } = useTheme()
    const [mounted, setMounted] = useState(false)

    useEffect(() => {
        setMounted(true)
    }, [])

    if (!mounted) return null

    const isDark = theme === 'dark'

    return (
        <div className="fixed inset-0 w-full h-full -z-50 overflow-hidden pointer-events-none">
            {/* Base background color */}
            <div
                className={`absolute inset-0 transition-colors duration-1000 ${isDark ? 'bg-[#050505]' : 'bg-[#fdfdfc]'
                    }`}
            />

            {/* Subtle gallery lighting gradients instead of neon orbs */}
            <div className={`absolute inset-0 ${isDark ? 'opacity-30' : 'opacity-20'}`}>
                {/* Soft top spotlight */}
                <motion.div
                    animate={{
                        opacity: [0.3, 0.5, 0.3],
                        scale: [1, 1.05, 1],
                    }}
                    transition={{ duration: 15, repeat: Infinity, ease: 'easeInOut' }}
                    className={`absolute top-[-20%] left-[20%] w-[60%] h-[60%] rounded-full blur-[150px]
            ${isDark ? 'bg-zinc-700/40' : 'bg-white/80'}`}
                />

                {/* Gentle gold wash bottom right */}
                <motion.div
                    animate={{
                        opacity: [0.1, 0.2, 0.1],
                    }}
                    transition={{ duration: 20, repeat: Infinity, ease: 'easeInOut' }}
                    className={`absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full blur-[140px]
            ${isDark ? 'bg-[#d4af37]/10' : 'bg-[#d4af37]/20'}`}
                />

                {/* High-end grain texture overlay for tactile, editorial museum feel */}
                <div
                    className="absolute inset-0 opacity-[0.035] mix-blend-overlay"
                    style={{ backgroundImage: 'url("data:image/svg+xml,%3Csvg viewBox=%220 0 400 400%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E")' }}
                />
            </div>
        </div>
    )
}
