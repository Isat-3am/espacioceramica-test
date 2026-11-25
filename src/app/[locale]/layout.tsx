import { NextIntlClientProvider } from "next-intl";
import { notFound } from "next/navigation";
import { routing } from "@/i18n/routing";
import { ReactNode } from "react";
import localFont from "next/font/local";
import { Metadata } from "next";
import Header from "./header";
import "./globals.css";
import { CartProvider } from "@/context/CartContext";
import CartDrawer from "@/components/CartDrawer";
// import { PayPalScriptProvider } from "@paypal/react-paypal-js";

const initialOptions = {
  clientId: process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID!,
  currency: "USD",
  intent: "capture",
};

const satoshiFont = localFont({
  src: [
    {
      path: "../../../public/fonts/Satoshi-Variable.ttf",
      style: "normal",
      weight: "100 900",
    },
  ],
  variable: "--satoshi-variable",
  display: "swap",
});

const lastikFont = localFont({
  src: [
    {
      path: "../../../public/fonts/Lastik-Regular.woff2",
      weight: "400",
      style: "normal",
    },
    {
      path: "../../../public/fonts/Lastik-Regular.woff",
      weight: "400",
      style: "normal",
    },
  ],
  variable: "--lastik-regular",
  display: "swap",
});

type Props = {
  children: ReactNode;
  params: Promise<{ locale: string }>; // 👈 params es una promesa
};

export const metadata: Metadata = {
  title: "Espacio Cerámica",
  description:
    "Espacio cerámica es nuestro taller, un proyecto de búsqueda e investigación que surgió en el 2008, donde reunimos nuestras obsesiones en torno al oficio. Desde el comienzo construimos, a través de la experiencia, nuestra profesión, ampliando y profundizando nuestros conocimientos con cada nueva horneada.",
};

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export default async function LocaleLayout({ children, params }: Props) {
  const { locale } = await params;
  let messages;
  try {
    messages = (await import(`@/messages/${locale}.json`)).default;
  } catch (error) {
    notFound();
  }

  return (
    <html lang={locale}>
      <body
        className={`${satoshiFont.variable} ${lastikFont.variable} antialiased `}
        style={{ backgroundColor: "#f6f6f7" }}
      >
        <NextIntlClientProvider locale={locale} messages={messages}>
          <CartProvider>
            <Header />
            <CartDrawer />
            <main className="transition-all px-4"> {children} </main>
          </CartProvider>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
