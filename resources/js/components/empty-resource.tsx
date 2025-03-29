interface EmptyResourceProps {
    type: string;
}

export default function EmptyResource (props: EmptyResourceProps) {
    const { type } = props;
    return (
        <div>
            No {type} found ☹️
        </div>
    )
}