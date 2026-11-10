import { NextResponse } from "next/server";

// Crea el pago en Mercado Pago (Checkout Pro) y devuelve el link al que se manda al comprador.
// Los precios y el stock se leen de la API del backend: nunca se confia en lo que manda el navegador.

class UserError extends Error {}

type CartLine = { id: number; slug: string; quantity: number };

const isLocal = (url: string) => /^https?:\/\/(localhost|127\.0\.0\.1)/.test(url);

export async function POST(req: Request) {
  try {
    const accessToken = process.env.MP_ACCESS_TOKEN;
    const api = process.env.NEXT_PUBLIC_API_URL;
    if (!accessToken || !api) {
      console.error("Falta MP_ACCESS_TOKEN o NEXT_PUBLIC_API_URL");
      return NextResponse.json(
        { ok: false, error: "Mercado Pago no está configurado." },
        { status: 500 },
      );
    }

    const { buyer, items, locale } = await req.json();

    if (
      !buyer ||
      typeof buyer.name !== "string" ||
      !buyer.name.trim() ||
      typeof buyer.email !== "string" ||
      !buyer.email.trim()
    ) {
      throw new UserError("Faltan los datos del comprador.");
    }
    if (!Array.isArray(items) || items.length === 0 || items.length > 20) {
      throw new UserError("El carrito está vacío.");
    }

    const lines = await Promise.all(
      (items as CartLine[]).map(async (item) => {
        const quantity = Number(item.quantity);
        if (
          !Number.isInteger(quantity) ||
          quantity < 1 ||
          quantity > 20 ||
          typeof item.slug !== "string"
        ) {
          throw new UserError("Producto inválido en el carrito.");
        }

        const res = await fetch(
          `${api}/product/${encodeURIComponent(item.slug)}/es`,
          { cache: "no-store" },
        );
        const product = res.ok ? await res.json() : null;
        if (!product || Number(product.id) !== Number(item.id)) {
          throw new UserError("Un producto del carrito ya no está disponible.");
        }
        if (Number(product.stock) < quantity) {
          throw new UserError(`Sin stock suficiente: ${product.title}`);
        }
        const price = Number(product.price_ars);
        if (!Number.isFinite(price) || price <= 0) {
          throw new UserError(`Producto sin precio: ${product.title}`);
        }
        return {
          id: Number(product.id),
          title: String(product.title),
          quantity,
          price,
        };
      }),
    );

    const siteUrl = (process.env.SITE_URL || new URL(req.url).origin).replace(
      /\/$/,
      "",
    );
    const lang = locale === "en" ? "en" : "es";
    const local = isLocal(siteUrl);

    const preference = {
      items: lines.map((l) => ({
        id: String(l.id),
        title: l.title,
        quantity: l.quantity,
        unit_price: l.price,
        currency_id: "ARS",
      })),
      payer: { name: String(buyer.name), email: String(buyer.email) },
      back_urls: {
        success: `${siteUrl}/${lang}/checkout/mp?r=success`,
        pending: `${siteUrl}/${lang}/checkout/mp?r=pending`,
        failure: `${siteUrl}/${lang}/checkout/mp?r=failure`,
      },
      // Mercado Pago no acepta estos dos datos con direcciones locales (localhost).
      ...(local
        ? {}
        : {
            auto_return: "approved",
            notification_url: `${siteUrl}/api/mp/webhook`,
          }),
      external_reference: crypto.randomUUID(),
      statement_descriptor: "ESPACIO CERAMICA",
      // El webhook usa esto para registrar el pedido y descontar el stock.
      metadata: {
        buyer: {
          name: String(buyer.name),
          email: String(buyer.email),
          phone: String(buyer.phone ?? ""),
          address: String(buyer.address ?? ""),
          city: String(buyer.city ?? ""),
          zip: String(buyer.zip ?? ""),
        },
        items: lines.map((l) => ({
          id: l.id,
          quantity: l.quantity,
          title: l.title,
          price: l.price,
        })),
      },
    };

    const mpRes = await fetch("https://api.mercadopago.com/checkout/preferences", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${accessToken}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify(preference),
    });
    const mpData = await mpRes.json().catch(() => null);

    if (!mpRes.ok || !mpData?.init_point) {
      console.error("Mercado Pago rechazó la preferencia:", mpRes.status, mpData);
      return NextResponse.json(
        { ok: false, error: "No se pudo iniciar el pago." },
        { status: 502 },
      );
    }

    return NextResponse.json({ ok: true, url: mpData.init_point });
  } catch (error) {
    if (error instanceof UserError) {
      return NextResponse.json({ ok: false, error: error.message }, { status: 400 });
    }
    console.error(error);
    return NextResponse.json(
      { ok: false, error: "No se pudo iniciar el pago." },
      { status: 500 },
    );
  }
}
