export default function AppLogo() {
    return (
        <>
            <img
                src="/storage/logo/farm-sense-logo.png"
                alt="FarmSense Logo"
                className="size-8 object-contain"
            />
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    FarmSense
                </span>
            </div>
        </>
    );
}
