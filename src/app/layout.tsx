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
    default: "MindHikers",
    template: `%s | MindHikers`,
  },
  description:
    "MindHikers is a bilingual brand home for thoughtful content, practical workflows, and product experiments.",
  openGraph: {
    title: "MindHikers",
    description:
      "MindHikers is a bilingual brand home for thoughtful content, practical workflows, and product experiments.",
    url: "https://www.mindhikers.com",
    siteName: "MindHikers",
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
    title: "MindHikers",
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
          <div className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute left-[-8%] top-[-4%] h-72 w-72 rounded-full bg-[radial-gradient(circle,rgba(195,151,97,0.24),transparent_68%)] blur-3xl" />
            <div className="absolute right-[-10%] top-[14%] h-80 w-80 rounded-full bg-[radial-gradient(circle,rgba(231,208,179,0.45),transparent_70%)] blur-3xl" />
            <div className="absolute bottom-[-8%] left-[22%] h-72 w-72 rounded-full bg-[radial-gradient(circle,rgba(160,121,82,0.16),transparent_72%)] blur-3xl" />
          </div>
          <div className="relative z-10 mx-auto max-w-6xl px-6 pb-24 pt-24 sm:px-8 sm:pt-28">
            {children}
          </div>
          <Navbar />
        </ThemeProvider>
      </body>
    </html>
  );
}
