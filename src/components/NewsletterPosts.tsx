"use client";
import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import ScrollHint from "@/components/ScrollHint";

interface Post {
  id: number;
  title: string;
  excerpt: string;
  date: string;
  url: string;
  image: string | null;
}

// Ultimas publicaciones de la newsletter: foto, fecha, titulo y el comienzo del texto.
// Para leer la nota completa se abre en Substack (ahi se suscriben si quieren).
const NewsletterPosts = ({ pub, archiveUrl }: { pub: string; archiveUrl: string }) => {
  const locale = useLocale();
  const t = useTranslations("Newsletter");
  const [posts, setPosts] = useState<Post[]>([]);

  useEffect(() => {
    async function getPosts() {
      try {
        const res = await fetch("/api/newsletter/posts?pub=" + encodeURIComponent(pub));
        if (!res.ok) throw new Error("Error al obtener datos");
        const data = await res.json();
        setPosts(Array.isArray(data.posts) ? data.posts : []);
      } catch (error) {
        console.error(error);
      }
    }

    getPosts();
  }, [pub]);

  // Sin publicaciones (o si Substack no responde) no se muestra nada.
  if (posts.length === 0) return null;

  const formatDate = (value: string) =>
    new Date(value).toLocaleDateString(locale === "es" ? "es-AR" : "en-US", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });

  return (
    <div className="flex flex-col gap-y-6 pt-12">
      {/* En celular la flecha de "seguir bajando" va en esta fila, sobre el margen derecho. */}
      <div className="flex items-center justify-between">
        <h2 className="text-sm font-[--lastik-regular] uppercase">
          {t("latest")}
        </h2>
        <div className="lg:hidden -my-4">
          <ScrollHint inline />
        </div>
      </div>

      <div className="flex flex-col max-w-3xl">
        {posts.map((post) => (
          <article
            key={post.id}
            className="flex flex-col sm:flex-row gap-x-4 gap-y-3 pb-4 mb-4 border-b border-black"
          >
            {post.image && (
              <a
                href={post.url}
                target="_blank"
                rel="noopener noreferrer"
                className="sm:w-1/3 aspect-[7/5] overflow-hidden shrink-0"
              >
                <img
                  src={post.image}
                  alt={post.title}
                  loading="lazy"
                  className="h-full w-full object-cover object-center transition-all hover:opacity-90"
                />
              </a>
            )}
            <div className="flex flex-col gap-y-2 text-sm leading-snug">
              <span className="opacity-70">{formatDate(post.date)}</span>
              <h3 className="font-[--lastik-regular] uppercase">{post.title}</h3>
              {post.excerpt && <p className="line-clamp-3">{post.excerpt}</p>}
              <a
                href={post.url}
                target="_blank"
                rel="noopener noreferrer"
                className="uppercase underline font-[--lastik-regular] hover:opacity-50"
              >
                {t("read")}
              </a>
            </div>
          </article>
        ))}
      </div>

      <a
        href={archiveUrl}
        target="_blank"
        rel="noopener noreferrer"
        className="text-sm uppercase underline font-[--lastik-regular] hover:opacity-50"
      >
        {t("all")}
      </a>
    </div>
  );
};

export default NewsletterPosts;
