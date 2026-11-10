"use client";
import { useEffect, useState } from "react";

// Flecha larga y fina, abajo al centro de la pantalla, que se mueve suavemente para indicar que se
// puede seguir hacia abajo. Desaparece apenas se empieza a bajar.
const ScrollHint = () => {
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
      className={`pointer-events-none fixed bottom-6 left-1/2 -translate-x-1/2 z-10 transition-opacity duration-500 ${
        visible ? "opacity-100" : "opacity-0"
      }`}
    >
      <svg
        className="scroll-hint-arrow"
        width="14"
        height="72"
        viewBox="0 0 14 72"
        fill="none"
        stroke="currentColor"
        strokeWidth="1"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <line x1="7" y1="1" x2="7" y2="70" />
        <polyline points="1,64 7,70 13,64" />
      </svg>
    </div>
  );
};

export default ScrollHint;
