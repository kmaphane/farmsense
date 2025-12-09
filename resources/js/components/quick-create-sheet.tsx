import { BatchForm, PortioningForm, SlaughterForm } from '@/components/broiler';
import { CustomerForm } from '@/components/crm';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetBody,
    SheetContent,
    SheetHeader,
} from '@/components/ui/sheet';
import { Link, router } from '@inertiajs/react';
import {
    Bird,
    DollarSign,
    Loader2,
    Package,
    Plus,
    Scissors,
    Users,
} from 'lucide-react';
import { useEffect, useState } from 'react';

type QuickActionType =
    | 'batch'
    | 'slaughter'
    | 'portioning'
    | 'pricing'
    | 'customer';

interface QuickAction {
    type: QuickActionType;
    label: string;
    description: string;
    href?: string;
    icon: React.ElementType;
    color: string;
    group: 'production' | 'management';
    usesSheet?: boolean;
}

const quickActions: QuickAction[] = [
    {
        type: 'batch',
        label: 'New Batch',
        description: 'Start a new broiler batch',
        icon: Bird,
        color: 'text-green-600 dark:text-green-400',
        group: 'production',
        usesSheet: true,
    },
    {
        type: 'slaughter',
        label: 'Record Slaughter',
        description: 'Process birds for sale',
        icon: Scissors,
        color: 'text-red-600 dark:text-red-400',
        group: 'production',
        usesSheet: true,
    },
    {
        type: 'portioning',
        label: 'New Portioning',
        description: 'Cut into packs',
        icon: Package,
        color: 'text-purple-600 dark:text-purple-400',
        group: 'production',
        usesSheet: true,
    },
    {
        type: 'pricing',
        label: 'Update Pricing',
        description: 'Manage product prices',
        href: '/products/pricing',
        icon: DollarSign,
        color: 'text-blue-600 dark:text-blue-400',
        group: 'management',
        usesSheet: false,
    },
    {
        type: 'customer',
        label: 'New Customer',
        description: 'Add a customer',
        icon: Users,
        color: 'text-amber-600 dark:text-amber-400',
        group: 'management',
        usesSheet: true,
    },
];

interface SlaughterData {
    batches: Array<{
        id: number;
        name: string;
        batch_number: string;
        current_quantity: number;
        age_in_days: number;
    }>;
    products: Array<{
        id: number;
        name: string;
        type: string;
        yield_per_bird: number;
        units_per_package: number;
        package_unit: string | null;
    }>;
    discrepancyReasons: Array<{
        value: string;
        label: string;
    }>;
    suggestedDate: string;
}

interface PortioningData {
    wholeChickenStock: {
        id: number;
        name: string;
        quantity_on_hand: number;
    } | null;
    chickenPiecesProduct: {
        id: number;
        name: string;
        quantity_on_hand: number;
        units_per_package: number;
        package_unit: string | null;
    } | null;
    suggestedDate: string;
    defaultPackWeight: number;
}

interface BatchData {
    suppliers: Array<{
        id: number;
        name: string;
    }>;
    suggestedBatchNumber: string;
    suggestedStartDate: string;
}

interface CustomerData {
    customerTypes: Array<{
        value: string;
        label: string;
    }>;
}

