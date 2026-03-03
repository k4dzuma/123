'use client'

import { motion } from 'framer-motion'
import { ReactNode } from 'react'

interface TextRevealProps {
    children: string
    className?: string
    delay?: number
}

export function TextReveal({ children, className = '', delay = 0 }: TextRevealProps) {
    // Split text by lines (or just treat as single if no newlines, but we aim for line-by-line look)
    const lines = children.split('\n')

    const containerVariants = {
        hidden: { opacity: 0 },
        visible: {
            opacity: 1,
            transition: {
                staggerChildren: 0.12,
                delayChildren: delay,
            },
        },
    }

    const itemVariants = {
        hidden: { y: '102%', opacity: 0 },
        visible: {
            y: 0,
            opacity: 1,
            transition: {
                duration: 0.8,
                ease: "easeOut"
            } as any,
        },
    }

    return (
        <motion.span
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
            className={`inline-block overflow-hidden ${className}`}
        >
            {lines.map((line, index) => (
                <span key={index} className="block overflow-hidden py-1">
                    <motion.span
                        variants={itemVariants}
                        className="block pointer-events-none"
                    >
                        {line}
                    </motion.span>
                </span>
            ))}
        </motion.span>
    )
}
