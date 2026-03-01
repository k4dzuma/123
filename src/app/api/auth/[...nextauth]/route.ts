import NextAuth from 'next-auth'
import CredentialsProvider from 'next-auth/providers/credentials'
import { prisma } from '@/lib/prisma'
import { compareSync } from 'bcryptjs'

const handler = NextAuth({
    providers: [
        CredentialsProvider({
            name: 'credentials',
            credentials: {
                login: { label: 'Login', type: 'text' },
                password: { label: 'Password', type: 'password' },
            },
            async authorize(credentials) {
                if (!credentials?.login || !credentials?.password) return null

                const user = await prisma.user.findUnique({
                    where: { login: credentials.login },
                })

                if (!user) return null

                const isValid = compareSync(credentials.password, user.password)
                if (!isValid) return null

                return {
                    id: String(user.id),
                    name: user.login,
                    email: user.email,
                    image: user.avatar,
                    role: user.role,
                }
            },
        }),
    ],
    callbacks: {
        async jwt({ token, user }) {
            if (user) {
                token.id = user.id
                token.role = (user as unknown as Record<string, unknown>).role as string
            }
            return token
        },
        async session({ session, token }) {
            if (session.user) {
                (session.user as Record<string, unknown>).id = token.id
                    ; (session.user as Record<string, unknown>).role = token.role
            }
            return session
        },
    },
    pages: {
        signIn: '/login',
    },
    session: {
        strategy: 'jwt',
    },
    secret: process.env.NEXTAUTH_SECRET,
})

export { handler as GET, handler as POST }
