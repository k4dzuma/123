'use client'

import { useRouter } from 'next/navigation'
import { useEffect } from 'react'

export default function LeaderboardRedirect() {
  const router = useRouter()
  useEffect(() => {
    router.replace('/quests')
  }, [router])
  return null
}
