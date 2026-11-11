import { useState } from "react";

interface Props {
  src: string;
  alt: string;
  // Las fotos que se ven apenas se abre la pagina pueden pedirse con prioridad.
  priority?: boolean;
}

// Las imagenes se piden recien cuando estan por entrar en pantalla (carga diferida),
// asi la tienda no baja todas las fotos de golpe. Mientras carga, se ve el fondo gris.
const ImageComponent = ({ src, alt, priority = false }: Props) => {
  const [loaded, setLoaded] = useState(false);

  return (
    <img
      src={src}
      alt={alt}
      loading={priority ? "eager" : "lazy"}
      decoding="async"
      onLoad={() => setLoaded(true)}
      className={`h-full w-full object-cover object-center transition-all hover:opacity-90 ${
        loaded ? "" : "bg-black/10 animate-pulse"
      }`}
    />
  );
};

export default ImageComponent;
