"use client";
import { useEffect } from "react";
import { useTranslations } from "next-intl";
import Footer from "@/components/footer";

// Boletin de Substack: el formulario oficial de suscripcion se muestra incrustado.
const SUBSTACK = "https://espacioceramica.substack.com";

const page = () => {
  const t = useTranslations("Newsletter");

  useEffect(() => {
    document.title = "Newsletter | Espacio Cerámica";
    window.scrollTo(0, 0);
  }, []);

  return (
    <>
      <section className="pt-6 lg:pt-8 flex flex-col gap-y-6">
        <h1 className="text-3xl lg:text-4xl font-[--lastik-regular] uppercase">
          {t("title")}
        </h1>
        <p className="text-sm max-w-md leading-snug">{t("text")}</p>

        <iframe
          src={SUBSTACK + "/embed"}
          title="Newsletter"
          className="w-full max-w-md h-[320px] border-0 bg-white"
          loading="lazy"
        />

        <a
          href={SUBSTACK}
          target="_blank"
          rel="noopener noreferrer"
          className="text-sm uppercase underline font-[--lastik-regular] hover:opacity-50"
        >
          {t("open")}
        </a>
      </section>
      <Footer />
    </>
  );
};

export default page;
