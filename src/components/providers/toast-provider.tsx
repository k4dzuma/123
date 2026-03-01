'use client'

import { Toaster } from 'react-hot-toast'

export function ToastProvider() {
    return (
        <Toaster
            position="top-right"
            toastOptions={{
                duration: 3000,
                style: {
                    background: '#1a1a2e',
                    color: '#fff',
                    border: '1px solid rgba(255,255,255,0.1)',
                    borderRadius: '16px',
                    padding: '12px 20px',
                    fontSize: '14px',
                    boxShadow: '0 20px 40px rgba(0,0,0,0.3)',
                },
                success: {
                    iconTheme: { primary: '#a855f7', secondary: '#fff' },
                },
                error: {
                    iconTheme: { primary: '#ef4444', secondary: '#fff' },
                },
            }}
        />
    )
}
