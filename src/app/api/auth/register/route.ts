import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { hashSync } from 'bcryptjs'

export async function POST(request: Request) {
    try {
        const { login, email, password } = await request.json()

        if (!login || !email || !password) {
            return NextResponse.json({ error: 'Все поля обязательны' }, { status: 400 })
        }

        if (login.length < 3) {
            return NextResponse.json({ error: 'Логин должен быть не менее 3 символов' }, { status: 400 })
        }

        if (password.length < 6) {
            return NextResponse.json({ error: 'Пароль должен быть не менее 6 символов' }, { status: 400 })
        }

        const existingUser = await prisma.user.findFirst({
            where: { OR: [{ login }, { email }] },
        })

        if (existingUser) {
            return NextResponse.json(
                { error: existingUser.login === login ? 'Логин уже занят' : 'Email уже используется' },
                { status: 400 }
            )
        }

        const hashedPassword = hashSync(password, 10)

        const user = await prisma.user.create({
            data: { login, email, password: hashedPassword },
        })

        return NextResponse.json({
            success: true,
            user: { id: user.id, login: user.login, email: user.email },
        })
    } catch {
        return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 })
    }
}
