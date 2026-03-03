import type { Metadata } from "next";
import { Inter, JetBrains_Mono, Instrument_Serif } from "next/font/google";
import "./globals.css";
import { ThemeProvider } from "@/components/theme/theme-provider";
import NextAuthProvider from "@/components/providers/session-provider";
import { ToastProvider } from "@/components/providers/toast-provider";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin", "cyrillic"],
});

const jetbrainsMono = JetBrains_Mono({
  variable: "--font-jetbrains-mono",
  subsets: ["latin"],
});

const instrumentSerif = Instrument_Serif({
  variable: "--font-instrument-serif",
  subsets: ["latin"],
  weight: "400",
  style: "italic",
});

export const metadata: Metadata = {
  title: "Museum Gallery — Digital Heritage",
  description: "A world-class digital curation of human heritage powered by biological data and immersive technology.",
  keywords: ["museum", "history", "digital art", "immersive", "heritage"],
};

export const viewport = {
  themeColor: "#0a0b0d",
};

import { Navbar } from "@/components/layout/navbar";
import { GlobalBackground } from "@/components/layout/global-background";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru" suppressHydrationWarning>
      <body
        className={`${inter.variable} ${jetbrainsMono.variable} ${instrumentSerif.variable} font-sans antialiased bg-background text-foreground`}
      >
        <NextAuthProvider>
          <ThemeProvider>
            <GlobalBackground />
            <Navbar />
            <ToastProvider />
            {children}
          </ThemeProvider>
        </NextAuthProvider>
      </body>
    </html>
  );
}

