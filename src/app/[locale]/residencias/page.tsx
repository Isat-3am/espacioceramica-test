"use client";
import { useState, useEffect } from "react";
import { useLocale } from "next-intl";
import Voluntariados from "./voluntariados";
import Inscripciones from "./inscripciones";
import Image from "@/components/Image";
import Gallery from "@/components/gallery";
import Modal from "@/components/modal";
import Item from "@/components/item";
import Intro from "@/components/intro";
import Loader from "@/components/loading";

interface data {
  id: number;
  image: string;
  title: string;
  text: string;
}

const page = () => {
  const locale = useLocale();
  const [file, setFile] = useState("");
  const [article, setArticle] = useState("#experiencias-formativas");
  const [data, setData] = useState<data[]>([]);
  const [loading, setLoading] = useState(true);
  // Con el idioma de la página: de la presentacion "Residencias" se usan la foto y la descripcion.
  const apiURL = process.env.NEXT_PUBLIC_API_URL + "/intros/" + locale;

  useEffect(() => {
    document.title = "Residencias Espacio Cerámica";
  }, []);

  useEffect(() => {
    async function getItems() {
      try {
        const res = await fetch(apiURL);
        if (!res.ok) throw new Error("Error al obtener datos");
        const data = await res.json();
        setData(data);
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    }

    getItems();
  }, []);

  const goTo = (id: string) => {
    setArticle(id);
    const article = document.querySelector(id);
    if (!article) return;
    article.scrollIntoView({ behavior: "smooth" });
  };

  if (loading) return <Loader />;

  return (
    <section>
      <div className="flex flex-col lg:items-center lg:flex-row gap-x-12 gap-y-8 relative mb-20">
        <div className="lg:w-1/3 flex flex-col text-sm min-h-50vh">
          <div className="flex lg:justify-center mt-20 lg:mt-0">
            <img
              src="/logo/logo-residencias.png"
              alt="Logo"
              className="w-2/3 max-w-md"
            />
          </div>
          {/* Descripcion cargada en el panel (Presentaciones > Residencias). El titulo no se repite
              porque el logo ya dice "Residencias". Si la descripcion esta vacia, no se muestra nada. */}
          {data[2]?.text && (
            <div className="flex lg:justify-center mt-8">
              <p className="lg:w-2/3 max-w-md leading-snug whitespace-break-spaces">
                {data[2].text}
              </p>
            </div>
          )}
        </div>
        <div className="flex lg:w-2/3 aspect-4/5 lg:aspect-5/4 overflow-hidden">
          <Image src={data[2].image} alt="Residencias" />
        </div>
      </div>

      <div className="sticky top-10 flex flex-col w-full bg-[#f6f6f7] py-2 z-10">
        <Item
          title={
            locale === "es" ? "Experiencias Formativas" : "Educational Experiences"
          }
          active={article === "#experiencias-formativas"}
          action={() => goTo("#experiencias-formativas")}
        />
        <Item
          title={locale === "es" ? "Voluntarios" : "Volunteers"}
          active={article === "#voluntariados"}
          action={() => goTo("#voluntariados")}
        />
      </div>

      <div className="scroll-mt-12" id="experiencias-formativas">
        <Intro locale={locale} section={4} />
      </div>

      <Inscripciones locale={locale} />

      <Gallery section={2} setFile={setFile} />

      <Voluntariados locale={locale} />

      {file ? <Modal file={file} setFile={setFile} /> : null}
    </section>
  );
};

export default page;
