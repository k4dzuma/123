'use client'

import React, { useEffect, useRef, useState } from 'react'
import Link from 'next/link'
import { motion, AnimatePresence } from 'framer-motion'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import {
  ArrowRight,
  Menu,
  X,
  Plus,
  Terminal,
  Cpu,
  Layers,
  Circle,
  ShieldCheck
} from 'lucide-react'
import { useTheme } from '@/components/theme/theme-provider'
import { Hero3DScene } from '@/components/3d/hero-scene'
import { Footer } from '@/components/layout/footer'

gsap.registerPlugin(ScrollTrigger)


/* ─── B. HERO SECTION: THE OPENING SHOT ─── */
function Hero() {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      const tl = gsap.timeline()
      tl.from('.hero-part', {
        y: 40,
        opacity: 0,
        duration: 1.2,
        stagger: 0.15,
        ease: 'power3.out'
      })
        .from('.hero-scene', {
          opacity: 0,
          scale: 1.1,
          duration: 2,
          ease: 'power2.out'
        }, '-=1')
    }, containerRef)

    return () => ctx.revert()
  }, [])

  return (
    <section ref={containerRef} className="relative h-[100dvh] w-full flex items-end justify-start overflow-hidden bg-transparent">
      {/* Background with 3D and Gradient */}
      <div className="hero-scene absolute inset-0 z-0 pointer-events-none">
        <Hero3DScene />
        <div className="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent opacity-60" />
      </div>

      <div className="relative z-10 w-full max-w-7xl mx-auto px-8 lg:px-16 pb-24 lg:pb-32">
        <div className="max-w-4xl space-y-6">
          <div className="hero-part">
            <h2 className="text-xs md:text-sm uppercase tracking-[0.4em] font-medium text-primary mb-4 block">
              Цифровое наследие // est. 2026
            </h2>
          </div>

          <div className="hero-part">
            <h1 className="leading-[0.85] tracking-tighter uppercase font-sans font-black text-white" style={{ fontSize: 'clamp(3rem, 10vw, 8rem)' }}>
              ВИРТУАЛЬНЫЙ <br />
              <div className="h-[1.1em] overflow-hidden">
                <RotatingWord words={['МУЗЕЙ.', 'АРХИВ.', 'МИР.', 'ОПЫТ.', 'ПОРТАЛ.']} />
              </div>
            </h1>
          </div>

          <div className="hero-part pt-4">
            <p className="text-sm md:text-base text-foreground/60 max-w-md tracking-wide leading-relaxed font-light">
              Человек и Время — Путешествие сквозь эпоху. Погрузитесь в богатую историю нашего колледжа: от первых дней до наших времен.
            </p>
          </div>

          <div className="hero-part pt-8 flex gap-4">
            <Link href="/sections">
              <button className="group relative bg-white text-black px-10 py-5 rounded-full text-xs uppercase tracking-[0.3em] font-black magnetic-btn overflow-hidden">
                <span className="relative z-10">Экспозиция</span>
                <div className="absolute inset-0 bg-primary translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-[cubic-bezier(0.25,0.46,0.45,0.94)]" />
              </button>
            </Link>
            <button className="bg-white/5 border border-white/10 text-white px-10 py-5 rounded-full text-xs uppercase tracking-[0.3em] font-bold magnetic-btn">
              Видео-тур
            </button>
          </div>
        </div>
      </div>
    </section>
  )
}

