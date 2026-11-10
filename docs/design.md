# Criterios de diseño de Espacio Cerámica

Para respetar en **todo** lo nuevo que se agregue al sitio. Sacado del código actual (`src/`), no inventado. Si algo nuevo necesita romper una regla, se anota acá el motivo.

Idea general: sitio editorial, sobrio y de poco color. Fondo gris muy claro, texto negro, tipografía chica, mayúsculas para títulos, líneas finas negras y mucho aire. Las fotos mandan.

## 1. Colores
| Uso | Valor | Dónde se usa |
|---|---|---|
| Fondo de la página | `#f6f6f7` | `body`, header, barra sticky de secciones |
| Texto | negro (`#1d1d1d` / `text-black`) | todo |
| Texto secundario | `text-black/70` o `opacity-70` | aclaraciones, notas |
| Carga (skeleton) | `bg-black/10 animate-pulse` | imágenes mientras cargan |
| Inputs | `bg-black/10 p-2 h-12 text-sm` | formularios (checkout) |
| Panel superpuesto | `bg-white` | menú desplegable, carrito |
| Líneas | `border-black` (1 px) | filas de información |

Reglas:
- **No se agregan colores de acento** (nada de azul, rojo ni verde). Todo es negro, blanco y grises.
- Un error o aviso se muestra con texto y `opacity-60` (ver `components/Error.tsx`), con prefijo `*`.
- Sin degradados. Sin sombras, salvo la del carrito (`shadow`).

## 2. Tipografía
- **Satoshi** (variable, peso 400): texto corriente. Es la fuente por defecto del `body`.
- **Lastik Regular**: títulos, etiquetas, valores y botones. Se usa con la clase `font-[--lastik-regular]`, copiando el patrón existente.
- Tamaños: casi todo es `text-sm`, con `leading-4` o `leading-snug`.
- Título de una ficha de producto: `text-3xl lg:text-4xl font-[--lastik-regular] uppercase`.
- Título de una página de contenido (residencia, newsletter): el mismo tamaño chico y sutil de los títulos de sección, `text-sm font-[--lastik-regular] uppercase`. No usar el título grande ahí.
- Páginas con un bloque de presentación (ej. Newsletter): en pantalla ancha, título y texto a la izquierda y el elemento principal a la derecha, centrados a lo alto de la pantalla (`lg:min-h-[calc(100svh-4rem)] lg:items-center`). En celular, apilados.
- Página de una residencia: en pantalla ancha, título y reseña a la izquierda (columna fija, centrada a lo alto de la pantalla mientras se baja) y el formulario de Google a la derecha, desde el borde superior (debajo del menú). El enlace de volver va arriba a la izquierda. En celular, todo en fila: volver, título, reseña, formulario.
- Flecha de "seguir bajando" (`ScrollHint`): línea larga y fina (1 px), abajo al centro, con un movimiento lento y corto; desaparece al empezar a bajar. Respeta `prefers-reduced-motion`.
- Título de sección: `text-sm font-[--lastik-regular] uppercase`.
- Títulos y botones siempre en **mayúsculas** (`uppercase`). El texto corrido, no.
- No se agregan otras fuentes. No se usan negritas (`font-medium` solo aparece en el checkout).

## 3. Estructura y espaciado
- `<main>` tiene `px-4`. No se pone un contenedor con ancho máximo en las páginas de contenido.
- Secciones separadas con `pt-20`. Para anclas, `scroll-mt-12`.
- Páginas de contenido en filas de tres columnas en escritorio: `lg:w-1/4` (título) · `lg:w-1/2` (imagen o contenido) · `lg:w-1/4` (texto). En celular, todo en una columna (`flex-col`).
- Se diseña **primero para celular** y se agrega `lg:` para escritorio.
- Barra de subsecciones fija: `sticky top-10 bg-[#f6f6f7] py-2 z-10`.
- El encabezado es fijo (`sticky top-0 z-40`). El menú y el carrito son paneles que se deslizan.
- Sin esquinas redondeadas (solo `rounded-full` para puntitos). Sin tarjetas con borde ni sombra.

## 4. Componentes que ya existen (reusar, no duplicar)
| Componente | Para qué |
|---|---|
| `ButtonNavigation` | Botón principal: `bg-black text-white p-3 uppercase text-sm border hover:bg-white hover:text-black`, en Lastik. Es un enlace. |
| `ButtonSubmit` | Acción en texto: `underline uppercase hover:opacity-50`, en Lastik. |
| `Item` | Selector con puntito: `( • ) TÍTULO`. Se usa en la barra de subsecciones. |
| `Info` | Fila de datos: etiqueta a la izquierda (`w-1/3`) y valor en Lastik (`w-2/3`), con línea negra debajo. |
| `Info2` | Igual que `Info`, con un texto extra y más espacio abajo. |
| `Image` | Imagen con skeleton y `object-cover`; baja a `opacity-90` al pasar el mouse. |
| `Intro` | Fila de tres columnas: título · imagen · texto. |
| `Gallery`, `Modal`, `ImageModal` | Galería de fotos y vista ampliada. |
| `Loader` | Indicador de carga (BeatLoader). |
| `Error` | Mensaje de error de formulario. |

## 5. Imágenes
- Formas fijas con `aspect-*` y `overflow-hidden`: tarjetas de producto `aspect-[5/7]`; fotos anchas `aspect-[7/5]`; portada `aspect-4/5` en celular y `aspect-5/4` en escritorio.
- Siempre `object-cover object-center`. Nunca se deforman.
- El panel sube las fotos a 1080 px de ancho, en JPG.
- Todas llevan `alt` con el título.

