'use client'

import { motion, useScroll, useSpring } from 'framer-motion'

export function ScrollProgress() {
    const { scrollYProgress } = useScroll()
    const scaleX = useSpring(scrollYProgress, {
        stiffness: 100,
        damping: 30,
        restDelta: 0.001
    })

    return (
        <motion.div
            className="fixed top-0 left-0 right-0 h-[3px] z-[100] origin-left"
            style={{
                scaleX,
                background: 'linear-gradient(90deg, hsl(var(--primary)) 0%, hsl(var(--primary) / 0.8) 100%)',
                boxShadow: '0 0 8px 2px hsl(var(--primary) / 0.6), 0 0 20px 4px hsl(var(--primary) / 0.3)',
            }}
        />
    )
}
