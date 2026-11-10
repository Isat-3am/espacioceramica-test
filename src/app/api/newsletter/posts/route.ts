import { NextResponse } from "next/server";

// Lista de las ultimas publicaciones de la newsletter de Substack (solo las publicas).
// Se lee en el servidor (no desde el navegador) y se guarda una hora para que cargue rapido.
// Usa la lista publica de Substack, que no es parte de su documentacion oficial: si cambia o falla,
// devuelve una lista vacia y la pagina sigue mostrando el formulario de suscripcion.

type SubstackPost = {
  id: number;
  title: string;
  subtitle: string | null;
  description: string | null;
  post_date: string;
  canonical_url: string;
  cover_image: string | null;
  audience: string;
};

export async function GET(req: Request) {
  const pub = new URL(req.url).searchParams.get("pub") ?? "";
  // Solo el nombre de la publicacion (algo.substack.com): evita que se use para pedir otras direcciones.
  if (!/^[a-z0-9-]{1,63}$/i.test(pub)) {
    return NextResponse.json({ posts: [] }, { status: 400 });
  }

  try {
    const res = await fetch(
      `https://${pub}.substack.com/api/v1/archive?sort=new&limit=12`,
      {
        headers: { "User-Agent": "Mozilla/5.0 (compatible; EspacioCeramicaWeb)" },
        next: { revalidate: 3600 },
      },
    );
    if (!res.ok) throw new Error("Substack respondio " + res.status);
    const json = await res.json();
    // Substack devuelve la lista directa o dentro de { posts: [...] }.
    const data: SubstackPost[] = Array.isArray(json) ? json : (json?.posts ?? []);

    const posts = data
      .filter((p) => p.audience === "everyone")
      .slice(0, 6)
      .map((p) => ({
        id: p.id,
        title: p.title,
        excerpt: p.description || p.subtitle || "",
        date: p.post_date,
        url: p.canonical_url,
        image: p.cover_image,
      }));

    return NextResponse.json({ posts });
  } catch (error) {
    console.error("Newsletter: no se pudo leer Substack", error);
    return NextResponse.json({ posts: [] });
  }
}
