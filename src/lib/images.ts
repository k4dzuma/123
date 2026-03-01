export interface ImagePaths {
  hero: '/images/museum-bg.jpg'
  sections: string[]
  exhibits: string[]
  avatars: string[]
}

export const DEFAULT_AVATAR = '/images/avatars/default.png'

export function getImageUrl(filename: string): string {
  return `/images/${filename}`
}

export function getSectionImageUrl(filename: string): string {
  return `/images/sections/${filename}`
}

export function getExhibitImageUrl(filename: string): string {
  return `/images/exhibits/${filename}`
}

export function getAvatarUrl(filename: string): string {
  return `/images/avatars/${filename}`
}
