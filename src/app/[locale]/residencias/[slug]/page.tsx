"use client";
import { useState, useEffect } from "react";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import Loader from "@/components/loading";

interface data {
  id: number;
  slug: string;
  title: string;
  text: string;
  form_url: string | null;
}

// Devuelve el link para incrustar el formulario de Google, o null si no se puede incrustar
// (por ejemplo, los links cortos forms.gle: ahi solo se ofrece abrirlo en otra pestaña).
const embedUrl = (url: string | null) => {
  if (!url) return null;
  try {
    const u = new URL(url);
    if (u.protocol !== "https:" || u.hostname !== "docs.google.com") return null;
    if (!u.pathname.includes("/viewform")) return null;
    u.search = "";
    u.searchParams.set("embedded", "true");
    return u.toString();
  } catch {
    return null;
  }
};

const page = () => {
  const locale = useLocale();
  const t = useTranslations("Residencias");
  const { slug } = useParams();
  const [data, setData] = useState<data | null>(null);
  const [loading, setLoading] = useState(true);
  const apiURL =
    process.env.NEXT_PUBLIC_API_URL + "/residencia/" + slug + "/" + locale;

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
    if (data) document.title = data.title + " | Espacio Cerámica";
  }, [data]);

  // Al entrar desde el botón de la lista, empezar arriba de todo (no en la posicion de la lista).
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [loading]);

  if (loading) return <Loader />;

  const back = (
    <Link
      href="/residencias"
      className="text-sm uppercase font-[--lastik-regular] hover:underline"
    >
      {t("back")}
    </Link>
  );

  if (!data)
    return (
      <section className="pt-6 lg:pt-8 flex flex-col gap-y-8">
        <p className="text-sm">{t("notFound")}</p>
        {back}
      </section>
    );

  const embed = embedUrl(data.form_url);

  return (
    <section className="flex flex-col lg:flex-row lg:items-start gap-x-12 gap-y-6 pt-6 lg:pt-0 pb-12">
      {/* Pantalla ancha: titulo y resena a la izquierda, centrados a lo alto de la pantalla y fijos
          mientras se baja por el formulario. En celular: una cosa debajo de la otra. */}
      <div className="relative lg:w-1/3 lg:shrink-0 lg:sticky lg:top-12 lg:h-[calc(100svh-3rem)] flex flex-col lg:justify-center gap-y-4">
        <div className="lg:absolute lg:top-0 lg:left-0">{back}</div>
        <h1 className="text-sm font-[--lastik-regular] uppercase">
          {data.title}
        </h1>
        {data.text && (
          <p className="text-sm max-w-md leading-snug whitespace-break-spaces">
            {data.text}
          </p>
        )}
        {data.form_url && (
          <a
            href={data.form_url}
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm uppercase underline font-[--lastik-regular] hover:opacity-50"
          >
            {t("openForm")}
          </a>
        )}
      </div>

      {/* Formulario a la derecha, arrancando desde arriba de la pagina. */}
      {embed && (
        <iframe
          src={embed}
          title={data.title}
          className="w-full lg:flex-1 lg:min-w-0 lg:max-w-2xl lg:ml-auto h-[1400px] border-0"
          loading="lazy"
        >
          {t("loadingForm")}
        </iframe>
      )}
    </section>
  );
};

export default page;
