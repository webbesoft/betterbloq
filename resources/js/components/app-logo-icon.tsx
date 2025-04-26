import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <img
            src="/images/logo.png"
            alt="betterbloq logo"
            className="h-10 w-auto bg-white"
        />
    );
}
