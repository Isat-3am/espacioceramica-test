"use client";

import { Link, usePathname } from "@/i18n/navigation";
import { useTranslations } from "next-intl";

const footer = () => {
  const pathname = usePathname();
  const t = useTranslations("Navigation");

  return (
    <div className="flex flex-col gap-y-20 mt-40">
      <nav className="mb-20">
        <ul className="text-2xl font-[--lastik-regular] flex flex-col leading-6">
          <Link
            href="/taller-estudio"
            className={`hover:underline uppercase ${
              pathname === "/taller-estudio" ? "underline" : ""
            }`}
          >
            {t("title-1")}
          </Link>
          <Link
            href="/tienda"
            className={`hover:underline uppercase ${
              pathname === "/tienda" ? "underline" : ""
            }`}
          >
            {t("title-2")}
          </Link>
          <Link
            href="/residencias"
            className={`hover:underline uppercase ${
              pathname === "/residencias" ? "underline" : ""
            }`}
          >
            {t("title-3")}
          </Link>
          <Link
            href="/nosotros"
            className={`hover:underline uppercase ${
              pathname === "/nosotros" ? "underline" : ""
            }`}
          >
            {t("title-4")}
          </Link>
          <Link
            href="/newsletter"
            className={`hover:underline uppercase ${
              pathname === "/newsletter" ? "underline" : ""
            }`}
          >
            {t("title-6")}
          </Link>
          <Link
            href="/contacto"
            className={`hover:underline uppercase ${
              pathname === "/contacto" ? "underline" : ""
            }`}
          >
            {t("title-5")}
          </Link>
        </ul>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-4 text-sm max-w-3xl leading-4">
        <div className="flex flex-col gap-y-2">
          <span>Espacio Cerámica </span>
          <div className="flex flex-col">
            <a
              href="https://instagram.com/espacioceramica"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:underline"
            >
              @espacioceramica
            </a>
            <a
              href="mailto:espacioceramicapatagonia@gmail.com"
              className="hover:underline"
            >
              espacioceramicapatagonia@gmail.com
            </a>
          </div>
          <span>Chubut, Patagonia Argentina.</span>
        </div>
        <div className="flex flex-col gap-y-2">
          <span>Fernando Lopez</span>
          <div className="flex flex-col">
            <a
              href="https://instagram.com/proyecto___liebre"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:underline"
            >
              @proyecto___liebre
            </a>
            <a
              href="mailto:espacioceramica27@gmail.com"
              className="hover:underline"
            >
              espacioceramica27@gmail.com
            </a>
          </div>
          <span>Chubut, Patagonia Argentina.</span>
        </div>
        <div className="flex flex-col gap-y-2">
          <span>Victoria Drisaldi</span>
          <div className="flex flex-col">
            <a
              href="https://instagram.com/victoriadrisaldi"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:underline"
            >
              @victoriadrisaldi
            </a>
            <a
              href="mailto:mariavictoriadrisaldi@gmail.com"
              className="hover:underline"
            >
              mariavictoriadrisaldi@gmail.com
            </a>
          </div>
          <span>Chubut, Patagonia Argentina.</span>
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-3 items-end gap-x-12 gap-y-4 text-sm max-w-3xl pb-4 leading-4">
        <div className="flex flex-col">
          <Link href={"/"} className="hover:underline">
            Creditos
          </Link>
          <Link href={"/"} className="hover:underline">
            FAQs
          </Link>
          <Link href={"/"} className="hover:underline">
            Políticas de Privacidad
          </Link>
        </div>
        <div className="flex flex-col">
          <span>Encontranos en</span>
          <a
            href="https://instagram.com/espacioceramica"
            target="_blank"
            rel="noopener noreferrer"
            className="hover:underline"
          >
            @espacioceramica
          </a>
        </div>
      </div>
    </div>
  );
};

export default footer;
