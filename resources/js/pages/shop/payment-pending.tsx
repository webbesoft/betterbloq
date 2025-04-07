import { Loader } from "lucide-react";
import { useEffect } from "react";

export default function PaymentPending(props: { url: string }) {
    console.log(props);
    const { url } = props;

    useEffect(() => {
        if (url) {
            window.location.href = url;
        }
    }, [url]);

    return (
        <div className="h-screen flex items-center justify-center">
            <Loader />
        </div>
    )
}