## 6. Comportamiento
- Cada página carga sus datos con `fetch` a `NEXT_PUBLIC_API_URL` dentro de un `useEffect`, con `Loader` mientras espera y `console.error` si falla.
- Textos en español e inglés: todo texto fijo va en `src/messages/es.json` y `en.json`. El contenido que carga la artesana viaja con el idioma en la URL de la API (`/es` o `/en`).
- Los links a otras páginas usan `Link` de `@/i18n/navigation`, no `<a>`, para conservar el idioma. Los links externos usan `<a target="_blank" rel="noopener noreferrer">`.
- Hover: subrayado (`hover:underline`) o `hover:opacity-50`. Sin animaciones llamativas; solo `transition-all`.
- El título de la pestaña del navegador se pone con `document.title`.

## 7. Cómo se arma una página nueva
1. Copiar el esqueleto de una página parecida (`residencias/page.tsx` para listas con secciones, `tienda/[slug]/page.tsx` para una ficha).
2. Usar solo los componentes de la sección 4.
3. Agregar los textos a `es.json` y `en.json`.
4. Probarla en celular (375 px) y en escritorio antes de pasarla.

## 8. Residencias

**Etapa 1 (hecha, 10/11/2026):** solo título y formulario. En la sección Pasantías de `/residencias` aparece un bloque "Inscripción" con un botón (`ButtonNavigation`) por cada residencia cargada. Cada botón lleva a `/residencias/[slug]`, que muestra el título (`text-3xl lg:text-4xl font-[--lastik-regular] uppercase`), el formulario de Google incrustado y el enlace "Abrir el formulario en una pestaña nueva". El panel carga solo **título** y **link del formulario** (solo se aceptan links de Google Forms). Todo lo demás de esta sección es la **etapa 2, en pausa y sin presupuestar**; el código ya probado está en `docs/backend/residencias-pausado/`.

Diseño propuesto para la etapa 2 (referencia, pendiente de aprobar):

Hoy `/residencias` tiene dos secciones: **Pasantías** (texto fijo y galería) y **Voluntarios** (lista de cuentas de Instagram). La artesana ya puede cambiar las fotos y el texto de Pasantías desde el panel. Voluntarios no cambia.

**Lo nuevo:** dentro de Pasantías, debajo del texto, una lista de **botones**, uno por cada residencia que la artesana carga y deja activa. Cada botón lleva a una **página propia** con la información básica y el formulario de Google incrustado.

### Botón en la lista
Mismo estilo que `ButtonNavigation` (negro, mayúsculas, Lastik), o una fila de texto con línea negra abajo, como `Info`. Lleva el título y la fecha, por ejemplo: `RESIDENCIA 1 · 11 – 17 ENERO 2027`. Sin foto grande en la lista.

### Página de una residencia (`/residencias/[slug]`)
Igual a la ficha de producto (`tienda/[slug]`): título grande en mayúsculas, filas `Info`, y las fotos al costado en escritorio.

**Se muestra en la página** (información que la gente necesita antes de inscribirse):
- Título y descripción corta (el "qué es": 7 días, quema a leña a 1300 °C en horno de tiro invertido, sin experiencia previa).
- Fechas (inicio y fin) y duración (7 días, 6 noches). Si se puede llegar un día antes, se aclara.
- Lugar (zona rural a 7 km de Gaiman, Chubut).
- Cupos (grupo de 3 personas).
- Precio en ARS y en USD (personas del exterior).
- Forma de pago (50 % para confirmar y 50 % al llegar; cuotas a charlar).
- Qué incluye y qué no incluye.
- Condiciones (seguro de accidentes personales, actividad sujeta al clima, traer 4 piezas bizcochadas de hasta 15 cm).
- Fotos: hasta 6 por residencia, cargadas con un solo botón (una o varias a la vez). La primera es la principal. Con 1 foto se ve una grande; con 2 a 6 van apiladas en la columna derecha con miniaturas al costado, igual que la ficha de producto (`tienda/[slug]`). No hay deslizamiento automático ni collage. Los datos van a la izquierda.
- El formulario de inscripción de Google incrustado, con un enlace "abrir en una pestaña nueva" como respaldo.

**No se muestra en la página, queda solo en el formulario** (son datos de cada persona):
- Nombre, mail y redes.
- Dónde vive y a qué se dedica.
- Experiencia en cerámica.
- Condiciones de salud.
- Indicaciones de comida.
- Comentarios.

### Datos que carga la artesana en el panel
Título · descripción · fecha de inicio · fecha de fin · llegada anticipada (texto opcional) · lugar · cupos · precio ARS · precio USD · forma de pago (texto) · qué incluye · qué no incluye · condiciones · fotos · link del formulario · activa sí/no. Todo con versión en español e inglés.

### Cuestiones abiertas
- Mostrar o no los cupos disponibles: los actualiza la artesana a mano. Si no los mantiene al día, mejor mostrar solo "Grupo de 3 personas".
- Pago con Mercado Pago para la reserva (50 %): fuera de esta etapa. Hoy el pago se arregla por videollamada.

## 9. Tienda (decisión pendiente de la artesana)
En las tarjetas de la tienda se ve el título y las dimensiones. El stock solo se usa en la ficha de cada producto ("Agregar al carrito" o "Sin stock"). Falta decidir si se muestra también en la lista general. Si se agrega, va como una línea más de texto `text-sm`, igual que las dimensiones, y sin colores ni etiquetas llamativas.

## 10. Cosas a revisar
- El número entre paréntesis en algunos nombres (por ejemplo "Tetera Kyusu (T2)") es parte del título, no es el stock.
- En `residencias/page.tsx`, la primera carga usa `"/intros/ESP"` en vez del idioma actual.
