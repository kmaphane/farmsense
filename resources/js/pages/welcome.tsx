import { dashboard, login } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Landing() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="FarmSense – Farm Management" />
            <div className="min-h-screen bg-background text-foreground">
                {/* Top Bar */}
                <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                    <div className="flex items-center gap-3">
                        <img
                            src="/storage/logo/farm-sense-logo.png"
                            alt="FarmSense"
                            className="size-10 rounded-md object-cover"
                            onError={(e) => {
                                const el = e.currentTarget;
                                el.onerror = null;
                                el.src = '/storage/brand/farm-sense-logo.png';
                            }}
                        />
                        <div className="text-xl font-semibold tracking-wide">
                            <span className="text-primary">Farm</span>
                            <span className="text-foreground">Sense</span>
                        </div>
                    </div>
                    <div className="hidden items-center gap-6 md:flex">
                        <a
                            href="#about"
                            className="text-sm font-medium hover:text-primary"
                        >
                            Who we are
                        </a>
                        <a
                            href="#features"
                            className="text-sm font-medium hover:text-primary"
                        >
                            Features
                        </a>
                        <a
                            href="#gallery"
                            className="text-sm font-medium hover:text-primary"
                        >
                            Our gallery
                        </a>
                        <a
                            href="#contact"
                            className="text-sm font-medium hover:text-primary"
                        >
                            Contact us
                        </a>
                    </div>
                    <div className="flex items-center gap-3">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <Link
                                href={login()}
                                className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
                            >
                                Log in
                            </Link>
                        )}
                    </div>
                </nav>

                {/* Hero */}
                <header className="relative mx-auto mt-2 max-w-7xl overflow-hidden rounded-2xl">
                    <div className="relative bg-gradient-to-br from-primary/10 via-accent/5 to-background">
                        <div className="relative mx-auto flex max-w-4xl flex-col items-center px-6 py-24 text-center sm:py-28 lg:py-36">
                            {/* Agricultural icon */}
                            <svg
                                aria-hidden="true"
                                className="mb-6 h-12 w-12 text-primary"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.5"
                            >
                                <path
                                    d="M12 2v20M8 6l4-4 4 4M8 18l4 4 4-4M6 12h12"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                            <div className="mb-3 text-sm font-semibold tracking-wider text-primary uppercase">
                                Farm Management
                            </div>
                            <h1 className="display-serif text-4xl text-foreground sm:text-5xl lg:text-6xl">
                                FarmSense
                            </h1>
                            <p className="mt-4 max-w-2xl text-lg text-balance text-muted-foreground">
                                Run your farm with clarity—records, pricing,
                                suppliers and production dashboards in a modern,
                                intuitive interface.
                            </p>
                            <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                                <Link
                                    href={login()}
                                    className="rounded-md bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground hover:opacity-90"
                                >
                                    Get Started
                                </Link>
                                <a
                                    href="#about"
                                    className="text-sm font-semibold hover:text-primary"
                                >
                                    Learn more →
                                </a>
                            </div>
                            {/* Watermark stamp */}
                            <div className="pointer-events-none absolute -right-6 -bottom-6 hidden opacity-20 md:block">
                                <svg
                                    className="h-32 w-32"
                                    viewBox="0 0 120 120"
                                    fill="none"
                                >
                                    <circle
                                        cx="60"
                                        cy="60"
                                        r="56"
                                        stroke="currentColor"
                                        className="text-border"
                                        strokeWidth="2"
                                    />
                                    <text
                                        x="60"
                                        y="45"
                                        textAnchor="middle"
                                        className="fill-current text-muted-foreground"
                                        fontSize="12"
                                        fontWeight="600"
                                    >
                                        FarmSense
                                    </text>
                                    <text
                                        x="60"
                                        y="60"
                                        textAnchor="middle"
                                        className="fill-current text-muted-foreground"
                                        fontSize="8"
                                    >
                                        FARM SYSTEMS
                                    </text>
                                    <text
                                        x="60"
                                        y="75"
                                        textAnchor="middle"
                                        className="fill-current text-muted-foreground"
                                        fontSize="8"
                                    >
                                        EST. 2025
                                    </text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </header>

                {/* About */}
                <section
                    id="about"
                    className="mx-auto max-w-7xl px-6 py-16 lg:px-8"
                >
                    <div className="grid grid-cols-1 items-start gap-10 md:grid-cols-2">
                        <div>
                            <div className="mb-3 text-sm font-semibold tracking-wider text-primary uppercase">
                                About Us
                            </div>
                            <h2 className="display-serif mt-2 text-3xl text-foreground">
                                We Understand Farming Better
                            </h2>
                        </div>
                        <div className="space-y-4 text-base leading-7 text-muted-foreground">
                            <p>
                                Inspired by the field—clean interfaces, emerald
                                green accents, and intuitive design—our platform
                                stays consistent from landing page to dashboard
                                so teams feel at home.
                            </p>
                            <p>
                                Modules for suppliers, pricing history, and
                                production metrics come standard. Bring your
                                data; we'll keep it organized and searchable.
                            </p>
                            <div className="mt-6">
                                <Link
                                    href={login()}
                                    className="rounded-md border border-border bg-background px-4 py-2 text-sm font-medium hover:bg-muted"
                                >
                                    Get started
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Features */}
                <section
                    id="features"
                    className="mx-auto max-w-7xl px-6 py-16 lg:px-8"
                >
                    <div className="mb-12 text-center">
                        <div className="mb-3 text-sm font-semibold tracking-wider text-primary uppercase">
                            Features
                        </div>
                        <h2 className="display-serif text-3xl text-foreground">
                            Everything You Need
                        </h2>
                        <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground">
                            Powerful tools designed specifically for modern farm
                            management
                        </p>
                    </div>
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {/* Feature 1 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                Batch Management
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Track batches from start to finish with daily
                                logs, mortality tracking, and performance
                                metrics.
                            </p>
                        </div>

                        {/* Feature 2 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                Financial Tracking
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Monitor expenses, pricing, and profitability
                                with detailed reports and analytics.
                            </p>
                        </div>

                        {/* Feature 3 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                Inventory Management
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Keep track of feed, medicine, and supplies
                                across multiple warehouses with automated
                                alerts.
                            </p>
                        </div>

                        {/* Feature 4 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                CRM
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Manage customers and suppliers with contact
                                information, transaction history, and
                                performance ratings.
                            </p>
                        </div>

                        {/* Feature 5 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                Analytics
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Real-time dashboards with FCR, EPEF
                                calculations, and production insights to
                                optimize operations.
                            </p>
                        </div>

                        {/* Feature 6 */}
                        <div className="rounded-lg border border-border bg-card p-6">
                            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-card-foreground">
                                Multi-Tenancy
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Complete data isolation between farms with
                                role-based access control and team management.
                            </p>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                    <div className="rounded-2xl bg-gradient-to-br from-primary/10 via-accent/5 to-background p-12 text-center">
                        <h2 className="display-serif text-3xl text-foreground">
                            Ready to Get Started?
                        </h2>
                        <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground">
                            Join modern farms using FarmSense to streamline
                            operations and boost productivity
                        </p>
                        <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                            <Link
                                href={login()}
                                className="rounded-md bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground hover:opacity-90"
                            >
                                Get Started
                            </Link>
                            <a
                                href="#contact"
                                className="text-sm font-semibold hover:text-primary"
                            >
                                Contact Us
                            </a>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="mx-auto max-w-7xl border-t border-border px-6 py-8 lg:px-8">
                    <div className="flex flex-col items-center justify-between gap-4 md:flex-row">
                        <div className="flex items-center gap-3">
                            <img
                                src="/storage/logo/farm-sense-logo.png"
                                alt="FarmSense"
                                className="size-8 rounded-md object-cover"
                                onError={(e) => {
                                    const el = e.currentTarget;
                                    el.onerror = null;
                                    el.src =
                                        '/storage/brand/farm-sense-logo.png';
                                }}
                            />
                            <div className="text-sm font-medium">
                                <span className="text-primary">Farm</span>
                                <span className="text-foreground">Sense</span>
                            </div>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            &copy; {new Date().getFullYear()} FarmSense. All
                            rights reserved.
                        </p>
                        <div className="flex items-center gap-4">
                            <a
                                href="#about"
                                className="text-sm text-muted-foreground hover:text-primary"
                            >
                                About
                            </a>
                            <a
                                href="#features"
                                className="text-sm text-muted-foreground hover:text-primary"
                            >
                                Features
                            </a>
                            <a
                                href="#contact"
                                className="text-sm text-muted-foreground hover:text-primary"
                            >
                                Contact
                            </a>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
