"use client";
import { useLocale, useTranslations } from "next-intl";
import Info from "@/components/info";
import Info2 from "@/components/info2";
import Envio from "@/components/envio";
import { useState, useEffect } from "react";
import { useParams } from "next/navigation";
import Loader from "@/components/loading";
import Image from "@/components/Image";
import { useCart } from "@/context/CartContext";
import { ItemType, ImageType } from "@/types/types";
import { formatPrice } from "@/utils/formatPrice";

const page = () => {
  const locale = useLocale();
  const t = useTranslations("Cart");
  const [data, setData] = useState<ItemType>();
  const [loading, setLoading] = useState(true);
  const [goToImage, setGoToImage] = useState(1);
  const { slug } = useParams();
  const apiURL =
    process.env.NEXT_PUBLIC_API_URL + "/product/" + slug + "/" + locale;
  const { addItem } = useCart();

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

  useEffect(() => {
    const sections = document.querySelectorAll("[id^='image-']");

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const id = Number(entry.target.id.replace("image-", ""));
            setGoToImage(id);
          }
        });
      },
      {
        root: null,
        threshold: 0.5,
        rootMargin: "0px 0px 0px 0px",
      }
    );

    sections.forEach((section) => observer.observe(section));

    return () => {
      sections.forEach((section) => observer.unobserve(section));
    };
  }, [data]);

  if (loading) return <Loader />;
  if (!data) return null;

  const goTo = (id: number) => {
    setGoToImage(id);
    const image = document.querySelector(`#image-${id}`);
    if (!image) return;
    image.scrollIntoView({ behavior: "smooth" });
  };

  const handleAddToCart = (data: ItemType) => {
    addItem({ ...data, quantity: 1 });
    const cart = document.querySelector("#cart") as HTMLElement;
    if (!cart) return;
    cart.style.right = "0";
  };

  return (
    <section className="relative">
      <div className="flex flex-col lg:flex-row-reverse pt-30 lg:pt-40 pb-2">
        <div className="lg:fixed lg:left-6 lg:w-2/5 flex flex-col gap-y-10 lg:pr-20 pb-8">
          <h1 className="text-3xl lg:text-4xl font-[--lastik-regular] uppercase">
            {data.title}
          </h1>
          <div>
            <Info title={t("description")} description={data.text} />
            <Info
              title={t("price")}
              description={
                formatPrice(data.price_ars, "ARS") +
                " / " +
                formatPrice(data.price_usd, "USD")
              }
            />
            <Info
              title={t("stock")}
              description={
                data.stock > 0
                  ? t("available", { count: data.stock })
                  : t("withoutStock")
              }
            />
            <Info title={t("author")} description={data.author} />
            <Info2
              title={t("size")}
              description={data.size}
              text={data.text_2}
            />
            <div className="flex gap-x-4 mb-2 pb-2 text-sm border-b border-black">
              <div className="w-1/3"> </div>
              <div className="w-2/3 font-[--lastik-regular]">
                {data.stock > 0 ? (
                  <button
                    className="cursor-pointer uppercase underline font-base hover:opacity-50"
                    onClick={() => handleAddToCart(data)}
                  >
                    {t("add")}
                  </button>
                ) : (
                  <span className="uppercase">{t("withoutStock")}</span>
                )}
              </div>
            </div>

            <Envio lan={locale} title={t("delivery")} />
          </div>
        </div>
        <div className="lg:w-3/5 flex">
          <div className="flex-1 flex flex-col gap-y-2 lg:pr-30">
            {data.images.map((image: ImageType, index: number) => {
              return (
                <div id={`image-${index + 1}`} key={image.id}>
                  <Image src={image.image} alt={data.title} />
                </div>
              );
            })}
          </div>
          <div className="fixed right-6 w-14 flex-col gap-y-1 hidden lg:flex z-10">
            {data.images.map((image: ImageType, index: number) => {
              return (
                <button
                  key={image.id}
                  onClick={() => goTo(index + 1)}
                  className={`${
                    goToImage === index + 1 ? "opacity-100" : "opacity-50"
                  } hover:opacity-100 cursor-pointer`}
                >
                  <Image src={image.image} alt={data.title} />
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
};

export default page;
