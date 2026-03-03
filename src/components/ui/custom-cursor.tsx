'use client'

import { useEffect, useState } from 'react'
import { motion, useSpring } from 'framer-motion'

export function CustomCursor() {
    const [isHovering, setIsHovering] = useState(false)
    const [isVisible, setIsVisible] = useState(false)

    // Smooth trailing physics using framer-motion springs
    const cursorX = useSpring(-100, { stiffness: 500, damping: 28, mass: 0.5 })
    const cursorY = useSpring(-100, { stiffness: 500, damping: 28, mass: 0.5 })

    useEffect(() => {
        // Do not activate on touch devices
        if (window.matchMedia('(hover: none)').matches) return

        setIsVisible(true)

        // Hide default cursor across the entire document once loaded
        document.documentElement.classList.add('custom-cursor-active')

        const updateMousePosition = (e: MouseEvent) => {
            cursorX.set(e.clientX)
            cursorY.set(e.clientY)
        }

        const handleMouseOver = (e: MouseEvent) => {
            const target = e.target as HTMLElement
            // Detect if element is meant to be interactive
            const isClickable =
                window.getComputedStyle(target).cursor === 'pointer' ||
                target.tagName.toLowerCase() === 'a' ||
                target.tagName.toLowerCase() === 'button' ||
                target.closest('a') !== null ||
                target.closest('button') !== null

            setIsHovering(isClickable)
        }

        window.addEventListener('mousemove', updateMousePosition)
        window.addEventListener('mouseover', handleMouseOver)

        return () => {
            window.removeEventListener('mousemove', updateMousePosition)
            window.removeEventListener('mouseover', handleMouseOver)
            document.documentElement.classList.remove('custom-cursor-active')
        }
    }, [cursorX, cursorY])

    if (!isVisible) return null

    return (
        <motion.div
            className="fixed top-0 left-0 z-[10000] rounded-full pointer-events-none hidden md:block"
            style={{
                x: cursorX,
                y: cursorY,
                translateX: '-50%',
                translateY: '-50%',
                mixBlendMode: 'difference'
            }}
            animate={{
                width: isHovering ? 56 : 16,
                height: isHovering ? 56 : 16,
                backgroundColor: isHovering ? 'transparent' : 'white',
                border: isHovering ? '1px solid white' : '1px solid transparent',
            }}
            transition={{ type: 'spring', stiffness: 300, damping: 20 }}
        />
    )
}
