'use client'

import { useRef, useState } from 'react'
import { useTheme } from '@/components/theme/theme-provider'

export function SpotlightCard({ children, className = '' }: { children: React.ReactNode, className?: string }) {
    const divRef = useRef<HTMLDivElement>(null)
    const [position, setPosition] = useState({ x: 0, y: 0 })
    const [opacity, setOpacity] = useState(0)
    const { theme } = useTheme()

    const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
        if (!divRef.current) return
        const rect = divRef.current.getBoundingClientRect()
        setPosition({ x: e.clientX - rect.left, y: e.clientY - rect.top })
    }

    const handleMouseEnter = () => setOpacity(1)
    const handleMouseLeave = () => setOpacity(0)

    // Using primary color for the spotlight
    const spotlightColor = theme === 'dark' ? 'rgba(56, 189, 248, 0.15)' : 'rgba(56, 189, 248, 0.1)'

    return (
        <div
            ref={divRef}
            onMouseMove={handleMouseMove}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
            className={`relative overflow-hidden ${className}`}
        >
            <div
                className="pointer-events-none absolute -inset-px transition duration-500 z-0"
                style={{
                    opacity,
                    background: `radial-gradient(800px circle at ${position.x}px ${position.y}px, ${spotlightColor}, transparent 40%)`,
                }}
            />
            <div className="relative z-10 w-full h-full flex flex-col">
                {children}
            </div>
        </div>
    )
}
