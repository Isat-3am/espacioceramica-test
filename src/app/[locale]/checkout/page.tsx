"use client";
import { useState } from "react";
import { useTranslations, useLocale } from "next-intl";
import CartResume from "@/components/CartResume";
import { InfoType } from "@/types/types";
import { useForm, SubmitHandler } from "react-hook-form";
import Error from "@/components/Error";
import { useCart } from "@/context/CartContext";
import { useRouter } from "@/i18n/navigation";

// PayPal oculto por ahora: se va a incorporar con la cuenta de los artesanos.
// Para mostrarlo de nuevo, poner en true (el codigo del medio de pago sigue ahi).
const PAYPAL_ENABLED = false;

const Page = () => {
  const [payType, setPayType] = useState("mercadopago");
  const [mpLoading, setMpLoading] = useState(false);
  const [mpError, setMpError] = useState("");
  const locale = useLocale();
  const t = useTranslations("Cart");
  const c = useTranslations("Contact");
  const { setBuyer } = useCart();
  const router = useRouter();
  const { items, buyer } = useCart();

  const Label = ({ title }: { title: string }) => {
    return (
      <span className="text-xs opacity-70 font-medium uppercase">{title}</span>
    );
  };

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<InfoType>({
    defaultValues: {
      email: buyer.email,
      name: buyer.name,
      phone: buyer.phone,
      address: buyer.address,
      city: buyer.city,
      zip: buyer.zip,
      delv_type: buyer.delv_type,
      pay_type: buyer.pay_type,
    },
  });

  const onSubmit: SubmitHandler<InfoType> = async (data) => {
    setBuyer({ ...data, pay_type: payType });

    if (payType === "mercadopago") {
      setMpError("");
      setMpLoading(true);
      try {
        // El pago lo crea el backend propio (precios y stock salen de su base de datos).
        // text/plain evita el preflight de CORS; el cuerpo es JSON.
        const res = await fetch(
          process.env.NEXT_PUBLIC_API_URL + "/mp/create-preference",
          {
            method: "POST",
            headers: { "Content-Type": "text/plain" },
            body: JSON.stringify({
              buyer: data,
              locale,
              items: items.map((i) => ({ id: i.id, quantity: i.quantity })),
            }),
          },
        );
        const result = await res.json();
        if (result.ok && result.url) {
          window.location.href = result.url;
          return;
        }
        setMpError(result.error || t("mpError"));
      } catch {
        setMpError(t("mpError"));
      }
      setMpLoading(false);
      return;
    }

    router.push("/checkout/end");
  };

  if (items.length === 0)
    return (
      <section>
        <div className="text-center text-lg font-medium mt-8">{t("empty")}</div>
      </section>
    );

  return (
    <section className="flex flex-col-reverse lg:flex-row items-stretch gap-x-4 gap-y-8 h-full max-w-5xl m-auto mt-8">
      <div className="lg:w-3/5 flex flex-col gap-y-8 pb-4">
        <h1 className="text-lg font-[--lastik-regular] uppercase">
          {t("title")}
        </h1>

        <form
          onSubmit={handleSubmit(onSubmit)}
          className="flex flex-col gap-y-4"
        >
          <div
            className={`p-6 border border-gray-300 flex flex-col gap-y-6 bg-white`}
          >
            <h2 className="font-medium">{t("email")}</h2>
            <div className="flex flex-col gap-y-1">
              <Label title={c("email")} />
              <input
                placeholder={t("emailPlaceholder")}
                className="bg-black/10 p-2 h-12 text-sm"
                {...register("email", {
                  required: t("required"),
                  maxLength: 50,
                  pattern: {
                    value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                    message: "Dirección de correo electrónico inválida",
                  },
                })}
              />
              <Error error={errors.email} />
              <span className="opacity-70 text-sm">{t("notifications")}</span>
            </div>
          </div>
          <div
            className={`p-6 border border-gray-300 flex flex-col gap-y-6 bg-white`}
          >
            <h2 className="font-medium">{t("info")}</h2>
            <div className="flex flex-col gap-y-1">
              <Label title={c("name")} />
              <input
                type="text"
                {...register("name", { required: t("required") })}
                className="bg-black/10 p-2 h-12 text-sm"
              />
              <Error error={errors.name} />
              <Label title={c("phone")} />
              <input
                type="text"
                {...register("phone", {})}
                className="bg-black/10 p-2 h-12 text-sm"
              />
              <Error error={errors.phone} />
              <Label title={c("address")} />
              <input
                {...register("address", { required: t("required") })}
                className="bg-black/10 p-2 h-12 text-sm uppercase"
              />
              <Error error={errors.address} />
              <Label title={c("city")} />
              <input
                {...register("city", { required: t("required") })}
                className="bg-black/10 p-2 h-12 text-sm uppercase"
              />
              <Error error={errors.city} />
              <Label title={c("zip")} />
              <input
                {...register("zip", { required: t("required") })}
                className="bg-black/10 p-2 h-12 text-sm uppercase"
              />
              <Error error={errors.zip} />
            </div>
          </div>
          <div
            className={`p-6 border border-gray-300 flex flex-col gap-y-6 bg-white`}
          >
            <h2 className="font-medium">{t("payType")}</h2>
            <div className="flex flex-col gap-y-3">
                <button
                  onClick={() => setPayType("mercadopago")}
                  type="button"
                  className={`w-full flex items-center gap-x-4 p-4 border text-left cursor-pointer ${
                    payType === "mercadopago"
                      ? "border-black"
                      : "border-gray-300 hover:border-black/50"
                  }`}
                >
                  <span className="w-5 h-5 shrink-0 rounded-full border border-black flex items-center justify-center">
                    {payType === "mercadopago" && (
                      <span className="w-2.5 h-2.5 rounded-full bg-black"></span>
                    )}
                  </span>
                  <span className="font-medium">{t("mercadoPago")}</span>
                </button>
                <button
                  onClick={() => setPayType("Transferencia bancaria")}
                  type="button"
                  className={`w-full flex items-center gap-x-4 p-4 border text-left cursor-pointer ${
                    payType === "Transferencia bancaria"
                      ? "border-black"
                      : "border-gray-300 hover:border-black/50"
                  }`}
                >
                  <span className="w-5 h-5 shrink-0 rounded-full border border-black flex items-center justify-center">
                    {payType === "Transferencia bancaria" && (
                      <span className="w-2.5 h-2.5 rounded-full bg-black"></span>
                    )}
                  </span>
                  <span className="font-medium">{t("bankTransfer")}</span>
                </button>
              {PAYPAL_ENABLED && (
                <button
                  onClick={() => setPayType("paypal")}
                  type="button"
                  className={`w-full flex items-center gap-x-4 p-4 border text-left cursor-pointer ${
                    payType === "paypal"
                      ? "border-black"
                      : "border-gray-300 hover:border-black/50"
                  }`}
                >
                  <span className="w-5 h-5 shrink-0 rounded-full border border-black flex items-center justify-center">
                    {payType === "paypal" && (
                      <span className="w-2.5 h-2.5 rounded-full bg-black"></span>
                    )}
                  </span>
                  <span className="font-medium">{t("paypal")}</span>
                </button>
              )}
            </div>
            {payType === "mercadopago" && (
              <div className="text-sm">{t("mercadoPagoInfo")}</div>
            )}
            {payType === "Transferencia bancaria" && (
              <div className="flex flex-col">
                <div className="text-sm">
                  <strong>Entidad</strong> Banco del Chubut S.A. <br />
                  <strong>CBU</strong> 0830010234003144210017 <br />
                  <strong>Titular</strong> Lopez Fernando Gabriel <br />
                  <strong>Tipo y N° de cuenta</strong> CA $ 01000031442100106
                  <br />
                  <strong>Alias</strong> proyectoliebre <br />
                  <strong>CUIT</strong> 20278809723
                </div>
              </div>
            )}
            {PAYPAL_ENABLED && payType === "paypal" && (
              <div className="text-sm">
                <strong>PayPal:</strong>{" "}
                <a
                  href="https://www.paypal.com/paypalme/DaianaLopez991"
                  target="_blank"
                  rel="noreferrer"
                  className="hover:underline"
                >
                  @DaianaLopez991
                </a>
              </div>
            )}
          </div>

          {mpError && <Error error={{ type: "validate", message: mpError }} />}

          <button
            className="uppercase cursor-pointer underline font-base hover:opacity-50 font-[--lastik-regular] mb-8 disabled:opacity-50 disabled:cursor-wait"
            type="submit"
            disabled={mpLoading}
          >
            {mpLoading ? t("mpRedirecting") : t("endPurchase")}
          </button>
        </form>
      </div>
      <div className="lg:w-2/5">
        <CartResume />
      </div>
    </section>
  );
};

export default Page;
