import { NextResponse } from "next/server";
import { sendOrderMail, sendOwnerAlert } from "@/lib/orderMail";

// Mercado Pago avisa aca cuando cambia el estado de un pago.
// No se confia en el contenido del aviso: se consulta el pago a Mercado Pago con nuestro token.
// Si el pago esta aprobado, se registra el pedido en el backend (descuenta stock) y se mandan los mails.
//
// Respuestas: 200 = listo (o nada que hacer); 5xx = que Mercado Pago reintente mas tarde.

type MetaItem = { id: number; quantity: number; title: string; price: number };

const ok = () => NextResponse.json({ ok: true });

export async function POST(req: Request) {
  const accessToken = process.env.MP_ACCESS_TOKEN;
  const orderToken = process.env.ORDER_API_TOKEN;
  const api = process.env.NEXT_PUBLIC_API_URL;
  if (!accessToken || !orderToken || !api) {
    console.error("Webhook MP: falta MP_ACCESS_TOKEN, ORDER_API_TOKEN o NEXT_PUBLIC_API_URL");
    return NextResponse.json({ ok: false }, { status: 500 });
  }

  try {
    const url = new URL(req.url);
    const body = await req.json().catch(() => ({}));
    const type = body?.type ?? url.searchParams.get("type") ?? url.searchParams.get("topic");
    const paymentId = String(body?.data?.id ?? url.searchParams.get("data.id") ?? url.searchParams.get("id") ?? "");

    // Solo interesan los avisos de pagos.
    if (type !== "payment" || !/^\d+$/.test(paymentId)) return ok();

    const payRes = await fetch(`https://api.mercadopago.com/v1/payments/${paymentId}`, {
      headers: { Authorization: `Bearer ${accessToken}` },
      cache: "no-store",
    });
    // 404: aviso de prueba o pago de otra cuenta. No hay nada que reintentar.
    if (payRes.status === 404) return ok();
    if (!payRes.ok) {
      console.error("Webhook MP: no se pudo leer el pago", paymentId, payRes.status);
      return NextResponse.json({ ok: false }, { status: 502 });
    }
    const payment = await payRes.json();

    if (payment.status !== "approved") return ok();

    const meta = payment.metadata;
    const buyer = meta?.buyer;
    const items: MetaItem[] = Array.isArray(meta?.items) ? meta.items : [];
    if (!buyer?.email || items.length === 0) {
      console.error("Webhook MP: pago aprobado sin datos del pedido", paymentId);
      return ok();
    }

    const orderRes = await fetch(`${api}/order/create`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Order-Token": orderToken,
      },
      body: JSON.stringify({
        comprador: {
          nombre: buyer.name,
          email: buyer.email,
          telefono: buyer.phone ?? "",
          direccion: [buyer.address, buyer.city, buyer.zip].filter(Boolean).join(", "),
        },
        medio_pago: "mercadopago",
        estado: "approved",
        pago_id: paymentId,
        items: items.map((i) => ({ item_id: i.id, cantidad: i.quantity })),
      }),
      cache: "no-store",
    });

    // Error del servidor del backend: que Mercado Pago reintente.
    if (orderRes.status >= 500) {
      console.error("Webhook MP: el backend fallo", paymentId, orderRes.status);
      return NextResponse.json({ ok: false }, { status: 502 });
    }

    const order = await orderRes.json().catch(() => null);

    // El pago ya se habia registrado antes: no se repite nada.
    if (order?.success && order?.duplicate) return ok();

    if (!order?.success) {
      // Pago aprobado pero el pedido no se pudo registrar (por ejemplo, se acabo el stock):
      // hay que avisarle a la duena para que reembolse o coordine con el comprador.
      const detalle = `Pago aprobado en Mercado Pago (id ${paymentId}) de ${buyer.name} (${buyer.email}) por $${payment.transaction_amount}, pero no se pudo registrar el pedido: ${order?.error ?? "error desconocido"}. Revisá el pago en Mercado Pago: puede que haya que reembolsarlo.`;
      console.error("Webhook MP:", detalle);
      await sendOwnerAlert("ATENCIÓN: pago aprobado sin pedido registrado", detalle).catch((e) =>
        console.error("No se pudo mandar el aviso:", e),
      );
      return ok();
    }

    const total = Number(order.total);
    const mismatch = Math.abs(total - Number(payment.transaction_amount)) > 1;

    // Si el mail falla, el pedido igual quedo registrado: no se pide reintento.
    await sendOrderMail({
      buyer,
      items: items.map((i) => ({ title: i.title, quantity: i.quantity, price_ars: i.price })),
      total,
      payment: "mercadopago",
      orderNumber: order.order_id,
      ownerNote: mismatch
        ? `Revisar: el monto pagado en Mercado Pago ($${payment.transaction_amount}) no coincide con el total del pedido ($${total}). Pago id ${paymentId}.`
        : `Pago Mercado Pago id ${paymentId}.`,
    }).catch((e) => console.error("No se pudo mandar el mail del pedido:", e));

    return ok();
  } catch (error) {
    console.error("Webhook MP:", error);
    return NextResponse.json({ ok: false }, { status: 500 });
  }
}
