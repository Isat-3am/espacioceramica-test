const info = ({
  title,
  description,
  text,
}: {
  title: string;
  description: string;
  text: string;
}) => {
  return (
    <div className="flex gap-x-4 mb-2 pb-6 text-sm leading-4">
      <div className="w-1/3">{title}</div>
      <div className="w-2/3 font-[--lastik-regular] whitespace-break-spaces flex flex-col gap-y-2">
        <span>{description}</span>
        <span>{text}</span>
      </div>
    </div>
  );
};

export default info;
