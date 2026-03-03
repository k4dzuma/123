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
            <div className="relative h-80 bg-secondary/60 rounded-[3rem] p-10 overflow-hidden border border-white/15 shadow-[0_0_40px_rgba(var(--primary-rgb),0.08)] flex items-center justify-center">
              <DiagnosticShuffler />
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold uppercase tracking-tight">Экспозиции</h3>
              <p className="text-sm text-foreground/50 font-light leading-relaxed">
                Наши кураторы собрали уникальные коллекции, оживляющие историю через цифровые экосистемы.
              </p>
            </div>
          </div>

          {/* Card 2: Artifact Scanner */}
          <div className="feature-card group flex flex-col gap-8 lg:mt-24">
            <div className="relative h-80 bg-secondary/60 rounded-[3rem] p-10 overflow-hidden border border-white/15 shadow-[0_0_40px_rgba(var(--primary-rgb),0.08)] font-mono">
              <ArtifactScanner />
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold uppercase tracking-tight">Интерактив</h3>
              <p className="text-sm text-foreground/50 font-light leading-relaxed">
                Исследуйте экспонаты в дополненной реальности — каждый артефакт хранит живую историю.
              </p>
            </div>
          </div>

          {/* Card 3: Quest Progress */}
          <div className="feature-card group flex flex-col gap-8 lg:mt-48">
            <div className="relative h-80 bg-secondary/60 rounded-[3rem] p-10 overflow-hidden border border-white/15 shadow-[0_0_40px_rgba(var(--primary-rgb),0.08)]">
              <QuestProgress />
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
            y: idx * 52 - 52,
            opacity: idx === 1 ? 1 : 0.5,
            scale: idx === 1 ? 1.05 : 0.88,
            zIndex: idx === 1 ? 10 : 0
          }}
          transition={{ type: 'spring', stiffness: 300, damping: 25 }}
          className={`absolute px-8 py-4 rounded-full border uppercase text-[11px] tracking-[0.3em] font-bold ${idx === 1
            ? 'bg-primary/20 border-primary/60 text-primary shadow-[0_0_20px_rgba(var(--primary-rgb),0.3)]'
            : 'bg-white/5 border-white/10 text-foreground/60'
            }`}
        >
          {item.label}
        </motion.div>
      ))}
    </div>
  )
}

