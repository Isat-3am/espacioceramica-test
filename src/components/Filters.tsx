"use client";
import Item from "@/components/item";
import { useState, useEffect, useRef } from "react";

interface category {
  id: number;
  title: string;
}

interface author {
  id: number;
  title: string;
}

const Filters = ({
  lan,
  category,
  categories,
  setCategory,
  setAuthor,
  author,
  authors,
  setSearch,
  search,
}: {
  lan: string;
  category: number;
  categories: category[];
  setCategory: (category: number) => void;
  setAuthor: (author: number) => void;
  author: number;
  authors: author[];
  setSearch: (search: string) => void;
  search: string;
}) => {
  const [open, setOpen] = useState(true);
  const lastScrollY = useRef(0);

  // Set initial open state based on screen width
  useEffect(() => {
    const handleResize = () => {
      setOpen(window.innerWidth >= 900);
    };

    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  // Close filters on scroll (ONLY on mobile)
  useEffect(() => {
    const handleScroll = () => {
      const isMobile = window.innerWidth < 900;
      const currentScroll = window.scrollY;

      if (isMobile && currentScroll > lastScrollY.current) {
        setOpen(false);
      }

      lastScrollY.current = currentScroll;
    };

    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const handleOpen = () => setOpen(!open);

  if (open) {
    return (
      <div className="w-full sticky top-12 flex flex-col mb-4 lg:mb-6 bg-[#f6f6f7] py-2 z-10">
        <div className="flex items-center gap-x-4 lg:gap-x-8 flex-wrap">
          <Item
            title={lan === "es" ? "Ver Todo" : "All"}
            active={category === 0 ? true : false}
            action={() => setCategory(0)}
          />
          {categories.map((item: category) => {
            return (
              <Item
                key={item.id}
                title={item.title}
                active={category === item.id ? true : false}
                action={() => setCategory(item.id)}
              />
            );
          })}
        </div>
        <div className="flex items-center gap-x-4 lg:gap-x-8 flex-wrap my-2 lg:my-0">
          {authors.map((item: author) => {
            return (
              <Item
                key={item.id}
                title={item.title}
                active={author === item.id ? true : false}
                action={() => setAuthor(item.id)}
              />
            );
          })}
        </div>
        <div className="flex gap-x-2 text-sm font-[--lastik-regular]">
          <div>
            (
            <input
              type="text"
              value={search}
              className="border-b border-black lg:w-64 px-2 focus:outline-none"
              onChange={(e) => setSearch(e.target.value)}
            />
            )
          </div>
          <button className="cursor-pointer hover:text-white hover:bg-black">
            {lan === "es" ? "BUSCAR" : "SEARCH"}
          </button>
        </div>
      </div>
    );
  } else {
    return (
      <div className="w-full sticky top-12 flex flex-col mb-4 bg-[#f6f6f7] py-2 z-10">
        <div className="flex gap-x-2 text-sm font-[--lastik-regular]">
          <div>
            (
            <input
              type="text"
              className="border-b border-black lg:w-64 px-2 focus:outline-none"
            />
            )
          </div>
          <button
            className="cursor-pointer hover:text-white hover:bg-black uppercase"
            onClick={handleOpen}
          >
            {lan === "es" ? "Filtros" : "Filters"}
          </button>
        </div>
      </div>
    );
  }
};

export default Filters;
