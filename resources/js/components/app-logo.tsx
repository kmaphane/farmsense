export default function AppLogo() {
    return (
        <span className="inline-flex items-center gap-2">
            <img
                src="/storage/logo/farm-sense-logo.png"
                alt="FarmSense Logo"
                className="size-8 object-contain"
            />
            <span className="font-semibold text-sm leading-tight truncate">FarmSense</span>
        </span>
    );
}