function ArtifactScanner() {
  const SCAN_MS = 2400
  const PAUSE_MS = 1000
  const TICK_MS = 30
  const TOTAL_TICKS = SCAN_MS / TICK_MS

  const [scanY, setScanY] = useState(0)
  const [revealed, setRevealed] = useState(0)
  const [visible, setVisible] = useState(true)

  const fields = [
    { label: 'ОБЪЕКТ', value: 'Печатная машинка «Ятрань»' },
    { label: 'ПЕРИОД', value: '1975 — 1990 гг.' },
    { label: 'СТАТУС', value: 'ЭКСПОНИРУЕТСЯ' },
    { label: 'ФОНД', value: 'КТТ-архив' },
  ]

  useEffect(() => {
    let tick = 0
    let pausing = false
    const interval = setInterval(() => {
      if (pausing) return
      tick++
      const newY = Math.min(100, (tick / TOTAL_TICKS) * 100)
      setScanY(newY)
      setRevealed(Math.min(fields.length, Math.floor((tick / TOTAL_TICKS) * (fields.length + 0.5))))
      if (tick >= TOTAL_TICKS) {
        pausing = true
        setVisible(false)
        setTimeout(() => {
          tick = 0
          setScanY(0)
          setRevealed(0)
          setVisible(true)
          pausing = false
        }, PAUSE_MS)
      }
    }, TICK_MS)
    return () => clearInterval(interval)
  }, [])
  return (
    <div className="w-full h-full flex flex-col gap-3 relative overflow-hidden">
      {/* Typewriter silhouette + scan line */}
      <div className="relative flex items-center justify-center" style={{ height: 96 }}>
        <svg viewBox="0 0 140 80" className="w-44 h-24 text-white/30" fill="none">
          {/* Paper */}
          <rect x="50" y="2" width="40" height="22" rx="2" stroke="currentColor" strokeWidth="1" fill="hsl(var(--primary)/0.06)" />
          <line x1="56" y1="9" x2="84" y2="9" stroke="currentColor" strokeWidth="0.5" strokeDasharray="3 2" />
          <line x1="56" y1="14" x2="84" y2="14" stroke="currentColor" strokeWidth="0.5" strokeDasharray="3 2" />
          <line x1="56" y1="19" x2="76" y2="19" stroke="currentColor" strokeWidth="0.5" strokeDasharray="3 2" />
          {/* Platen roller */}
          <rect x="30" y="22" width="80" height="8" rx="4" stroke="currentColor" strokeWidth="1.5" />
          {/* Body */}
          <rect x="15" y="30" width="110" height="30" rx="5" stroke="currentColor" strokeWidth="1.5" />
          {/* Keys row 1 */}
          <rect x="22" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="33" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="44" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="55" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="66" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="77" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="88" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="99" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="110" y="36" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          {/* Keys row 2 */}
          <rect x="27" y="45" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="38" y="45" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          {/* Spacebar */}
          <rect x="49" y="45" width="44" height="5" rx="2" stroke="currentColor" strokeWidth="0.8" />
          <rect x="97" y="45" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          <rect x="108" y="45" width="7" height="5" rx="1.5" stroke="currentColor" strokeWidth="0.8" />
          {/* Feet */}
          <rect x="25" y="60" width="18" height="5" rx="2.5" stroke="currentColor" strokeWidth="1" />
          <rect x="97" y="60" width="18" height="5" rx="2.5" stroke="currentColor" strokeWidth="1" />
          {/* Spacebar highlight */}
          <line x1="52" y1="47.5" x2="90" y2="47.5" stroke="hsl(var(--primary)/0.5)" strokeWidth="1.5" />
        </svg>

        {/* Laser scan line — only within the SVG area, positioned absolutely inside the container */}
        <div
          className="absolute left-6 right-6 h-[2px] pointer-events-none rounded-full"
          style={{
            top: `${scanY}%`,
            background: 'linear-gradient(90deg, transparent 5%, hsl(var(--primary)) 35%, hsl(var(--primary)) 65%, transparent 95%)',
            boxShadow: '0 0 10px 3px hsl(var(--primary) / 0.6)',
            opacity: visible ? 1 : 0,
            transition: 'opacity 0.2s',
          }}
        />
      </div>

      {/* Data fields */}
      <div className="space-y-1.5 mt-1">
        {fields.map((f, i) => (
          <motion.div
            key={f.label}
            animate={{ opacity: i < revealed ? 1 : 0, x: i < revealed ? 0 : -10 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className="flex gap-3 items-baseline"
          >
            <span className="text-[9px] font-mono text-primary/60 tracking-[0.2em] w-20 shrink-0">{f.label}</span>
            <span className="text-[11px] font-mono text-white/90 tracking-wide">{f.value}</span>
          </motion.div>
        ))}
      </div>
    </div>
  )
}

function QuestProgress() {
  const achievements = [
    { icon: '🏛️', label: 'Историк', xp: 150 },
    { icon: '🔍', label: 'Следопыт', xp: 300 },
    { icon: '📜', label: 'Архивист', xp: 500 },
  ]
  const [unlockedIdx, setUnlockedIdx] = useState(0)
  const [displayXp, setDisplayXp] = useState(0)
  const targetXp = achievements[Math.min(unlockedIdx, achievements.length - 1)].xp
  useEffect(() => {
    const interval = setInterval(() => {
      setUnlockedIdx(prev => (prev + 1) % (achievements.length + 1))
    }, 3000)
    return () => clearInterval(interval)
  }, [])
  useEffect(() => {
    const target = achievements[Math.min(unlockedIdx, achievements.length - 1)].xp
    let current = 0
    const step = target / 40
    const timer = setInterval(() => {
      current += step
      if (current >= target) { setDisplayXp(target); clearInterval(timer) }
      else setDisplayXp(Math.floor(current))
    }, 40)
    return () => clearInterval(timer)
  }, [unlockedIdx])
  return (
    <div className="w-full h-full flex flex-col items-center justify-center gap-5">
      <div className="text-center">
        <p className="text-[9px] font-mono text-primary/60 tracking-[0.4em] uppercase mb-1">Очки опыта</p>
        <p className="text-4xl font-black font-sans tracking-tighter text-white">
          {displayXp.toLocaleString()}<span className="text-primary text-xl ml-1">XP</span>
        </p>
      </div>
      <div className="w-full h-3 bg-white/10 rounded-full overflow-hidden border border-white/5">
        <div
          className="h-full rounded-full"
          style={{
            width: `${Math.max(2, (displayXp / targetXp) * 100)}%`,
            background: 'linear-gradient(90deg, hsl(var(--primary)/0.8), hsl(var(--primary)))',
            boxShadow: '0 0 12px 2px hsl(var(--primary) / 0.7)',
            transition: 'width 0.08s linear',
          }}
        />
      </div>
      <div className="flex gap-4">
        {achievements.map((a, i) => (
          <motion.div
            key={a.label}
            animate={{ scale: i <= unlockedIdx ? 1 : 0.8, opacity: i <= unlockedIdx ? 1 : 0.25 }}
            transition={{ type: 'spring', stiffness: 400, damping: 20 }}
            className={`flex flex-col items-center gap-1.5 p-3 rounded-2xl border transition-colors ${i <= unlockedIdx
              ? 'border-primary/40 bg-primary/10 shadow-[0_0_12px_rgba(var(--primary-rgb),0.15)]'
              : 'border-white/5 bg-white/5'
              }`}
          >
            <span className="text-xl">{a.icon}</span>
            <span className="text-[8px] font-mono text-white/60 tracking-widest uppercase">{a.label}</span>
          </motion.div>
        ))}
      </div>
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
    <div className="space-y-2">
      <div className="text-white text-[11px] leading-loose tracking-wide font-mono">
        <span className="text-primary/70">&gt;&gt; </span>
        {text}
        <span className="inline-block w-[2px] h-4 bg-primary ml-1 animate-pulse shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]" />
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
    <div className="w-full h-full flex flex-col items-center justify-center gap-8 relative">
      <div className="grid grid-cols-7 gap-3">
        {days.map((day, idx) => (
          <div
            key={idx}
            className={`w-10 h-10 rounded-full border flex items-center justify-center text-[10px] font-black transition-all duration-300 ${activeDay === idx
              ? 'bg-primary text-primary-foreground border-primary scale-125 shadow-[0_0_16px_rgba(var(--primary-rgb),0.6)]'
              : 'bg-white/5 border-white/10 text-white/50'
              }`}
          >
            {day}
          </div>
        ))}
      </div>
      <div className="bg-primary/20 border border-primary/50 px-8 py-2.5 rounded-full text-[10px] uppercase tracking-widest font-bold text-primary shadow-[0_0_12px_rgba(var(--primary-rgb),0.2)]">
        Сохранить протокол
      </div>
      {/* Animated Cursor */}
      <div ref={cursorRef} className="absolute top-1/2 left-1/4 pointer-events-none z-20">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" className="text-white drop-shadow-[0_0_6px_rgba(255,255,255,0.8)]">
          <path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z" fill="currentColor" stroke="rgba(255,255,255,0.4)" strokeWidth="1" />
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
  const [activeIdx, setActiveIdx] = useState(0)
  const [prevIdx, setPrevIdx] = useState<number | null>(null)
  const [isVisible, setIsVisible] = useState(false)

  const steps = [
    { title: 'Архив', desc: 'Сканирование десятилетий истории через цифровые линзы времени.', icon: <Terminal size={40} /> },
    { title: 'Выпускники', desc: 'Валентина Терешкова, Борис Власов — мы гордимся каждым героем.', icon: <Cpu size={40} /> },
    { title: 'Наследие', desc: 'Ваш вклад в общую копилку истории колледжа и текстильного края.', icon: <Layers size={40} /> },
  ]

  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const wheelThrottle = useRef(false)
  const atStart = activeIdx === 0
  const atEnd = activeIdx === steps.length - 1

  // Detect visibility
  useEffect(() => {
    if (!containerRef.current) return
    const observer = new IntersectionObserver(
      ([entry]) => setIsVisible(entry.isIntersecting),
      { threshold: 0.4 }
    )
    observer.observe(containerRef.current)
    return () => observer.disconnect()
  }, [])

  // Helper: go to a specific card and reset timer
  const goTo = (idx: number) => {
    if (idx < 0 || idx >= steps.length) return
    setPrevIdx(activeIdx)
    setActiveIdx(idx)
  }

  // Auto-advance every 3.5s while visible — restart on manual change
  useEffect(() => {
    if (!isVisible) return
    if (timerRef.current) clearInterval(timerRef.current)
    timerRef.current = setInterval(() => {
      setActiveIdx(prev => {
        const next = (prev + 1) % steps.length
        setPrevIdx(prev)
        return next
      })
    }, 3500)
    return () => { if (timerRef.current) clearInterval(timerRef.current) }
  }, [isVisible, activeIdx])

  // Intercept wheel scroll while section is visible
  useEffect(() => {
    const el = containerRef.current
    if (!el) return

    const onWheel = (e: WheelEvent) => {
      if (!isVisible) return

      // Allow normal scroll when at boundaries (first/last card)
      if (e.deltaY > 0 && atEnd) return      // last card → let page scroll down
      if (e.deltaY < 0 && atStart) return    // first card → let page scroll up

      // Otherwise capture the scroll
      e.preventDefault()
      if (wheelThrottle.current) return
      wheelThrottle.current = true
      setTimeout(() => { wheelThrottle.current = false }, 700)

      if (e.deltaY > 0) {
        goTo(activeIdx + 1)
      } else {
        goTo(activeIdx - 1)
      }
    }

    el.addEventListener('wheel', onWheel, { passive: false })
    return () => el.removeEventListener('wheel', onWheel)
  }, [isVisible, activeIdx, atStart, atEnd])

  return (
    <section ref={containerRef} className="relative py-24 px-6 md:px-16 min-h-[70vh] flex items-center justify-center">
      {/* Dots indicator */}
      <div className="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-3 z-20">
        {steps.map((_, i) => (
          <button
            key={i}
            onClick={() => { setPrevIdx(activeIdx); setActiveIdx(i) }}
            className="transition-all duration-300"
          >
            <div className={`rounded-full transition-all duration-500 ${i === activeIdx
              ? 'w-8 h-2 bg-primary shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]'
              : 'w-2 h-2 bg-white/20 hover:bg-white/40'
              }`} />
          </button>
        ))}
      </div>

      {/* Cards stack */}
      <div className="relative w-full max-w-5xl h-[60vh]" style={{ perspective: '1200px' }}>
        <AnimatePresence mode="wait">
          <motion.div
            key={activeIdx}
            initial={{ opacity: 0, scale: 0.96, y: 24, rotateX: 3 }}
            animate={{ opacity: 1, scale: 1, y: 0, rotateX: 0 }}
            exit={{ opacity: 0, scale: 0.94, y: -16, rotateX: -3, filter: 'blur(6px)' }}
            transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
            className="absolute inset-0 w-full h-full"
            style={{ transformOrigin: 'center center' }}
          >
            <div className="w-full h-full bg-secondary/95 backdrop-blur-3xl rounded-[3rem] border border-white/10 p-10 md:p-16 flex flex-col md:flex-row gap-10 items-center shadow-[0_0_60px_rgba(0,0,0,0.6)] relative overflow-hidden">
              {/* Active glow */}
              <div className="absolute inset-0 rounded-[3rem] pointer-events-none" style={{ boxShadow: 'inset 0 0 80px rgba(var(--primary-rgb),0.05)' }} />

              <div className="w-full md:w-1/2 space-y-6">
                <motion.span
                  key={`label-${activeIdx}`}
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.15, duration: 0.4 }}
                  className="font-mono text-[10px] text-primary/60 tracking-[0.5em] uppercase block"
                >
                  Этап 0{activeIdx + 1}
                </motion.span>
                <motion.h3
                  key={`title-${activeIdx}`}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.2, duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                  className="text-4xl md:text-6xl font-sans font-black uppercase tracking-tighter leading-none"
                >
                  {steps[activeIdx].title}
                </motion.h3>
                <motion.p
                  key={`desc-${activeIdx}`}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.3, duration: 0.5 }}
                  className="text-foreground/40 text-sm font-light leading-relaxed max-w-sm"
                >
                  {steps[activeIdx].desc}
                </motion.p>
              </div>

              <div className="w-full md:w-1/2 flex items-center justify-center">
                <motion.div
                  key={`icon-${activeIdx}`}
                  initial={{ opacity: 0, scale: 0.85, rotate: -8 }}
                  animate={{ opacity: 1, scale: 1, rotate: 0 }}
                  transition={{ delay: 0.2, duration: 0.7, ease: [0.16, 1, 0.3, 1] }}
                  className="w-40 h-40 md:w-48 md:h-48 border border-white/10 rounded-full flex items-center justify-center relative"
                >
                  <div className="w-32 h-32 md:w-40 md:h-40 border border-primary/20 rounded-full animate-spin [animation-duration:25s]" />
                  <div className="absolute text-primary">
                    {steps[activeIdx].icon}
                  </div>
                </motion.div>
              </div>
            </div>
          </motion.div>
        </AnimatePresence>
      </div>
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
