"use client";
import { useState, useEffect } from "react";
import { useTranslations } from "next-intl";
import ButtonNavigation from "@/components/ButtonNavigation";

interface data {
  id: number;
  slug: string;
  title: string;
}

// Botones de las residencias abiertas: cada uno lleva a la pagina con el formulario de inscripcion.
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

  // Sin residencias cargadas no se muestra nada.
  if (data.length === 0) return null;

  return (
    <div className="flex flex-col lg:flex-row items-start gap-4 pt-20">
      <div className="lg:w-1/4">
        <h2 className="text-sm font-[--lastik-regular] uppercase">
          {t("register")}
        </h2>
      </div>
      <div className="lg:w-1/2 flex flex-col gap-y-2 w-full">
        {data.map((item) => (
          <ButtonNavigation
            key={item.id}
            label={item.title}
            href={"/residencias/" + item.slug}
          />
        ))}
      </div>
    </div>
  );
};

export default Inscripciones;
