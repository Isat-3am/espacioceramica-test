"use client";
import { useState, useEffect } from "react";
import Loader from "@/components/loading";

interface data {
  id: string;
  name: string;
  url: string;
  acount: string;
}

const page = ({ locale }: { locale: string }) => {
  const [data, setData] = useState<data[]>([]);
  const [loading, setLoading] = useState(true);
  const apiURL = process.env.NEXT_PUBLIC_API_URL + "/volunteers/";

  useEffect(() => {
    async function getData() {
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

    getData();
  }, []);

  if (loading) return <Loader />;

  return (
    <div
      className="w-full flex flex-col lg:flex-row items-start gap-4 pt-20 scroll-mt-20 pb-12"
      id="voluntariados"
    >
      <div className="lg:w-1/4">
        <h2 className="text-sm font-[--lastik-regular] uppercase">
          {locale === "es" ? "Residentes" : "Residents"}
        </h2>
      </div>
      <div className="lg:w-1/2 text-sm">
        {data.map((item: data, index: number) => {
          return (
            <article key={index} className="flex items-center gap-x-1 text-sm ">
              <span className="uppercase font-[--lastik-regular]">
                {item.name}
              </span>
              <a
                href={item.url}
                target="_blank"
                rel="noopener noreferrer"
                className="hover:underline"
              >
                {item.acount}
              </a>
            </article>
          );
        })}
      </div>
      <div></div>
    </div>
  );
};

export default page;
