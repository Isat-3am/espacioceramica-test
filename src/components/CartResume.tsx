import { Link } from "@/i18n/navigation";
import { useCart } from "@/context/CartContext";
import { useTranslations } from "next-intl";
import Image from "@/components/Image";
import { formatPrice } from "@/utils/formatPrice";

const CartResume = () => {
  const { items, total, removeItem, incrementQuantity, decrementQuantity } =
    useCart();
  const t = useTranslations("Cart");

  return (
    <div className="flex flex-col gap-y-8">
      <h2 className="text-lg font-[--lastik-regular] uppercase">
        {t("order")}
      </h2>
      <div className="p-6 border border-gray-300 bg-white">
        {items.map((item) => (
          <article key={item.id} className="flex justify-between mb-2">
            <div className="flex gap-2">
              <Link
                href={`/tienda/${item.slug}`}
                className="aspect-square w-20"
              >
                <Image src={item.image} alt={item.title} />
              </Link>
              <div className="leading-4">
                <h3 className="font-medium">{item.title}</h3>
                <h4 className="text-sm">{item.author}</h4>
              </div>
            </div>
            <div className="flex flex-col gap-y-1 items-end">
              <div className="leading-4 flex flex-col items-end mb-1">
                <span>
                  {formatPrice(item.price_ars * item.quantity, "ARS")}
                </span>
                <span>
                  {formatPrice(item.price_usd * item.quantity, "USD")}
                </span>
              </div>
              <div className="flex gap-x-1">
                <span className="bg-black text-white p-2 uppercase rounded-full w-6 h-6 justify-center items-center flex text-sm hover:bg-black/70 cursor-pointer font-medium">
                  {item.quantity}
                </span>
                <button
                  onClick={() => decrementQuantity(item.id)}
                  className="bg-black/30 text-white p-2 uppercase rounded-full w-6 h-6 justify-center items-center flex text-sm hover:bg-black cursor-pointer font-medium"
                >
                  -
                </button>
                <button
                  onClick={() => incrementQuantity(item.id)}
                  className="bg-black/30 text-white p-2 uppercase rounded-full w-6 h-6 justify-center items-center flex text-sm hover:bg-black cursor-pointer font-medium"
                >
                  +
                </button>
              </div>
              <button
                onClick={() => removeItem(item.id)}
                className="mt-5 underline cursor-pointer text-sm opacity-70 hover:opacity-100 font-[--lastik-regular]"
              >
                {t("remove")}
              </button>
            </div>
          </article>
        ))}
        <div className="flex justify-between border-t border-b border-gray-300 py-4 font-medium mt-8">
          <div>{t("total")}</div>
          <div className="flex flex-col leading-4">
            <span>{formatPrice(total, "ARS")}</span>
            <span>{formatPrice(total, "USD")}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartResume;
