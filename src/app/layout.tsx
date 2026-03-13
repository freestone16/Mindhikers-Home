import Navbar from "@/components/navbar";
import { ThemeProvider } from "@/components/theme-provider";
import { cn } from "@/lib/utils";
import type { Metadata } from "next";
import localFont from "next/font/local";
import "./globals.css";

const cabinetGrotesk = localFont({
  src: "../../public/fonts/CabinetGrotesk-Medium.ttf",
  variable: "--font-brand-sans",
  display: "swap",
});

const clashDisplay = localFont({
  src: "../../public/fonts/ClashDisplay-Semibold.ttf",
  variable: "--font-brand-display",
  display: "swap",
});

export const metadata: Metadata = {
  metadataBase: new URL("https://www.mindhikers.com"),
  title: {
    default: "心行者 Mindhikers",
    template: `%s | 心行者 Mindhikers`,
  },
  description:
    "心行者 Mindhikers 是一个双语品牌主页，用来承载内容、方法、产品实验与创作者工具入口。",
  openGraph: {
    title: "心行者 Mindhikers",
    description:
      "心行者 Mindhikers 是一个双语品牌主页，用来承载内容、方法、产品实验与创作者工具入口。",
    url: "https://www.mindhikers.com",
    siteName: "心行者 Mindhikers",
    type: "website",
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },
  twitter: {
    title: "心行者 Mindhikers",
    card: "summary_large_image",
  },
  verification: {
    google: "",
    yandex: "",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="zh-CN" suppressHydrationWarning>
      <body
        className={cn(
          "relative min-h-screen bg-background antialiased",
          cabinetGrotesk.variable,
          clashDisplay.variable
        )}
      >
        <ThemeProvider attribute="class" defaultTheme="light">
          <Navbar />
          <div className="relative z-10 mx-auto max-w-5xl px-5 pb-20 pt-[4.6rem] sm:px-6 sm:pt-[5rem]">
            {children}
          </div>
        </ThemeProvider>
      </body>
    </html>
  );
}
