'use client'

import { useState, useEffect } from 'react'
import { useSession } from 'next-auth/react'
import { useTheme } from '@/components/theme/theme-provider'
import { Loader2, MessageSquare, Trash2, Image as ImageIcon } from 'lucide-react'

interface CommentUser {
    id: number
    login: string
    avatar: string
}

interface Comment {
    id: number
    content: string
    imagePath: string | null
    createdAt: string
    user: CommentUser
    replies: Comment[]
}

export default function AdminCommentsPage() {
    const { theme } = useTheme()
    const { data: session } = useSession()
    const [comments, setComments] = useState<Comment[]>([])
    const [loading, setLoading] = useState(true)

    const adminId = (session?.user as Record<string, unknown>)?.id as string | undefined

    useEffect(() => {
        fetch('/api/admin/comments')
            .then(r => r.json())
            .then(setComments)
            .catch(console.error)
            .finally(() => setLoading(false))
    }, [])

    const deleteComment = async (id: number) => {
        if (!confirm('Удалить комментарий и все ответы?')) return
        try {
            await fetch('/api/admin/comments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', commentId: id, adminId }),
            })
            setComments(prev => prev.filter(c => c.id !== id))
        } catch { }
    }

    if (loading) {
        return <div className="flex items-center justify-center h-64"><Loader2 className="w-8 h-8 animate-spin text-violet-500" /></div>
    }

    return (
        <div>
            <div className="flex items-center justify-between mb-8">
                <h1 className={`text-3xl font-bold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                    Модерация комментариев
                </h1>
                <span className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>
                    Всего: {comments.length}
                </span>
            </div>

            {comments.length === 0 ? (
                <div className={`text-center py-12 rounded-2xl border ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                    }`}>
                    <MessageSquare className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                    <p className={theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}>Нет комментариев</p>
                </div>
            ) : (
                <div className="space-y-4">
                    {comments.map((comment) => (
                        <div key={comment.id} className={`rounded-2xl border p-5 ${theme === 'dark' ? 'bg-white/5 backdrop-blur-xl border-white/10' : 'bg-white/70 backdrop-blur-xl border-black/5 border-black/5'
                            }`}>
                            <div className="flex items-start gap-3">
                                <img src={comment.user.avatar || '/images/avatars/default_avatar.png'} alt="" className="w-10 h-10 rounded-full object-cover" />
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <span className={`font-semibold ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{comment.user.login}</span>
                                            <span className={`text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                                {new Date(comment.createdAt).toLocaleString('ru-RU')}
                                            </span>
                                        </div>
                                        <button onClick={() => deleteComment(comment.id)} className="p-2 rounded-lg hover:bg-red-500/10 text-red-400 transition-colors" title="Удалить">
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                    <p className={`mt-1 ${theme === 'dark' ? 'text-gray-300' : 'text-gray-600'}`}>{comment.content}</p>
                                    {comment.imagePath && (
                                        <div className="mt-2 flex items-center gap-2">
                                            <ImageIcon className="w-4 h-4 text-blue-400" />
                                            <a href={comment.imagePath} target="_blank" className="text-sm text-blue-400 hover:underline">Фото</a>
                                        </div>
                                    )}

                                    {/* Replies */}
                                    {comment.replies?.length > 0 && (
                                        <div className={`mt-3 pl-4 border-l-2 space-y-2 ${theme === 'dark' ? 'border-violet-500/20' : 'border-black/5'
                                            }`}>
                                            {comment.replies.map(reply => (
                                                <div key={reply.id} className="flex items-start gap-2">
                                                    <img src={reply.user.avatar || '/images/avatars/default_avatar.png'} alt="" className="w-7 h-7 rounded-full object-cover" />
                                                    <div className="flex-1">
                                                        <span className={`text-sm font-medium ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>{reply.user.login}</span>
                                                        <p className={`text-sm ${theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}`}>{reply.content}</p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}
