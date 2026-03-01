'use client'

import { useRef, useEffect } from 'react'
import { Plus } from 'lucide-react'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const CARDS = [
    {
        step: '01',
        title: 'Открытие',
        description: 'Навигация сквозь века. Каждый артефакт в нашем архиве имеет глубину и контекст, раскрывающийся при внимательном изучении.'
    },
    {
        step: '02',
        title: 'Осмысление',
        description: 'Взаимодействие с историей. В лекциях и интерактивных квестах мы пересобираем факты, делая их частью нашего опыта.'
    },
    {
        step: '03',
        title: 'Сохранение',
        description: 'Оцифровка памяти. Добавляйте, комментируйте и формируйте собственный слепок исторического пути вместе с нами.'
    }
]

export function StickyStackingArchive() {
    const containerRef = useRef<HTMLDivElement>(null)
    const cardsRef = useRef<(HTMLDivElement | null)[]>([])

    useEffect(() => {
        const ctx = gsap.context(() => {
            // Loop through each card except the last one
            cardsRef.current.forEach((card, index) => {
                if (!card || index === cardsRef.current.length - 1) return

                // Set up the ScrollTrigger timeline for this card
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: card,
                        start: 'top top',
                        end: 'bottom top',
                        scrub: true,
                        pin: true,
                        pinSpacing: false,
                    }
                })

                // As the next card comes up, scale down, blur and fade the current card
                tl.to(card, {
                    scale: 0.9,
                    opacity: 0.5,
                    filter: 'blur(20px)',
                    ease: 'none',
                })
            })
        }, containerRef)

        return () => ctx.revert()
    }, [])

    return (
        <section ref={containerRef} className="relative w-full bg-background pb-32">
            {CARDS.map((card, idx) => (
                <div
                    key={idx}
                    ref={(el) => { cardsRef.current[idx] = el }}
                    className="h-screen w-full flex items-center justify-center sticky top-0 p-6 md:p-12 overflow-hidden"
                >
                    {/* Card Surface */}
                    <div className="relative w-full max-w-[1200px] h-[70vh] rounded-[2rem] md:rounded-[3rem] border border-white/10 dark:bg-black/80 bg-white/80 backdrop-blur-3xl shadow-2xl flex flex-col justify-between p-10 md:p-20 overflow-hidden group">

                        {/* Background Graphic / Animation based on index */}
                        <div className="absolute inset-0 pointer-events-none opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-1000 flex items-center justify-center overflow-hidden">
                            {idx === 0 && (
                                <svg viewBox="0 0 100 100" className="w-[150%] h-[150%] max-w-none animate-[spin_60s_linear_infinite]">
                                    <path d="M50 0 L100 50 L50 100 L0 50 Z" fill="none" stroke="currentColor" strokeWidth="0.5" />
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" strokeWidth="0.2" strokeDasharray="2 4" />
                                    <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" strokeWidth="0.1" />
                                </svg>
                            )}
                            {idx === 1 && (
                                <div className="w-full h-full bg-[linear-gradient(to_right,#8882_1px,transparent_1px),linear-gradient(to_bottom,#8882_1px,transparent_1px)] bg-[size:40px_40px]">
                                    <div className="absolute top-0 bottom-0 w-[2px] bg-primary blur-[2px] animate-[ping_3s_linear_infinite]" style={{ left: '50%' }} />
                                </div>
                            )}
                            {idx === 2 && (
                                <svg viewBox="0 0 200 50" className="w-full h-auto text-primary">
                                    <path
                                        className="animate-[dash_3s_linear_infinite]"
                                        d="M0 25 L40 25 L50 10 L60 40 L70 25 L200 25"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1"
                                        strokeDasharray="200"
                                        strokeDashoffset="200"
                                    />
                                </svg>
                            )}
                        </div>

                        {/* Top row */}
                        <div className="flex justify-between items-start relative z-10">
                            <span className="font-mono text-xl tracking-widest text-muted-foreground">
                                [ {card.step} / {CARDS.length < 10 ? `0${CARDS.length}` : CARDS.length} ]
                            </span>
                            <Plus className="w-8 h-8 text-muted-foreground opacity-50" />
                        </div>

                        {/* Content content */}
                        <div className="relative z-10 max-w-2xl">
                            <h3 className="text-4xl md:text-7xl font-light tracking-tighter mb-8 uppercase text-balance">
                                {card.title}
                            </h3>
                            <p className="text-lg md:text-2xl text-muted-foreground font-light tracking-wide leading-relaxed">
                                {card.description}
                            </p>
                        </div>

                        {/* Edge highlights */}
                        <div className="absolute inset-0 border border-primary/20 rounded-[2rem] md:rounded-[3rem] opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none" />
                    </div>
                </div>
            ))}
        </section>
    )
}
