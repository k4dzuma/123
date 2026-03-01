'use client'

import { useState, useEffect } from 'react'
import { useParams } from 'next/navigation'
import Image from 'next/image'
import { Footer } from '@/components/layout/footer'
import { motion, AnimatePresence } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { ArrowLeft, ChevronDown, ChevronUp, Image as ImageIcon, Loader2 } from 'lucide-react'
import Link from 'next/link'

interface ContentItem {
    id: number
    title: string
    image: string
    text: string
}

interface SubSection {
    id: number
    name: string
    items: ContentItem[]
}

interface Section {
    id: number
    name: string
    image: string
    description: string
    subSections: SubSection[]
}

export default function SectionDetailPage() {
    const { theme } = useTheme()
    const params = useParams()
    const [section, setSection] = useState<Section | null>(null)
    const [loading, setLoading] = useState(true)
    const [openSub, setOpenSub] = useState<number | null>(null)
    const [lightboxImage, setLightboxImage] = useState<string | null>(null)

    useEffect(() => {
        if (params.id) {
            fetch(`/api/sections/${params.id}`)
                .then((r) => r.json())
                .then((data) => {
                    setSection(data)
                    if (data.subSections?.length > 0) setOpenSub(data.subSections[0].id)
                })
                .catch(console.error)
                .finally(() => setLoading(false))
        }
    }, [params.id])

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-transparent">
                <Loader2 className="w-8 h-8 animate-spin text-primary" />
            </div>
        )
    }

    if (!section) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-transparent">
                <p className="text-foreground/40 font-light tracking-wide italic">Раздел не найден</p>
            </div>
        )
    }

    return (
        <>
            <main className="min-h-screen bg-transparent pt-32 pb-32">
                {/* Hero */}
                <div className="container mx-auto px-8 lg:px-16 mb-12">
                    <div className="relative h-[400px] md:h-[500px] rounded-[3rem] overflow-hidden border border-white/10 shadow-3xl">
                        <Image src={section.image} alt={section.name} fill className="object-cover scale-105" />
                        <div className="absolute inset-0 bg-gradient-to-t from-secondary via-secondary/40 to-transparent" />
                        <div className="absolute bottom-0 left-0 right-0 p-12 md:p-16">
                            <Link href="/sections" className="inline-flex items-center gap-2 text-white/40 hover:text-primary mb-6 transition-luxury text-xs font-bold tracking-[0.3em] uppercase">
                                <ArrowLeft className="w-4 h-4" /> Архивы
                            </Link>
                            <h1 className="text-5xl md:text-7xl font-sans font-black tracking-tighter uppercase text-white mb-4">
                                {section.name}
                            </h1>
                            <p className="text-lg md:text-xl font-light tracking-wide text-white/60 max-w-3xl leading-relaxed">
                                {section.description}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Content */}
                <div className="container mx-auto px-8 lg:px-16 pb-24">
                    <div className="space-y-4">
                        {section.subSections.map((sub) => (
                            <motion.div
                                key={sub.id}
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="rounded-[2.5rem] border border-white/5 overflow-hidden bg-secondary/40 backdrop-blur-xl shadow-2xl transition-all duration-700"
                            >
                                <button
                                    onClick={() => setOpenSub(openSub === sub.id ? null : sub.id)}
                                    className="w-full flex items-center justify-between p-8 text-left transition-luxury hover:bg-white/5"
                                >
                                    <h2 className="text-xl font-bold tracking-tight text-white uppercase group-hover:text-primary">
                                        {sub.name}
                                    </h2>
                                    <div className="flex items-center gap-6">
                                        <span className="text-[10px] font-black tracking-[0.3em] uppercase text-foreground/40">
                                            {sub.items.length} ЭКСПУНАТОВ
                                        </span>
                                        {openSub === sub.id ? (
                                            <ChevronUp className="w-5 h-5 text-primary" />
                                        ) : (
                                            <ChevronDown className="w-5 h-5 text-foreground/20" />
                                        )}
                                    </div>
                                </button>

                                <AnimatePresence>
                                    {openSub === sub.id && (
                                        <motion.div
                                            initial={{ height: 0, opacity: 0 }}
                                            animate={{ height: 'auto', opacity: 1 }}
                                            exit={{ height: 0, opacity: 0 }}
                                            transition={{ duration: 0.3 }}
                                            className="overflow-hidden"
                                        >
                                            <div className={`p-5 pt-0 space-y-6 border-t ${theme === 'dark' ? 'border-white/10' : 'border-gray-100'
                                                }`}>
                                                {sub.items.map((item) => (
                                                    <div key={item.id} className="flex flex-col md:flex-row gap-6 pt-5">
                                                        {item.image && (
                                                            <div
                                                                className="relative w-full md:w-64 h-48 rounded-xl overflow-hidden flex-shrink-0 cursor-pointer group"
                                                                onClick={() => setLightboxImage(item.image)}
                                                            >
                                                                <Image src={item.image} alt={item.title} fill className="object-cover group-hover:scale-105 transition-transform duration-300" />
                                                                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                                                    <ImageIcon className="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                                                </div>
                                                            </div>
                                                        )}
                                                        <div>
                                                            <h3 className={`text-lg font-semibold mb-2 ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                                                {item.title}
                                                            </h3>
                                                            <p className={`leading-relaxed ${theme === 'dark' ? 'text-gray-300' : 'text-gray-600'}`}>
                                                                {item.text}
                                                            </p>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </motion.div>
                                    )}
                                </AnimatePresence>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </main>

            {/* Lightbox */}
            <AnimatePresence>
                {lightboxImage && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4 cursor-pointer"
                        onClick={() => setLightboxImage(null)}
                    >
                        <motion.div
                            initial={{ scale: 0.8 }}
                            animate={{ scale: 1 }}
                            exit={{ scale: 0.8 }}
                            className="relative max-w-4xl max-h-[80vh] w-full h-full"
                        >
                            <Image src={lightboxImage} alt="" fill className="object-contain" />
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <Footer />
        </>
    )
}
