import { NextResponse } from "next/server";
import { sendOrderMail } from "@/lib/orderMail";

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { buyer, items, total, payment } = body;

    if (!Array.isArray(items) || items.length === 0) {
      return NextResponse.json(
        { ok: false, error: "Pedido vacío" },
        { status: 400 },
      );
    }

    await sendOrderMail({ buyer, items, total, payment });

    return NextResponse.json({ ok: true });
  } catch (error) {
    console.error(error);
    return NextResponse.json({ ok: false, error }, { status: 500 });
  }
}
