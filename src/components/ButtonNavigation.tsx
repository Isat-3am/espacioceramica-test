import { Link } from "@/i18n/navigation";

const button = ({ label, href }: { label: string; href: string }) => {
  return (
    <Link
      href={href}
      className="bg-black text-white p-3 uppercase text-sm border hover:bg-white hover:text-black active:opacity-50 transition-all flex justify-center items-center font-[--lastik-regular]"
    >
      {label}
    </Link>
  );
};

export default button;
