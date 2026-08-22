import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon({
    alt = 'SOCOPA Security Agency, Inc.',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return <img src="/images/logo.png" alt={alt} {...props} />;
}