function RotatingWord({ words }: { words: string[] }) {
  const [index, setIndex] = useState(0)

  useEffect(() => {
    const timer = setInterval(() => {
      setIndex((prev) => (prev + 1) % words.length)
    }, 3000)
    return () => clearInterval(timer)
  }, [words.length])

  return (
    <AnimatePresence mode="wait">
      <motion.span
        key={words[index]}
        initial={{ y: 40, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        exit={{ y: -40, opacity: 0 }}
        transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
        className="block font-serif italic text-primary lowercase tracking-normal"
      >
        {words[index]}
      </motion.span>
    </AnimatePresence>
  )
}

/* ─── C. FEATURES: INTERACTIVE FUNCTIONAL ARTIFACTS ─── */
function Features() {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from('.feature-card', {
        scrollTrigger: {
          trigger: containerRef.current,
          start: 'top 80%',
        },
        y: 60,
        opacity: 0,
        duration: 1.2,
        stagger: 0.2,
        ease: 'power3.out'
      })
    }, containerRef)
    return () => ctx.revert()
  }, [])

  return (
    <section ref={containerRef} className="relative py-32 md:py-56 bg-transparent">
      <div className="container mx-auto px-8 lg:px-16">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-8">

          {/* Card 1: Diagnostic Shuffler */}
          <div className="feature-card group flex flex-col gap-8">
            <div className="relative h-80 bg-secondary rounded-[3rem] p-10 overflow-hidden border border-white/5 flex items-center justify-center">
              <DiagnosticShuffler />
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold uppercase tracking-tight">Экспозиции</h3>
              <p className="text-sm text-foreground/50 font-light leading-relaxed">
                Наши кураторы собрали уникальные коллекции, оживляющие историю через цифровые экосистемы.
              </p>
            </div>
          </div>

          {/* Card 2: Telemetry Typewriter */}
          <div className="feature-card group flex flex-col gap-8 lg:mt-24">
            <div className="relative h-80 bg-secondary rounded-[3rem] p-10 overflow-hidden border border-white/5 font-mono text-[10px]">
              <div className="flex items-center gap-2 mb-6">
                <div className="w-2 h-2 rounded-full bg-primary animate-pulse" />
                <span className="text-primary/60 uppercase tracking-widest">Поток метаданных</span>
              </div>
              <TelemetryTypewriter />
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold uppercase tracking-tight">Интерактив</h3>
              <p className="text-sm text-foreground/50 font-light leading-relaxed">
                Почувствуйте пульс истории через живую телеметрию и аутентичные архивные данные.
              </p>
            </div>
          </div>

          {/* Card 3: Cursor Protocol Scheduler */}
          <div className="feature-card group flex flex-col gap-8 lg:mt-48">
            <div className="relative h-80 bg-secondary rounded-[3rem] p-10 overflow-hidden border border-white/5">
              <ProtocolScheduler />
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold uppercase tracking-tight">Квесты</h3>
              <p className="text-sm text-foreground/50 font-light leading-relaxed">
                Взаимодействуйте с архивом через игровые механики и нейронную навигацию.
              </p>
            </div>
          </div>

        </div>
      </div>
    </section>
  )
}

function DiagnosticShuffler() {
  const [items, setItems] = useState([
    { label: 'История Техникума', active: true },
    { label: 'Театр моды', active: false },
    { label: 'Страницы войны', active: false },
  ])

  useEffect(() => {
    const interval = setInterval(() => {
      setItems(prev => {
        const next = [...prev]
        const first = next.shift()
        if (first) next.push(first)
        return next
      })
    }, 3000)
    return () => clearInterval(interval)
  }, [])

  return (
    <div className="flex flex-col items-center justify-center w-full h-full relative">
      {items.map((item, idx) => (
        <motion.div
          key={item.label}
          layout
          initial={false}
          animate={{
            y: idx * 40 - 40,
            opacity: idx === 1 ? 1 : 0.4,
            scale: idx === 1 ? 1 : 0.85,
            zIndex: idx === 1 ? 10 : 0
          }}
          transition={{ type: 'spring', stiffness: 300, damping: 25 }}
          className={`absolute px-8 py-4 rounded-full border border-white/10 bg-background/80 backdrop-blur-md uppercase text-[10px] tracking-[0.3em] font-bold ${idx === 1 ? 'text-primary' : 'text-foreground/40'}`}
        >
          {item.label}
        </motion.div>
      ))}
    </div>
  )
}

