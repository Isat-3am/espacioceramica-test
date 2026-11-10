"use client";
import { useEffect, useState } from "react";

// Flecha larga y fina que se mueve suavemente para indicar que se puede seguir hacia abajo.
// Desaparece apenas se empieza a bajar.
// Por defecto va fija abajo al centro de la pantalla; con `inline` se queda donde se la ponga
// (en celular se usa al lado del titulo "Ultimas publicaciones", sobre el margen derecho).
const ScrollHint = ({ inline = false }: { inline?: boolean }) => {
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY < 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <div
      aria-hidden="true"
      className={`pointer-events-none transition-opacity duration-500 ${
        inline ? "" : "fixed bottom-6 left-1/2 -translate-x-1/2 z-10"
      } ${visible ? "opacity-100" : "opacity-0"}`}
    >
      <svg
        className="scroll-hint-arrow"
        width="14"
        height="36"
        viewBox="0 0 14 36"
        fill="none"
        stroke="currentColor"
        strokeWidth="1"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <line x1="7" y1="1" x2="7" y2="34" />
        <polyline points="1,28 7,34 13,28" />
      </svg>
    </div>
  );
};

export default ScrollHint;
