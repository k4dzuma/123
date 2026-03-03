'use client'

import { motion, HTMLMotionProps } from 'framer-motion'
import { cn } from '@/lib/utils'

export function Skeleton({ className, ...props }: HTMLMotionProps<"div">) {
    return (
        <motion.div
            initial={{ opacity: 0.3 }}
            animate={{ opacity: [0.3, 0.5, 0.3] }}
            transition={{
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut"
            }}
            className={cn(
                "relative overflow-hidden rounded-3xl bg-white/5 backdrop-blur-md border border-white/5",
                "before:absolute before:inset-0 before:-translate-x-full before:animate-[shimmer_2s_infinite] before:bg-gradient-to-r before:from-transparent before:via-white/5 before:to-transparent",
                className
            )}
            {...props}
        />
    )
}

export function QuestCardSkeleton() {
    return (
        <div className="rounded-[2.5rem] border border-white/10 p-8 md:p-10 h-full flex flex-col bg-secondary/40 backdrop-blur-xl shadow-2xl">
            <div className="flex items-start justify-between mb-8">
                <Skeleton className="w-16 h-16 rounded-2xl" />
                <Skeleton className="w-24 h-6 rounded-full" />
            </div>
            <Skeleton className="w-3/4 h-8 mb-4" />
            <Skeleton className="w-full h-4 mb-2" />
            <Skeleton className="w-5/6 h-4 mb-8" />
            <div className="space-y-3 mb-5">
                <div className="flex gap-4">
                    <Skeleton className="w-20 h-4" />
                    <Skeleton className="w-20 h-4" />
                </div>
                <div className="flex gap-4">
                    <Skeleton className="w-24 h-4" />
                    <Skeleton className="w-24 h-4" />
                </div>
            </div>
            <Skeleton className="w-full h-14 mt-8 rounded-full" />
        </div>
    )
}

export function SectionCardSkeleton() {
    return (
        <div className="rounded-[2.5rem] overflow-hidden border border-white/5 bg-secondary/40 backdrop-blur-xl shadow-2xl h-full">
            <Skeleton className="h-64 w-full rounded-none" />
            <div className="p-8 space-y-4">
                <Skeleton className="w-2/3 h-8" />
                <Skeleton className="w-full h-4" />
                <Skeleton className="w-full h-4" />
                <Skeleton className="w-1/2 h-4" />
                <Skeleton className="w-24 h-4 mt-4" />
            </div>
        </div>
    )
}

export function ProfileSkeleton() {
    return (
        <div className="space-y-8">
            {/* Header Card Skeleton */}
            <div className="rounded-[2.5rem] border border-white/10 p-10 bg-secondary/40 backdrop-blur-xl shadow-2xl">
                <div className="flex items-center gap-6 mb-6">
                    <Skeleton className="w-24 h-24 rounded-2xl" />
                    <div className="space-y-3">
                        <Skeleton className="w-48 h-8" />
                        <Skeleton className="w-32 h-4 opacity-50" />
                        <div className="flex gap-2 mt-2">
                            <Skeleton className="w-28 h-6 rounded-full" />
                            <Skeleton className="w-24 h-6 rounded-full opacity-50" />
                        </div>
                    </div>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mt-12">
                    {[...Array(5)].map((_, i) => (
                        <Skeleton key={i} className="h-32 rounded-2xl" />
                    ))}
                </div>
            </div>

            {/* Achievements Skeleton */}
            <div className="rounded-[2.5rem] border border-white/10 p-10 bg-secondary/40 backdrop-blur-xl">
                <Skeleton className="w-56 h-8 mb-8" />
                <div className="grid grid-cols-2 md:grid-cols-3 gap-6">
                    {[...Array(6)].map((_, i) => (
                        <Skeleton key={i} className="h-32 rounded-2xl" />
                    ))}
                </div>
            </div>
        </div>
    )
}
