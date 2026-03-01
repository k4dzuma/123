'use client'

import { useEffect } from 'react'

export default function GlobalError({
    error,
    reset,
}: {
    error: Error & { digest?: string }
    reset: () => void
}) {
    useEffect(() => {
        console.error(error)
    }, [error])

    return (
        <html lang="ru">
            <body className="bg-black/20 backdrop-blur-xl border-t border-white/10 text-white flex items-center justify-center min-h-screen">
                <div className="text-center p-8 border border-red-500/20 rounded-2xl bg-white/5 backdrop-blur-xl backdrop-blur-md">
                    <h2 className="text-3xl font-bold mb-4">Критическая ошибка приложения</h2>
                    <p className="text-gray-400 mb-8 max-w-md">Что-то сломалось на самом базовом уровне. Мы извиняемся за доставленные неудобства.</p>
                    <button
                        onClick={() => reset()}
                        className="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors font-semibold"
                    >
                        Перезагрузить приложение
                    </button>
                </div>
            </body>
        </html>
    )
}
