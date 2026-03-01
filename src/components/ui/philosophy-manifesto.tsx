'use client'

import { useRef, useEffect } from 'react'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

export function PhilosophyManifesto() {
    const containerRef = useRef<HTMLDivElement>(null)
    const textRef = useRef<HTMLHeadingElement>(null)

    useEffect(() => {
        const ctx = gsap.context(() => {
            // Parallax effect on the background texture
            gsap.to('.manifesto-bg', {
                y: '20%',
                ease: 'none',
                scrollTrigger: {
                    trigger: containerRef.current,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: true,
                }
            })

            // Fade up text on scroll
            gsap.from(textRef.current, {
                y: 50,
                opacity: 0,
                duration: 1.5,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: containerRef.current,
                    start: 'top 70%',
                }
            })
        }, containerRef)

        return () => ctx.revert()
    }, [])

    return (
        <section ref={containerRef} className="relative w-full py-40 md:py-64 bg-background overflow-hidden flex items-center justify-center">
            {/* Organic texture parallax background */}
            <div
                className="manifesto-bg absolute inset-0 w-full h-[150%] -top-[25%] opacity-20 pointer-events-none"
                style={{
                    backgroundImage: 'url("https://images.unsplash.com/photo-1596401057633-54a8fea8ce64?q=80&w=2000&auto=format&fit=crop")',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                    filter: 'grayscale(50%) contrast(1.5)',
                }}
            />

            {/* Ambient Lighting to mix with texture */}
            <div className="absolute top-1/4 left-0 w-[500px] h-[500px] bg-violet-600/10 rounded-full blur-[100px] pointer-events-none mix-blend-screen" />
            <div className="absolute bottom-0 right-1/4 w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-[120px] pointer-events-none mix-blend-screen" />

            <div className="container relative z-10 px-6 lg:px-12 mx-auto text-center max-w-5xl">
                <div ref={textRef} className="space-y-12">
                    <p className="text-xl md:text-2xl text-neutral-400 font-light tracking-wide uppercase">
                        Большинство музеев фокусируются на: <br />
                        <span className="text-white mt-2 block font-medium">статичном хранении прошлого.</span>
                    </p>

                    <h2 className="text-5xl md:text-7xl lg:text-8xl font-black tracking-tighter text-white leading-[1.1]">
                        Мы фокусируемся на: <br />
                        <span className="text-primary italic font-serif">живом взаимодействии.</span>
                    </h2>
                </div>
            </div>
        </section>
    )
}
