"use client";
import { Suspense, useEffect, useRef } from "react";
import { useSearchParams } from "next/navigation";
import { useTranslations } from "next-intl";
import { useCart } from "@/context/CartContext";
import { Link } from "@/i18n/navigation";
import Footer from "@/components/footer";

// Pagina a la que vuelve el comprador desde Mercado Pago.
// El pedido y el descuento de stock los registra el aviso de Mercado Pago al backend (/mp/webhook), no esta pagina.
const Result = () => {
  const t = useTranslations("Cart");
  const params = useSearchParams();
  const result = params.get("r");
  const { clearCart } = useCart();
  const cleared = useRef(false);

  useEffect(() => {
    if (result !== "success" || cleared.current) return;
    cleared.current = true;
    clearCart();
  }, [result]);

  const message =
    result === "success"
      ? t("mpThanks")
      : result === "pending"
        ? t("mpPending")
        : t("mpFailure");

  return (
    <>
      <section className="max-w-5xl m-auto mt-8 font-medium text-lg text-center whitespace-break-spaces leading-5">
        {message}
        {result !== "success" && (
          <div className="mt-8 text-sm">
            <Link
              href="/checkout"
              className="uppercase underline font-[--lastik-regular] hover:opacity-50"
            >
              {t("mpRetry")}
            </Link>
          </div>
        )}
      </section>
      <Footer />
    </>
  );
};

const Page = () => (
  <Suspense fallback={null}>
    <Result />
  </Suspense>
);

export default Page;
