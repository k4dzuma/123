'use client'

import { createContext, useContext, ReactNode } from 'react'

interface ImagesContextType {
  hero: string
  sections: string[]
  exhibits: string[]
  avatars: string[]
}

const ImagesContext = createContext<ImagesContextType | undefined>(undefined)

const DEFAULT_IMAGES: ImagesContextType = {
  hero: '/images/museum-bg.jpg',
  sections: [
    '/images/01.png',
    '/images/02.png',
    '/images/03.png',
    '/images/04.png',
    '/images/05.png',
    '/images/06.png',
    '/images/07.png',
    '/images/08.png',
    '/images/09.png',
  ],
  exhibits: [
    '/images/Экспонаты/00_00_01.png',
    '/images/Экспонаты/00_00_02.png',
    '/images/Экспонаты/00_00_03.png',
    '/images/Экспонаты/00_00_04.png',
    '/images/Экспонаты/00_00_05.png',
    '/images/Экспонаты/00_00_06.png',
    '/images/Экспонаты/00_00_07.png',
    '/images/Экспонаты/00_00_08.png',
  ],
  avatars: [
    '/images/avatars/default_avatar.png',
    '/images/avatars/avatar_8_1744554711.png',
    '/images/avatars/avatar_9_1744541950.png',
    '/images/avatars/avatar_11_1744571320.jpg',
    '/images/avatars/avatar_12_1744640721.jpg',
    '/images/avatars/avatar_13_1744648329.jpg',
    '/images/avatars/avatar_15_1744666884.jpg',
  ],
}

export function ImageProvider({ children }: { children: ReactNode }) {
  return (
    <ImagesContext.Provider value={DEFAULT_IMAGES}>
      {children}
    </ImagesContext.Provider>
  )
}

export function useImages() {
  const context = useContext(ImagesContext)
  if (context === undefined) {
    throw new Error('useImages must be used within an ImageProvider')
  }
  return context
}

export function getSectionImages() {
  return DEFAULT_IMAGES.sections
}

export function getExhibitImages() {
  return DEFAULT_IMAGES.exhibits
}

export function getAvatarImages() {
  return DEFAULT_IMAGES.avatars
}

export function getHeroImage() {
  return DEFAULT_IMAGES.hero
}