function TelemetryTypewriter() {
  const messages = [
    "АУТЕНТИФИКАЦИЯ АРХИВА...",
    "ДОСТУП РАЗРЕШЕН: СЕКТОР 1930",
    "РАСШИФРОВКА МЕТАДАННЫХ...",
    "СТАБИЛИЗАЦИЯ ПОТОКА ПАМЯТИ...",
    "КАНАЛ СВЯЗИ АКТИВЕН // ZENITH",
    "СКАНИРОВАНИЕ ЭКСПОНАТОВ..."
  ]
  const [text, setText] = useState("")
  const [msgIdx, setMsgIdx] = useState(0)
  const [charIdx, setCharIdx] = useState(0)

  useEffect(() => {
    if (charIdx < messages[msgIdx].length) {
      const timer = setTimeout(() => {
        setText(prev => prev + messages[msgIdx][charIdx])
        setCharIdx(prev => prev + 1)
      }, 50)
      return () => clearTimeout(timer)
    } else {
      const timer = setTimeout(() => {
        setText("")
        setCharIdx(0)
        setMsgIdx(prev => (prev + 1) % messages.length)
      }, 2000)
      return () => clearTimeout(timer)
    }
  }, [charIdx, msgIdx])

  return (
    <div className="space-y-1">
      <div className="text-foreground/80 leading-loose">
        {text}
        <span className="inline-block w-1.5 h-3 bg-primary ml-1 animate-pulse" />
      </div>
    </div>
  )
}

function ProtocolScheduler() {
  const days = ['S', 'M', 'T', 'W', 'T', 'F', 'S']
  const [activeDay, setActiveDay] = useState(2)
  const cursorRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      const tl = gsap.timeline({ repeat: -1 })
      tl.to(cursorRef.current, { x: 100, y: 40, duration: 1.5, ease: 'power2.inOut' })
        .to(cursorRef.current, { scale: 0.9, duration: 0.1 })
        .call(() => setActiveDay(prev => (prev + 1) % 7))
        .to(cursorRef.current, { scale: 1, duration: 0.1 })
        .to(cursorRef.current, { x: 180, y: 120, duration: 1, ease: 'power2.inOut' })
        .to(cursorRef.current, { opacity: 0, duration: 0.5 })
        .set(cursorRef.current, { x: 0, y: 0, opacity: 1 })
    })
    return () => ctx.revert()
  }, [])

  return (
    <div className="w-full h-full flex flex-col items-center justify-center gap-6 relative">
      <div className="grid grid-cols-7 gap-4">
        {days.map((day, idx) => (
          <div
            key={idx}
            className={`w-8 h-8 rounded-full border border-white/5 flex items-center justify-center text-[8px] font-bold transition-luxury ${activeDay === idx ? 'bg-primary text-primary-foreground border-primary scale-110 shadow-lg shadow-primary/30' : 'bg-secondary text-foreground/40'}`}
          >
            {day}
          </div>
        ))}
      </div>
      <div className="bg-primary/20 border border-primary/40 px-6 py-2 rounded-full text-[8px] uppercase tracking-widest font-bold text-primary">
        Сохранить протокол
      </div>
      {/* Animated Cursor */}
      <div ref={cursorRef} className="absolute top-1/2 left-1/4 pointer-events-none z-20">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" className="text-white drop-shadow-md">
          <path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z" fill="currentColor" />
        </svg>
      </div>
    </div>
  )
}

/* ─── D. PHILOSOPHY: THE MANIFESTO ─── */
function Philosophy() {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from('.manifesto-part', {
        scrollTrigger: {
          trigger: containerRef.current,
          start: 'top 70%',
        },
        y: 30,
        opacity: 0,
        duration: 1.5,
        stagger: 0.4,
        ease: 'power3.out'
      })
    }, containerRef)
    return () => ctx.revert()
  }, [])

  return (
    <section ref={containerRef} className="relative w-full py-48 md:py-80 bg-transparent overflow-hidden flex items-center justify-center">
      {/* Background Dimmer */}
      <div className="absolute inset-0 bg-secondary/80 backdrop-blur-sm" />
      {/* Parallax Texture */}
      <div
        className="absolute inset-0 opacity-10 grayscale brightness-50 mix-blend-overlay"
        style={{
          backgroundImage: 'url("https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2000&auto=format&fit=crop")',
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        }}
      />
      <div className="container relative z-10 mx-auto px-8 max-w-6xl text-center space-y-12">
        <div className="manifesto-part">
          <p className="text-lg md:text-2xl text-foreground/40 font-light tracking-wide uppercase">
            Большинство архивов это: <span className="text-foreground/80 font-medium">Статичное хранение.</span>
          </p>
        </div>
        <div className="manifesto-part">
          <h2 className="leading-[1.1] tracking-tighter uppercase font-sans font-black text-white" style={{ fontSize: 'clamp(2.5rem, 8vw, 6rem)' }}>
            Мы создаем: <br />
            <span className="font-serif italic text-primary lowercase tracking-normal">Живую память.</span>
          </h2>
        </div>
      </div>
    </section>
  )
}

