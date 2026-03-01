export function Skeleton({ className = '', ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={`animate-pulse rounded-xl bg-white/10 ${className}`}
            {...props}
        />
    )
}

export function CardSkeleton() {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl p-6">
            <Skeleton className="h-6 w-3/4 mb-4" />
            <Skeleton className="h-4 w-full mb-2" />
            <Skeleton className="h-4 w-2/3 mb-4" />
            <Skeleton className="h-10 w-full" />
        </div>
    )
}

export function TableRowSkeleton() {
    return (
        <div className="flex items-center gap-4 p-4">
            <Skeleton className="w-10 h-10 rounded-full" />
            <div className="flex-1">
                <Skeleton className="h-4 w-1/3 mb-2" />
                <Skeleton className="h-3 w-1/2" />
            </div>
            <Skeleton className="h-6 w-16" />
        </div>
    )
}

export function ProfileSkeleton() {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl p-8">
            <div className="flex items-center gap-6 mb-8">
                <Skeleton className="w-24 h-24 rounded-2xl" />
                <div className="flex-1">
                    <Skeleton className="h-8 w-48 mb-2" />
                    <Skeleton className="h-4 w-32 mb-2" />
                    <Skeleton className="h-6 w-24" />
                </div>
            </div>
            <div className="grid grid-cols-3 gap-4">
                <Skeleton className="h-20 rounded-xl" />
                <Skeleton className="h-20 rounded-xl" />
                <Skeleton className="h-20 rounded-xl" />
            </div>
        </div>
    )
}
