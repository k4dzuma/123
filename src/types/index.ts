export interface User {
  id: string
  login: string
  email: string
  avatar?: string
  totalScore: number
  createdAt: Date
}

export interface Quest {
  id: string
  title: string
  description: string
  durationMinutes: number
  difficultyLevel: 'easy' | 'medium' | 'hard'
  isActive: boolean
  stepCount?: number
  completions?: number
}

export interface QuestStep {
  id: string
  order: number
  title: string
  description: string
  solutionHash: string
  hintText?: string
  stepScore: number
  maxAttempts: number
}

export interface Section {
  id: string
  name: string
  image: string
  url: string
}

export interface Comment {
  id: string
  user: User
  content: string
  imagePath?: string
  replies?: Comment[]
  createdAt: Date
}

export type DifficultyLevel = 'easy' | 'medium' | 'hard'
export type QuestStatus = 'in_progress' | 'completed' | 'abandoned'
