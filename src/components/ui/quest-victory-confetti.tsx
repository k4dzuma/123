'use client'

import { motion } from 'framer-motion'
import { useEffect, useState } from 'react'

interface ConfettiPieceProps {
    color: string
}

function ConfettiPiece({ color }: ConfettiPieceProps) {
    const randomX = Math.random() * 100 // 0 to 100vw
    const randomDelay = Math.random() * 2
    const randomDuration = 2 + Math.random() * 3
    const randomRotation = Math.random() * 360
    const randomSize = 5 + Math.random() * 8

    return (
        <motion.div
            initial={{
                y: -20,
                x: `${randomX}vw`,
                rotate: 0,
                opacity: 1
            }}
            animate={{
                y: '110dvh',
                rotate: randomRotation + 720,
                opacity: [1, 1, 0]
            }}
            transition={{
                duration: randomDuration,
                delay: randomDelay,
                ease: "easeIn"
            }}
            style={{
                position: 'fixed',
                width: randomSize,
                height: randomSize,
                backgroundColor: color,
                borderRadius: Math.random() > 0.5 ? '50%' : '2px',
                zIndex: 100,
                pointerEvents: 'none'
            }}
        />
    )
}

export function QuestVictoryConfetti() {
    const [pieces, setPieces] = useState<number[]>([])
    const colors = ['#C9A84C', '#FAF8F5', '#38bdf8', '#7B61FF', '#FFD700']

    useEffect(() => {
        setPieces(Array.from({ length: 80 }, (_, i) => i))
    }, [])

    return (
        <div className="fixed inset-0 pointer-events-none z-[110] overflow-hidden">
            {pieces.map((i) => (
                <ConfettiPiece key={i} color={colors[i % colors.length]} />
            ))}
        </div>
    )
}
