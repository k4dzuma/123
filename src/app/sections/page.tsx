'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import Image from 'next/image'
import { Footer } from '@/components/layout/footer'
import { motion } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { LayoutGrid, ChevronRight, Loader2 } from 'lucide-react'
import { TextReveal } from '@/components/ui/text-reveal'
import { SectionCardSkeleton } from '@/components/ui/skeleton'

interface Section {
  id: number
  name: string
  image: string
  description: string
  subSections: { id: number; name: string; _count: { items: number } }[]
}

export default function SectionsPage() {
  const { theme } = useTheme()
  const [sections, setSections] = useState<Section[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch('/api/sections')
      .then((r) => r.json())
      .then((data) => setSections(data))
      .catch(console.error)
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <main className="min-h-[100svh] relative pt-40 pb-32 overflow-hidden bg-transparent">
        <div className="container mx-auto px-8 lg:px-16">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {[...Array(8)].map((_, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
              >
                <SectionCardSkeleton />
              </motion.div>
            ))}
          </div>
        </div>
      </main>
    )
  }

  return (
    <>
      <main className="min-h-[100svh] relative pt-40 pb-32 overflow-hidden bg-transparent">
        <div className="container mx-auto px-8 lg:px-16 relative z-10">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center mb-16"
          >
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 mb-6">
              <LayoutGrid className="w-4 h-4 text-primary" />
              <span className="text-[10px] font-bold tracking-[0.3em] uppercase text-primary/60">
                Цифровые Архивы
              </span>
            </div>
            <h1 className="text-5xl md:text-7xl font-sans font-black tracking-tighter uppercase mb-6 text-white drop-shadow-2xl">
              <TextReveal>Наши</TextReveal> <TextReveal className="font-serif italic text-primary lowercase tracking-normal" delay={0.2}>Коллекции.</TextReveal>
            </h1>
            <p className="text-lg max-w-2xl mx-auto font-light tracking-wide text-foreground/50">
              Глубокое погружение в десятилетия истории колледжа через интерактивные имерсивные разделы.
            </p>
          </motion.div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {sections.map((section, i) => (
              <motion.div
                key={section.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
              >
                <Link href={`/sections/${section.id}`} className="block group">
                  <div className="rounded-[2.5rem] overflow-hidden border border-white/5 bg-secondary/40 backdrop-blur-xl transition-all duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)] hover:border-primary/30 hover:bg-secondary/60 shadow-2xl group-hover:-translate-y-2">
                    <div className="relative h-64 overflow-hidden">
                      <Image
                        src={section.image}
                        alt={section.name}
                        fill
                        className="object-cover transition-transform duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)] group-hover:scale-105"
                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                      />
                      <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                      <div className="absolute bottom-5 left-5 right-5">
                        <span className="text-white text-xs font-bold tracking-widest uppercase bg-white/10 backdrop-blur-md px-3 py-1.5 border border-white/20 rounded-full">
                          {section.subSections.length} подраздел{section.subSections.length !== 1 ? (section.subSections.length < 5 ? 'а' : 'ов') : ''}
                        </span>
                      </div>
                    </div>
                    <div className="p-8 space-y-4">
                      <h3 className="text-2xl font-bold tracking-tight uppercase text-white group-hover:text-primary transition-colors duration-500">
                        {section.name}
                      </h3>
                      <p className="text-sm font-light tracking-wide line-clamp-3 text-foreground/40 leading-relaxed">
                        {section.description}
                      </p>
                      <div className="pt-4 flex items-center gap-2 text-[10px] font-black tracking-[0.3em] uppercase text-primary">
                        Исследовать <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                      </div>
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </main>
      <Footer />
    </>
  )
}
