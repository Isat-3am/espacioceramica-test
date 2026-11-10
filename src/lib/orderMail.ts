import nodemailer from "nodemailer";
import { formatPrice } from "@/utils/formatPrice";

export const OWNER_EMAIL = "espacioceramicapatagonia@gmail.com";

export type MailBuyer = {
  name: string;
  email: string;
  phone?: string;
  address: string;
  city: string;
  zip: string;
};

export type MailItem = {
  title: string;
  quantity: number;
  price_ars: number;
};

const esc = (value: unknown) =>
  String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");

// Por defecto usa el SMTP de Ferozo (como antes). Para usar Gmail: SMTP_HOST=smtp.gmail.com,
// SMTP_USER=la casilla y SMTP_PASS=la clave de aplicacion de Gmail (no la contrasena de la cuenta).
const createTransporter = () => {
  const port = Number(process.env.SMTP_PORT) || 465;
  return nodemailer.createTransport({
    host: process.env.SMTP_HOST || "mda07.ferozo.com",
    port,
    secure: port === 465,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
  });
};

export async function sendOrderMail({
  buyer,
  items,
  total,
  payment,
  orderNumber,
  ownerNote,
}: {
  buyer: MailBuyer;
  items: MailItem[];
  total: number;
  payment: string;
  orderNumber?: number | string;
  ownerNote?: string;
}) {
  const transporter = createTransporter();
  const number = orderNumber ?? Math.floor(Math.random() * 10000) + 10000;

  const itemsHtml = items
    .map(
      (i) =>
        `<tr><td>${esc(i.title)}</td><td>${esc(i.quantity)}</td><td>${formatPrice(
          i.price_ars,
          "ARS",
        )}</td><td>${formatPrice(i.price_ars * i.quantity, "ARS")}</td></tr>`,
    )
    .join("");

  let html = `<div style="background-color: #f6f6f7; padding: 20px; color: #000000;">
      <h3>¡Gracias por tu compra, ${esc(buyer.name)}!</h3>
      <h2>Número de pedido: #${esc(number)}</h2>
      <hr />
      <h3>Detalles de la compra:</h3>
      <p><b>Forma de pago:</b> ${esc(payment)} <br />
      <b>Email:</b> ${esc(buyer.email)} <br />
      <b>Teléfono:</b> ${esc(buyer.phone)} <br />
      <b>Dirección:</b> ${esc(buyer.address)} <br />
      <b>Ciudad:</b> ${esc(buyer.city)} <br />
      <b>Código postal:</b> ${esc(buyer.zip)}</p>
      <hr />
      <h3>Resumen de tu pedido:</h3>
      <table border="1" cellpadding="6" cellspacing="0">
        <thead>
          <tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
        </thead>
        <tbody>${itemsHtml}</tbody>
      </table>
      <h3>Total: ${formatPrice(total, "ARS")}</h3>
      <hr />
     `;

  if (payment === "paypal")
    html += `<h3>Datos PayPal:</h3> <p>Para pagar, haz click en este enlace: <a
                  href="https://www.paypal.com/paypalme/DaianaLopez991"
                  target="_blank"
                  rel="noreferrer"
                  className="hover:underline"
                >
                  @DaianaLopez991
                </a></p>`;
  else if (payment === "mercadopago")
    html += `<h3>Pago recibido</h3>
      <p>Recibimos tu pago por Mercado Pago. No tenés que hacer nada más: nos vamos a comunicar con vos para coordinar la entrega.</p>`;
  else
    html += ` <h3>Datos bancarios:</h3>
      <p>Entidad Banco del Chubut S.A. <br />
CBU 0830010234003144210017  <br />
Titular Lopez Fernando Gabriel  <br />
Tipo y N° de cuenta CA $ 01000031442100106  <br />
Alias proyectoliebre  <br />
CUIT 20278809723 </p>
`;

  html += `<hr />
<p>Espacio Cerámica <br />
@espacioceramica <br />
espacioceramicapatagonia@gmail.com <br />
Chubut, Patagonia Argentina.</p> </div>`;

  await transporter.sendMail({
    from: `"Espacio Cerámica" <${process.env.SMTP_USER}>`,
    to: buyer.email,
    subject: "Confirmación de compra",
    html,
  });

  await transporter.sendMail({
    from: `"Espacio Cerámica" <${process.env.SMTP_USER}>`,
    to: OWNER_EMAIL,
    subject: `Nueva compra de ${buyer.name}`,
    html: ownerNote
      ? `<p><b>${esc(ownerNote)}</b></p>${html}`
      : html,
  });
}

// Aviso solo para la duena de la tienda (por ejemplo: pago aprobado que no se pudo registrar).
export async function sendOwnerAlert(subject: string, text: string) {
  const transporter = createTransporter();
  await transporter.sendMail({
    from: `"Espacio Cerámica" <${process.env.SMTP_USER}>`,
    to: OWNER_EMAIL,
    subject,
    html: `<div style="padding: 20px; color: #000000;"><p>${esc(text)}</p></div>`,
  });
}
