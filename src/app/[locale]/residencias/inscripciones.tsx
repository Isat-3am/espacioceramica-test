"use client";
import { useState, useEffect } from "react";
import { useTranslations } from "next-intl";
import ButtonNavigation from "@/components/ButtonNavigation";

interface data {
  id: number;
  slug: string;
  title: string;
  // "experiencia" (Experiencias Formativas) o "residencia"; lo elige la artesana en el panel.
  tipo?: string;
}

// Inscripciones abiertas, en dos columnas: Experiencias Formativas y Residencias.
// Cada boton lleva a la pagina con el formulario de inscripcion. En celular, una columna arriba de la otra.
const Inscripciones = ({ locale }: { locale: string }) => {
  const t = useTranslations("Residencias");
  const [data, setData] = useState<data[]>([]);
  const apiURL = process.env.NEXT_PUBLIC_API_URL + "/residencias/" + locale;

  useEffect(() => {
    async function getData() {
      try {
        const res = await fetch(apiURL);
        if (!res.ok) throw new Error("Error al obtener datos");
        const result = await res.json();
        setData(Array.isArray(result) ? result : []);
      } catch (error) {
        console.error(error);
      }
    }

    getData();
  }, []);

  // Sin inscripciones cargadas no se muestra nada.
  if (data.length === 0) return null;

  // Lo que no tenga tipo (datos viejos) cuenta como residencia.
  const columnas = [
    {
      key: "residencia",
      title: t("typeResidencias"),
      items: data.filter((i) => i.tipo !== "experiencia"),
    },
    {
      key: "experiencia",
      title: t("typeExperiencias"),
      items: data.filter((i) => i.tipo === "experiencia"),
    },
  ];

  return (
    <div className="flex flex-col lg:flex-row items-start gap-4 pt-12">
      <div className="lg:w-1/4">
        <h2 className="text-sm font-[--lastik-regular] uppercase">
          {t("register")}
        </h2>
      </div>
      <div className="lg:w-3/4 w-full flex flex-col lg:flex-row gap-x-4 gap-y-10">
        {columnas.map((col) => (
          <div key={col.key} className="flex-1 flex flex-col gap-y-2 w-full">
            {/* Subtitulo de la columna: letra normal (Satoshi) con linea negra abajo, como las filas
                de `Info` de la ficha de producto. Asi se distingue del titulo "Inscripcion" (Lastik). */}
            <h3 className="text-sm uppercase leading-4 pb-2 mb-2 border-b border-black">
              {col.title}
            </h3>
            {col.items.length === 0 ? (
              <p className="text-sm opacity-70">{t("noneOpen")}</p>
            ) : (
              col.items.map((item) => (
                <ButtonNavigation
                  key={item.id}
                  label={item.title}
                  href={"/residencias/" + item.slug}
                />
              ))
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default Inscripciones;
