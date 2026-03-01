'use client'

import { useState, useEffect, useRef } from 'react'
import { useSession } from 'next-auth/react'
import { Footer } from '@/components/layout/footer'
import { motion, AnimatePresence } from 'framer-motion'
import { useTheme } from '@/components/theme/theme-provider'
import { MessageSquare, Send, Reply, Trash2, Loader2, MessageCircle, Edit2, Camera, X, Check, ThumbsUp, ThumbsDown, Star } from 'lucide-react'

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

export default function CommentsPage() {
  const { theme } = useTheme()
  const { data: session } = useSession()
  const [comments, setComments] = useState<Comment[]>([])
  const [loading, setLoading] = useState(true)
  const [newComment, setNewComment] = useState('')
  const [newImage, setNewImage] = useState<File | null>(null)
  const [newImagePreview, setNewImagePreview] = useState<string | null>(null)
  const [replyTo, setReplyTo] = useState<number | null>(null)
  const [replyText, setReplyText] = useState('')
  const [editingId, setEditingId] = useState<number | null>(null)
  const [editText, setEditText] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [reactions, setReactions] = useState<Record<number, { likes: number; dislikes: number; userReaction: string | null }>>({})

  const userId = (session?.user as Record<string, unknown>)?.id as string | undefined

  useEffect(() => {
    loadComments()
  }, [])

  const loadComments = async () => {
    try {
      const res = await fetch('/api/comments')
      const data = await res.json()
      setComments(data)
      // Extract all comment IDs and fetch their reactions
      const ids = data.map((c: Comment) => c.id)
      data.forEach((c: Comment) => c.replies?.forEach((r: Comment) => ids.push(r.id)))
      if (ids.length > 0) {
        loadReactions(ids)
      }
    } catch {
      console.error('Failed to load comments')
    } finally {
      setLoading(false)
    }
  }

  const loadReactions = async (ids: number[]) => {
    try {
      const url = new URL(window.location.origin + '/api/reactions')
      url.searchParams.set('ids', ids.join(','))
      if (userId) url.searchParams.set('userId', userId)
      const res = await fetch(url.toString())
      const data = await res.json()
      setReactions(data)
    } catch {
      console.error('Failed to load reactions')
    }
  }

  const handleReaction = async (commentId: number, type: 'like' | 'dislike') => {
    if (!userId) return

    // Optimistic update
    setReactions(prev => {
      const current = prev[commentId] || { likes: 0, dislikes: 0, userReaction: null }
      const newReact = { ...current }

      if (current.userReaction === type) {
        // Toggle off
        newReact.userReaction = null
        if (type === 'like') newReact.likes--
        else newReact.dislikes--
      } else {
        // Change or new
        if (current.userReaction === 'like') newReact.likes--
        if (current.userReaction === 'dislike') newReact.dislikes--

        newReact.userReaction = type
        if (type === 'like') newReact.likes++
        else newReact.dislikes++
      }
      return { ...prev, [commentId]: newReact }
    })

    try {
      await fetch('/api/reactions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ commentId, userId, type }),
      })
    } catch {
      console.error('Failed to set reaction')
      // Revert could be here but optimistic UI usually fine for likes
    }
  }

  const uploadImage = async (file: File): Promise<string | null> => {
    const formData = new FormData()
    formData.append('file', file)
    try {
      const res = await fetch('/api/upload', { method: 'POST', body: formData })
      const data = await res.json()
      return data.path || null
    } catch {
      return null
    }
  }

  const handleImageSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        alert('Максимальный размер: 5 МБ')
        return
      }
      setNewImage(file)
      setNewImagePreview(URL.createObjectURL(file))
    }
  }

  const clearImage = () => {
    setNewImage(null)
    setNewImagePreview(null)
    if (fileInputRef.current) fileInputRef.current.value = ''
  }

  const submitComment = async (content: string, parentId?: number) => {
    if (!content.trim() || !userId) return
    setSubmitting(true)

    try {
      let imagePath: string | null = null
      if (newImage && !parentId) {
        imagePath = await uploadImage(newImage)
      }

      const res = await fetch('/api/comments', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, content: content.trim(), parentId, imagePath }),
      })

      if (res.ok) {
        setNewComment('')
        setReplyTo(null)
        setReplyText('')
        clearImage()
        loadComments()
      }
    } catch {
      console.error('Failed to submit comment')
    } finally {
      setSubmitting(false)
    }
  }

  const editComment = async (id: number) => {
    if (!editText.trim()) return
    setSubmitting(true)
    try {
      await fetch(`/api/comments/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: editText.trim() }),
      })
      setEditingId(null)
      setEditText('')
      loadComments()
    } catch {
      console.error('Failed to edit comment')
    } finally {
      setSubmitting(false)
    }
  }

  const deleteComment = async (id: number) => {
    if (!confirm('Удалить комментарий?')) return
    try {
      await fetch(`/api/comments/${id}`, { method: 'DELETE' })
      loadComments()
    } catch {
      console.error('Failed to delete comment')
    }
  }

  const startEdit = (comment: Comment) => {
    setEditingId(comment.id)
    setEditText(comment.content)
  }

  const formatDate = (str: string) => {
    return new Date(str).toLocaleString('ru-RU', {
      day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
    })
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-transparent">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    )
  }

  return (
    <>
      <main className="min-h-screen bg-transparent pt-40 pb-32">
        <div className="container mx-auto px-4 py-16 max-w-3xl">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center mb-20"
          >
            <div className="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-white/5 border border-white/10 mb-8 transition-luxury hover:bg-white/10">
              <MessageSquare className="w-4 h-4 text-primary" />
              <span className="text-[10px] font-black tracking-[0.4em] uppercase text-white/40">
                ДИАЛОГ С ПРОШЛЫМ
              </span>
            </div>
            <h1 className="text-6xl md:text-8xl font-sans font-black tracking-tighter uppercase text-white mb-6">
              Отзывы <span className="text-primary italic font-serif lowercase tracking-normal">посетителей.</span>
            </h1>
            <p className="text-xl font-light tracking-wide text-white/40 max-w-2xl mx-auto italic">
              «Голоса тех, кто прикоснулся к вечности. Оставьте свой след в нашей цифровой летописи.»
            </p>
          </motion.div>

          {/* New comment form */}
          {session && (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="rounded-[3rem] border border-white/10 p-10 mb-16 bg-secondary/40 backdrop-blur-2xl shadow-3xl overflow-hidden relative"
            >
              <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/50 to-transparent" />
              <h3 className="text-xs font-black tracking-[0.3em] uppercase text-white/40 mb-8 flex items-center gap-3">
                <Star className="w-4 h-4 text-primary" /> НОВАЯ ЗАПИСЬ
              </h3>
              <textarea
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                placeholder="Ваши впечатления от экспозиции..."
                rows={4}
                className="w-full rounded-[2rem] px-8 py-6 border border-white/5 bg-white/5 text-white placeholder-white/20 focus:outline-none focus:border-primary/50 focus:bg-white/10 transition-luxury resize-none font-light tracking-wide text-lg"
              />

              {/* Image preview */}
              {newImagePreview && (
                <div className="mt-3 relative inline-block">
                  <img src={newImagePreview} alt="Preview" className="max-h-32 rounded-xl" />
                  <button
                    onClick={clearImage}
                    className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
              )}

              <div className="flex items-center justify-between mt-3">
                <div>
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    onChange={handleImageSelect}
                    className="hidden"
                  />
                  <button
                    onClick={() => fileInputRef.current?.click()}
                    className="flex items-center gap-3 px-6 py-3 rounded-full text-[10px] font-black tracking-[0.2em] uppercase text-foreground/40 hover:text-white hover:bg-white/5 transition-luxury bg-transparent border border-white/10"
                  >
                    <Camera className="w-4 h-4 text-primary" />
                    ПРИКРЕПИТЬ ФОТО
                  </button>
                </div>
                <button
                  onClick={() => submitComment(newComment)}
                  disabled={submitting || !newComment.trim()}
                  className="px-10 py-4 rounded-full bg-primary text-white font-black uppercase tracking-widest text-[10px] disabled:opacity-20 hover:bg-white hover:text-black transition-luxury shadow-xl shadow-primary/20 flex items-center gap-3 magnetic-btn"
                >
                  {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <>ОТПРАВИТЬ <Send className="w-4 h-4" /></>}
                </button>
              </div>
            </motion.div>
          )}

          {/* Comments list */}
          <div className="space-y-4">
            {comments.length === 0 ? (
              <div className="text-center py-24 rounded-[3rem] border border-white/5 bg-secondary/40 backdrop-blur-xl shadow-2xl">
                <MessageCircle className="w-16 h-16 mx-auto mb-6 text-white/10" />
                <p className="text-white/40 font-black tracking-[0.3em] uppercase text-xs">АРХИВ ПУСТ</p>
              </div>
            ) : (
              comments.map((comment, i) => (
                <motion.div
                  key={comment.id}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.05 }}
                  className="rounded-[2.5rem] border border-white/5 p-8 bg-secondary/40 backdrop-blur-xl shadow-2xl transition-luxury hover:bg-secondary/50 group"
                >
                  <div className="flex items-start gap-3">
                    <img
                      src={comment.user.avatar || '/images/avatars/default_avatar.png'}
                      alt={comment.user.login}
                      className="w-14 h-14 rounded-2xl object-cover border border-white/10 flex-shrink-0 group-hover:border-primary/50 transition-luxury"
                    />
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-4 mb-3">
                        <span className="text-lg font-bold tracking-tight text-white uppercase group-hover:text-primary transition-luxury">
                          {comment.user.login}
                        </span>
                        <span className="text-[10px] font-black tracking-widest text-white/20 uppercase">
                          {formatDate(comment.createdAt)}
                        </span>
                      </div>

                      {/* Editing mode */}
                      {editingId === comment.id ? (
                        <div className="mt-2">
                          <textarea
                            value={editText}
                            onChange={(e) => setEditText(e.target.value)}
                            rows={3}
                            className={`w-full rounded-xl px-4 py-3 border resize-none focus:outline-none focus:ring-2 focus:ring-violet-500/30 ${theme === 'dark'
                              ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white'
                              : 'bg-gray-50 border-black/5'
                              }`}
                          />
                          <div className="flex gap-2 mt-2">
                            <button
                              onClick={() => editComment(comment.id)}
                              disabled={submitting || !editText.trim()}
                              className="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium disabled:opacity-50 flex items-center gap-1"
                            >
                              <Check className="w-4 h-4" /> Сохранить
                            </button>
                            <button
                              onClick={() => { setEditingId(null); setEditText('') }}
                              className={`px-4 py-2 rounded-xl border text-sm ${theme === 'dark' ? 'border-white/20 text-gray-400' : 'border-black/5 text-gray-500'
                                }`}
                            >
                              Отмена
                            </button>
                          </div>
                        </div>
                      ) : (
                        <p className="text-lg font-light tracking-wide text-white/60 leading-relaxed italic border-l-2 border-primary/20 pl-8 transition-luxury group-hover:text-white/80">
                          {comment.content}
                        </p>
                      )}

                      {/* Attached image */}
                      {comment.imagePath && editingId !== comment.id && (
                        <img
                          src={comment.imagePath}
                          alt="Фото"
                          className="mt-3 max-w-xs max-h-64 rounded-xl cursor-pointer hover:opacity-90 transition-opacity"
                          onClick={() => window.open(comment.imagePath!, '_blank')}
                        />
                      )}

                      {/* Actions */}
                      {editingId !== comment.id && (
                        <div className="flex items-center gap-4 mt-3">
                          <div className="flex items-center gap-2 mr-2">
                            <button
                              onClick={() => handleReaction(comment.id, 'like')}
                              className={`flex items-center gap-1 text-xs transition-colors ${reactions[comment.id]?.userReaction === 'like'
                                ? 'text-green-500'
                                : theme === 'dark' ? 'text-gray-500 hover:text-green-400' : 'text-gray-400 hover:text-green-500'
                                }`}
                            >
                              <ThumbsUp className={`w-4 h-4 ${reactions[comment.id]?.userReaction === 'like' ? 'fill-current' : ''}`} />
                              <span>{reactions[comment.id]?.likes || 0}</span>
                            </button>
                            <button
                              onClick={() => handleReaction(comment.id, 'dislike')}
                              className={`flex items-center gap-1 text-xs transition-colors ${reactions[comment.id]?.userReaction === 'dislike'
                                ? 'text-red-500'
                                : theme === 'dark' ? 'text-gray-500 hover:text-red-400' : 'text-gray-400 hover:text-red-500'
                                }`}
                            >
                              <ThumbsDown className={`w-4 h-4 ${reactions[comment.id]?.userReaction === 'dislike' ? 'fill-current' : ''}`} />
                            </button>
                          </div>
                          {session && (
                            <button
                              onClick={() => setReplyTo(replyTo === comment.id ? null : comment.id)}
                              className={`text-sm flex items-center gap-1 transition-colors ${theme === 'dark' ? 'text-gray-400 hover:text-violet-400' : 'text-gray-500 hover:text-violet-600'
                                }`}
                            >
                              <Reply className="w-4 h-4" /> Ответить
                            </button>
                          )}
                          {userId && Number(userId) === comment.user.id && (
                            <>
                              <button
                                onClick={() => startEdit(comment)}
                                className={`text-sm flex items-center gap-1 transition-colors ${theme === 'dark' ? 'text-gray-400 hover:text-blue-400' : 'text-gray-500 hover:text-fuchsia-600'
                                  }`}
                              >
                                <Edit2 className="w-4 h-4" /> Редактировать
                              </button>
                              <button
                                onClick={() => deleteComment(comment.id)}
                                className="text-sm flex items-center gap-1 text-red-400/60 hover:text-red-400 transition-colors"
                              >
                                <Trash2 className="w-4 h-4" /> Удалить
                              </button>
                            </>
                          )}
                        </div>
                      )}

                      {/* Reply form */}
                      <AnimatePresence>
                        {replyTo === comment.id && (
                          <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            exit={{ opacity: 0, height: 0 }}
                            className="mt-3"
                          >
                            <div className="flex gap-2">
                              <input
                                type="text"
                                value={replyText}
                                onChange={(e) => setReplyText(e.target.value)}
                                placeholder="Ваш ответ..."
                                className={`flex-1 rounded-xl px-3 py-2 text-sm border focus:outline-none focus:ring-2 focus:ring-violet-500/20 ${theme === 'dark'
                                  ? 'bg-white/5 backdrop-blur-xl border-white/20 text-white placeholder-white/30'
                                  : 'bg-gray-50 border-black/5 text-gray-900'
                                  }`}
                                onKeyDown={(e) => e.key === 'Enter' && submitComment(replyText, comment.id)}
                              />
                              <button
                                onClick={() => submitComment(replyText, comment.id)}
                                disabled={submitting || !replyText.trim()}
                                className="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm disabled:opacity-50"
                              >
                                <Send className="w-4 h-4" />
                              </button>
                            </div>
                          </motion.div>
                        )}
                      </AnimatePresence>

                      {/* Replies */}
                      {comment.replies?.length > 0 && (
                        <div className={`mt-4 space-y-3 pl-4 border-l-2 ${theme === 'dark' ? 'border-violet-500/20' : 'border-black/5'
                          }`}>
                          {comment.replies.map((reply) => (
                            <div key={reply.id} className="flex items-start gap-3">
                              <img
                                src={reply.user.avatar || '/images/avatars/default_avatar.png'}
                                alt={reply.user.login}
                                className="w-8 h-8 rounded-full object-cover border border-violet-500/20 flex-shrink-0"
                              />
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-0.5">
                                  <span className={`font-medium text-sm ${theme === 'dark' ? 'text-white' : 'text-gray-900'}`}>
                                    {reply.user.login}
                                  </span>
                                  <span className={`text-xs ${theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}`}>
                                    {formatDate(reply.createdAt)}
                                  </span>
                                </div>
                                <p className={`text-sm ${theme === 'dark' ? 'text-gray-300' : 'text-gray-600'}`}>
                                  {reply.content}
                                </p>
                                <div className="flex items-center gap-4 mt-1">
                                  <div className="flex items-center gap-2">
                                    <button
                                      onClick={() => handleReaction(reply.id, 'like')}
                                      className={`flex items-center gap-1 text-xs transition-colors ${reactions[reply.id]?.userReaction === 'like'
                                        ? 'text-green-500'
                                        : theme === 'dark' ? 'text-gray-500 hover:text-green-400' : 'text-gray-400 hover:text-green-500'
                                        }`}
                                    >
                                      <ThumbsUp className={`w-3 h-3 ${reactions[reply.id]?.userReaction === 'like' ? 'fill-current' : ''}`} />
                                      <span>{reactions[reply.id]?.likes || 0}</span>
                                    </button>
                                    <button
                                      onClick={() => handleReaction(reply.id, 'dislike')}
                                      className={`flex items-center gap-1 text-xs transition-colors ${reactions[reply.id]?.userReaction === 'dislike'
                                        ? 'text-red-500'
                                        : theme === 'dark' ? 'text-gray-500 hover:text-red-400' : 'text-gray-400 hover:text-red-500'
                                        }`}
                                    >
                                      <ThumbsDown className={`w-3 h-3 ${reactions[reply.id]?.userReaction === 'dislike' ? 'fill-current' : ''}`} />
                                    </button>
                                  </div>
                                  {userId && Number(userId) === reply.user.id && (
                                    <button
                                      onClick={() => deleteComment(reply.id)}
                                      className="text-xs text-red-400/60 hover:text-red-400 flex items-center gap-1 transition-colors"
                                    >
                                      <Trash2 className="w-3 h-3" /> Удалить
                                    </button>
                                  )}
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  </div>
                </motion.div>
              ))
            )}
          </div>
        </div>
      </main>
      <Footer />
    </>
  )
}
