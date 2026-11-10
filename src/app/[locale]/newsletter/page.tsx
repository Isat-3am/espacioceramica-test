"use client";
import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import Footer from "@/components/footer";
import Loader from "@/components/loading";
import NewsletterPosts from "@/components/NewsletterPosts";
import ScrollHint from "@/components/ScrollHint";

// Valor por defecto si el backend todavia no tiene datos de la seccion.
const DEFAULT_URL = "https://espacioceramica.substack.com";

interface data {
  title: string;
  text: string;
  url: string;
}

// Direccion de la publicacion de Substack a partir del link cargado en el panel
// (la publicacion https://algo.substack.com, o el perfil https://substack.com/@algo).
// Devuelve null si no se puede incrustar: ahi solo se ofrece el enlace.
const publicationBase = (url: string) => {
  try {
    const u = new URL(url);
    if (u.protocol !== "https:") return null;
    if (u.hostname === "substack.com") {
      const m = u.pathname.match(/^\/@([A-Za-z0-9_-]+)/);
      return m ? `https://${m[1]}.substack.com` : null;
    }
    if (u.hostname.endsWith(".substack.com") && u.hostname.length > 14) {
      return u.origin;
    }
    return null;
  } catch {
    return null;
  }
};

// Boletin de Substack: el formulario oficial de suscripcion se muestra incrustado.
// Titulo, descripcion y link se editan desde el panel (seccion Newsletter).
const page = () => {
  const locale = useLocale();
  const t = useTranslations("Newsletter");
  const [data, setData] = useState<data | null>(null);
  const [loading, setLoading] = useState(true);
  const apiURL = process.env.NEXT_PUBLIC_API_URL + "/newsletter/" + locale;

  useEffect(() => {
    async function getData() {
      try {
        const res = await fetch(apiURL);
        if (!res.ok) throw new Error("Error al obtener datos");
        setData(await res.json());
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    }

    getData();
  }, []);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [loading]);

  const title = data?.title || t("title");
  const text = data?.text || t("text");
  const url = data?.url || DEFAULT_URL;
  const base = publicationBase(url);

  useEffect(() => {
    document.title = title + " | Espacio Cerámica";
  }, [title]);

  if (loading) return <Loader />;

  return (
    <>
      <section className="flex flex-col">
        {/* En pantalla ancha: texto a la izquierda y formulario a la derecha, centrados a lo alto de la pantalla. */}
        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-x-12 gap-y-8 pt-6 lg:pt-0 lg:min-h-[calc(100svh-4rem)]">
          <div className="flex flex-col gap-y-4 lg:w-1/3">
            <h1 className="text-sm font-[--lastik-regular] uppercase">
              {title}
            </h1>
            <p className="text-sm max-w-md leading-snug whitespace-break-spaces">
              {text}
            </p>
            <a
              href={url}
              target="_blank"
              rel="noopener noreferrer"
              className="text-sm uppercase underline font-[--lastik-regular] hover:opacity-50"
            >
              {t("open")}
            </a>
          </div>

          {base && (
            <iframe
              src={base + "/embed"}
              title={title}
              className="w-full max-w-md lg:ml-auto h-[320px] border-0 bg-white"
            />
          )}
        </div>

        {base && (
          <NewsletterPosts
            pub={new URL(base).hostname.replace(".substack.com", "")}
            archiveUrl={base + "/archive"}
          />
        )}
      </section>
      <ScrollHint />
      <Footer />
    </>
  );
};

export default page;