export function QuickCreateSheet() {
    const [menuOpen, setMenuOpen] = useState(false);
    const [activeSheet, setActiveSheet] = useState<QuickActionType | null>(
        null,
    );
    const [sheetData, setSheetData] = useState<
        SlaughterData | PortioningData | BatchData | CustomerData | null
    >(null);
    const [loading, setLoading] = useState(false);

    const productionActions = quickActions.filter(
        (a) => a.group === 'production',
    );
    const managementActions = quickActions.filter(
        (a) => a.group === 'management',
    );

    // Fetch data when sheet opens
    useEffect(() => {
        if (!activeSheet) {
            setSheetData(null);
            return;
        }

        setLoading(true);

        const fetchData = async () => {
            try {
                let endpoint = '';
                if (activeSheet === 'slaughter') {
                    endpoint = '/api/slaughter/data';
                } else if (activeSheet === 'portioning') {
                    endpoint = '/api/portioning/data';
                } else if (activeSheet === 'batch') {
                    endpoint = '/api/batches/data';
                } else if (activeSheet === 'customer') {
                    endpoint = '/api/customers/data';
                }

                if (endpoint) {
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch data');
                    }

                    const data = await response.json();
                    setSheetData(data);
                }
            } catch (error) {
                console.error('Error fetching sheet data:', error);
                setActiveSheet(null);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [activeSheet]);

    const handleActionClick = (action: QuickAction) => {
        if (action.usesSheet) {
            setActiveSheet(action.type);
            setMenuOpen(false);
        } else if (action.href) {
            router.visit(action.href);
            setMenuOpen(false);
        }
    };

    const renderSheetContent = () => {
        if (loading) {
            return (
                <div className="flex items-center justify-center py-12">
                    <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
                </div>
            );
        }

        if (activeSheet === 'slaughter' && sheetData) {
            const data = sheetData as SlaughterData;
            return (
                <SlaughterForm
                    batches={data.batches}
                    discrepancyReasons={data.discrepancyReasons}
                    suggestedDate={data.suggestedDate}
                />
            );
        }

        if (activeSheet === 'portioning' && sheetData) {
            const data = sheetData as PortioningData;
            return (
                <PortioningForm
                    wholeChickenStock={data.wholeChickenStock}
                    chickenPiecesProduct={data.chickenPiecesProduct}
                    suggestedDate={data.suggestedDate}
                    defaultPackWeight={data.defaultPackWeight}
                    compact
                />
            );
        }

        if (activeSheet === 'batch' && sheetData) {
            const data = sheetData as BatchData;
            return (
                <BatchForm
                    suppliers={data.suppliers}
                    suggestedBatchNumber={data.suggestedBatchNumber}
                    suggestedStartDate={data.suggestedStartDate}
                    compact
                />
            );
        }

        if (activeSheet === 'customer' && sheetData) {
            const data = sheetData as CustomerData;
            return (
                <CustomerForm
                    customerTypes={data.customerTypes}
                    compact
                />
            );
        }

        return null;
    };

    const getSheetTitle = () => {
        const action = quickActions.find((a) => a.type === activeSheet);
        return action?.label || 'Quick Create';
    };

    const getSheetDescription = () => {
        const action = quickActions.find((a) => a.type === activeSheet);
        return action?.description || '';
    };

    const getSheetIcon = () => {
        const action = quickActions.find((a) => a.type === activeSheet);
        const Icon = action?.icon || Plus;
        return <Icon className="h-5 w-5" />;
    };

    return (
        <>
            {/* Quick Create Button */}
            <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8 rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200 hover:text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                onClick={() => setMenuOpen(true)}
                aria-label="Quick Create"
            >
                <Plus className="h-4 w-4" />
                <span className="sr-only">Quick Create</span>
            </Button>

            {/* Quick Create Menu Sheet */}
            <Sheet open={menuOpen} onOpenChange={setMenuOpen}>
                <SheetContent side="right" size="md" className="p-0">
                    <SheetHeader
                        title="Quick Create"
                        icon={<Plus className="size-6" />}
                        className="flex gap-2 border-b p-4"
                    />
                    <SheetBody className="divide-y">
                        <div className="p-4">
                            <div className="mb-2 text-xs font-normal text-muted-foreground">
                                Production
                            </div>
                            <ul className="flex flex-col gap-2">
                                {productionActions.map((action) => (
                                    <li key={action.type}>
                                        <button
                                            onClick={() =>
                                                handleActionClick(action)
                                            }
                                            className="flex w-full items-center gap-3 rounded p-2 hover:bg-primary/10"
                                        >
                                            <action.icon
                                                className={`h-5 w-5 ${action.color}`}
                                            />
                                            <div className="flex flex-col text-left">
                                                <span className="font-medium">
                                                    {action.label}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {action.description}
                                                </span>
                                            </div>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div className="p-4">
                            <div className="mb-2 text-xs font-normal text-muted-foreground">
                                Management
                            </div>
                            <ul className="flex flex-col gap-2">
                                {managementActions.map((action) => (
                                    <li key={action.type}>
                                        {action.href ? (
                                            <Link
                                                href={action.href}
                                                className="flex items-center gap-3 rounded p-2 hover:bg-primary/10"
                                                onClick={() => setMenuOpen(false)}
                                            >
                                                <action.icon
                                                    className={`h-5 w-5 ${action.color}`}
                                                />
                                                <div className="flex flex-col">
                                                    <span className="font-medium">
                                                        {action.label}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {action.description}
                                                    </span>
                                                </div>
                                            </Link>
                                        ) : (
                                            <button
                                                onClick={() =>
                                                    handleActionClick(action)
                                                }
                                                className="flex w-full items-center gap-3 rounded p-2 hover:bg-primary/10"
                                            >
                                                <action.icon
                                                    className={`h-5 w-5 ${action.color}`}
                                                />
                                                <div className="flex flex-col text-left">
                                                    <span className="font-medium">
                                                        {action.label}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {action.description}
                                                    </span>
                                                </div>
                                            </button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </SheetBody>
                </SheetContent>
            </Sheet>

            {/* Action Form Sheets */}
            <Sheet
                open={activeSheet !== null}
                onOpenChange={(open) => !open && setActiveSheet(null)}
            >
                <SheetContent
                    side="right"
                    size={activeSheet === 'slaughter' ? 'lg' : 'md'}
                    className="p-0"
                >
                    <SheetHeader
                        title={getSheetTitle()}
                        description={getSheetDescription()}
                        icon={getSheetIcon()}
                        className="flex gap-2 border-b p-4"
                    />
                    <SheetBody>{renderSheetContent()}</SheetBody>
                </SheetContent>
            </Sheet>
        </>
    );
}
