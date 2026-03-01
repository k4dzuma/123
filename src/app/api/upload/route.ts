import { NextResponse } from 'next/server'
import { writeFile, mkdir } from 'fs/promises'
import path from 'path'

export async function POST(request: Request) {
    try {
        const formData = await request.formData()
        const file = formData.get('file') as File | null

        if (!file) {
            return NextResponse.json({ error: 'Файл не загружен' }, { status: 400 })
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        if (!allowedTypes.includes(file.type)) {
            return NextResponse.json({ error: 'Допустимы только изображения (JPEG, PNG, GIF, WebP)' }, { status: 400 })
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            return NextResponse.json({ error: 'Максимальный размер файла: 5 МБ' }, { status: 400 })
        }

        const bytes = await file.arrayBuffer()
        const buffer = Buffer.from(bytes)

        // Generate unique filename
        const ext = file.name.split('.').pop() || 'jpg'
        const filename = `${Date.now()}_${Math.random().toString(36).slice(2, 8)}.${ext}`

        // Ensure upload directory exists
        const uploadDir = path.join(process.cwd(), 'public', 'uploads', 'comments')
        await mkdir(uploadDir, { recursive: true })

        // Save file
        const filePath = path.join(uploadDir, filename)
        await writeFile(filePath, buffer)

        return NextResponse.json({
            success: true,
            path: `/uploads/comments/${filename}`,
        })
    } catch {
        return NextResponse.json({ error: 'Ошибка загрузки файла' }, { status: 500 })
    }
}