/* ─── E. PROTOCOL: STICKY STACKING ARCHIVE ─── */
function Protocol() {
  const containerRef = useRef<HTMLDivElement>(null)
  const sectionRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      const cards = gsap.utils.toArray('.stack-card')
      cards.forEach((card: any, i: number) => {
        // Set initial z-index so cards don't overlap randomly
        gsap.set(card, { zIndex: 10 + i })

        if (i === cards.length - 1) return

        gsap.to(card, {
          scrollTrigger: {
            trigger: card,
            start: "top top",
            pin: true,
            pinSpacing: false,
            scrub: true,
            invalidateOnRefresh: true,
            anticipatePin: 1,
          },
          scale: 0.9,
          opacity: 0.5,
          filter: "blur(20px)",
          ease: "none"
        })
      })
    }, sectionRef)
    return () => ctx.revert()
  }, [])

  const steps = [
    { title: 'Архив', desc: 'Сканирование десятилетий истории через цифровые линзы времени.', icon: <Terminal size={40} /> },
    { title: 'Выпускники', desc: 'Валентина Терешкова, Борис Власов — мы гордимся каждым героем.', icon: <Cpu size={40} /> },
    { title: 'Наследие', desc: 'Ваш вклад в общую копилку истории колледжа и текстильного края.', icon: <Layers size={40} /> },
  ]

  return (
    <section ref={sectionRef} className="relative bg-transparent">
      {steps.map((step, idx) => (
        <div
          key={idx}
          className="stack-card h-screen w-full flex items-center justify-center px-8 lg:px-16"
        >
          <div className="w-full max-w-6xl h-[60vh] bg-secondary rounded-[3rem] border border-white/5 p-16 md:p-24 flex flex-col md:flex-row gap-16 items-center shadow-2xl relative overflow-hidden group">
            <div className="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-1000 bg-primary/20 pointer-events-none" />

            <div className="w-full md:w-1/2 space-y-8">
              <span className="font-mono text-xs text-primary/60 tracking-[0.5em] uppercase">Этап 0{idx + 1}</span>
              <h3 className="text-4xl md:text-7xl font-sans font-black uppercase tracking-tighter leading-none">{step.title}</h3>
              <p className="text-foreground/40 text-lg font-light leading-relaxed max-w-sm">{step.desc}</p>
            </div>

            <div className="w-full md:w-1/2 flex items-center justify-center">
              <div className="w-64 h-64 border border-white/10 rounded-full flex items-center justify-center relative">
                <div className="w-48 h-48 border border-primary/20 rounded-full animate-spin [animation-duration:20s]" />
                <div className="absolute text-primary">
                  {step.icon}
                </div>
              </div>
            </div>
          </div>
        </div>
      ))}
    </section>
  )
}



/* ─── MAIN PAGE PAGE ─── */
export default function Page() {
  return (
    <main className="relative min-h-screen">
      <Hero />
      <Features />
      <Philosophy />
      <Protocol />

      {/* Get Started Section */}
      <section className="py-48 px-8 flex flex-col items-center text-center gap-12 bg-transparent relative overflow-hidden">
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary/5 rounded-full blur-[200px] pointer-events-none" />
        <ShieldCheck className="text-primary w-16 h-16 animate-pulse" />
        <h2 className="text-4xl md:text-7xl font-sans font-black uppercase tracking-tighter leading-tight max-w-4xl">
          Оставь свой след в <span className="font-serif italic text-primary lowercase tracking-normal">вечном архиве.</span>
        </h2>
        <Link href="/register">
          <button className="bg-white text-black px-16 py-6 rounded-full text-xs uppercase tracking-[0.3em] font-black magnetic-btn">
            Присоединиться к нам
          </button>
        </Link>
      </section>

      <Footer />
    </main>
  )
}
