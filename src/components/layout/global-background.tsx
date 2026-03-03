'use client'

import { Canvas } from '@react-three/fiber'
import { motion } from 'framer-motion'
import { Sparkles } from '@react-three/drei'
import { useTheme } from '@/components/theme/theme-provider'

export function GlobalBackground() {
    const { theme } = useTheme()
    const isDark = theme === 'dark'

    return (
        <div className="fixed inset-0 w-full h-full z-[-1] pointer-events-none overflow-hidden bg-background">
            {/* Ambient Glows */}
            <motion.div
                animate={{
                    x: [0, 100, 0],
                    y: [0, 50, 0],
                    scale: [1, 1.2, 1],
                }}
                transition={{
                    duration: 20,
                    repeat: Infinity,
                    ease: "linear"
                }}
                className="absolute top-[-10%] left-[-10%] w-[60%] h-[60%] rounded-full bg-primary/10 blur-[120px]"
            />
            <motion.div
                animate={{
                    x: [0, -80, 0],
                    y: [0, -60, 0],
                    scale: [1, 1.1, 1],
                }}
                transition={{
                    duration: 25,
                    repeat: Infinity,
                    ease: "linear"
                }}
                className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-blue-900/20 blur-[100px]"
            />

            <Canvas
                camera={{ position: [0, 0, 10], fov: 50 }}
                gl={{ antialias: true, alpha: true }}
                dpr={[1, 2]}
                style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', zIndex: 1 }}
            >
                <Sparkles
                    count={450}
                    scale={30}
                    size={3}
                    speed={0.4}
                    opacity={isDark ? 0.35 : 0.15}
                    color="#38bdf8"
                />
                <Sparkles
                    count={200}
                    scale={35}
                    size={4}
                    speed={0.2}
                    opacity={isDark ? 0.25 : 0.1}
                    color="#ffffff"
                />
            </Canvas>

            {/* Vignette & Ambient Glows */}
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,transparent_0%,hsl(var(--background))_80%)] opacity-60" />
        </div>
    )
}
