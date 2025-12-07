import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    const { className, ...rest } = props;
    return (
        <img
            src="/storage/logo/farm-sense-logo.png"
            alt="FarmSense Logo"
            className={className}
            {...rest}
        />
    );
}
