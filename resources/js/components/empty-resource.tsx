interface EmptyResourceProps {
    type: string;
}

export default function EmptyResource(props: EmptyResourceProps) {
    const { type } = props;
    return <div className="w-full p-4 text-center text-xl">No {type} found ☹️</div>;
}
