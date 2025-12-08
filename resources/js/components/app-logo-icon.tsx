import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    const { className, ...rest } = props;
    return (
        <span className="inline-flex items-center gap-2">
            <img
                src="/storage/logo/farm-sense-logo.png"
                alt="FarmSense Logo"
                className={className ?? "size-8 object-contain"}
                {...rest}
            />
            <span className="font-semibold text-sm leading-tight truncate">FarmSense</span>
        </span>
    );
}
