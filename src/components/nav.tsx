"use client";
import { useEffect } from "react";
import { Link, usePathname } from "@/i18n/navigation";
import Lan from "@/components/lan";
import { useTranslations } from "next-intl";

const Nav = () => {
  const pathname = usePathname();
  const t = useTranslations("Navigation");

  const closeMenu = () => {
    const main = document.querySelector("main") as HTMLElement;
    const nav = document.querySelector("#nav") as HTMLElement;
    if (main) {
      main.style.marginTop = "0";
    }
    if (nav) {
      nav.style.top = "-650px";
    }
  };

  useEffect(() => {
    const main = document.querySelector("main") as HTMLElement;
    const nav = document.querySelector("#nav") as HTMLElement;

    const handleScroll = () => {
      if (main) {
        main.style.marginTop = "0";
      }
      if (nav) {
        nav.style.top = "-650px";
      }
    };

    window.addEventListener("scroll", handleScroll);

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  return (
    <div
      className="absolute left-0 px-4 transition-all w-full py-2 bg-white top-[-650px] flex justify-between z-50 h-[400px]"
      id="nav"
    >
      <div>
        <nav>
          <ul className="text-2xl font-[--lastik-regular] flex flex-col leading-6">
            <Link
              href="/taller-estudio"
              className={`hover:underline uppercase ${
                pathname === "/taller-estudio" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-1")}
            </Link>
            <Link
              href="/tienda"
              className={`hover:underline uppercase ${
                pathname === "/tienda" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-2")}
            </Link>
            <Link
              href="/residencias"
              className={`hover:underline uppercase ${
                pathname === "/residencias" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-3")}
            </Link>
            <Link
              href="/nosotros"
              className={`hover:underline uppercase ${
                pathname === "/nosotros" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-4")}
            </Link>
            <Link
              href="/newsletter"
              className={`hover:underline uppercase ${
                pathname === "/newsletter" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-6")}
            </Link>
            <Link
              href="/contacto"
              className={`hover:underline uppercase ${
                pathname === "/contacto" ? "underline" : ""
              }`}
              onClick={closeMenu}
            >
              {t("title-5")}
            </Link>
          </ul>
        </nav>
        <div className="mt-10 lg:hidden">
          <Lan />
        </div>
      </div>
      <div>
        <button className="hover:underline cursor-pointer" onClick={closeMenu}>
          {t("close")}
        </button>
      </div>
    </div>
  );
};

export default Nav;
