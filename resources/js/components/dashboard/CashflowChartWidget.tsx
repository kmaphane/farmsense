import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Calendar as CalendarIcon,
    Filter,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';

// Date helper functions to replace date-fns
const formatDate = (date: Date): string => {
    return date.toISOString().split('T')[0]; // yyyy-MM-dd
};

const formatDateLabel = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' }); // MMM dd
};

const subDays = (date: Date, days: number): Date => {
    const result = new Date(date);
    result.setDate(result.getDate() - days);
    return result;
};

const startOfMonth = (date: Date): Date => {
    return new Date(date.getFullYear(), date.getMonth(), 1);
};

const endOfMonth = (date: Date): Date => {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0);
};

const subMonths = (date: Date, months: number): Date => {
    const result = new Date(date);
    result.setMonth(result.getMonth() - months);
    return result;
};

interface DailyCashflow {
    date: string;
    date_label: string;
    cash_in: number;
    cash_out: number;
    net: number;
}

interface CashflowTotals {
    cash_in: number;
    cash_out: number;
    net: number;
}

interface CashflowPeriod {
    start: string;
    end: string;
    days: number;
}

interface Transaction {
    id: string;
    date: string;
    description: string;
    category: string;
    amount: number;
    type: 'in' | 'out';
}

interface CashflowHistory {
    daily: DailyCashflow[];
    totals: CashflowTotals;
    period: CashflowPeriod;
    transactions?: Transaction[];
}

interface Props {
    history: CashflowHistory;
}

function formatCurrency(cents: number): string {
    const absValue = Math.abs(cents) / 100;
    return `P${absValue.toLocaleString('en-BW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function CashflowChartWidget({ history: initialHistory }: Props) {
    const [history, setHistory] = useState<CashflowHistory>(initialHistory);
    const [isRandomMode, setIsRandomMode] = useState(false);
    const [isCustomDialogOpen, setIsCustomDialogOpen] = useState(false);
    const [customStart, setCustomStart] = useState('');
    const [customEnd, setCustomEnd] = useState('');

    // Pagination State
    const [currentPage, setCurrentPage] = useState(0);
    const ITEMS_PER_PAGE = 5;

    // Update history when props change (if not in random mode) and reset pagination
    if (initialHistory !== history && !isRandomMode) {
        setHistory(initialHistory);
        setCurrentPage(0); // Reset to first page on data refresh
    }

    const maxValue = Math.max(
        ...history.daily.map((d) => Math.max(d.cash_in, d.cash_out)),
    );

    const getBarHeight = (value: number) => {
        if (maxValue === 0) return 0;
        return (value / maxValue) * 100;
    };

    const handleFilterChange = (filter: string) => {
        const today = new Date();
        let start: Date | null = null;
        let end: Date = today;

        if (filter === 'random') {
            generateRandomData();
            return;
        }

        setIsRandomMode(false);

        switch (filter) {
            case '7days':
                start = subDays(today, 6);
                break;
            case '30days':
                start = subDays(today, 29);
                break;
            case 'this_month':
                start = startOfMonth(today);
                end = endOfMonth(today);
                break;
            case 'last_month':
                start = startOfMonth(subMonths(today, 1));
                end = endOfMonth(subMonths(today, 1));
                break;
            case 'custom':
                setIsCustomDialogOpen(true);
                return;
        }

        if (start) {
            applyDateFilter(start, end);
        }
    };

    const applyDateFilter = (start: Date, end: Date) => {
        router.visit(window.location.pathname, {
            data: {
                start_date: formatDate(start),
                end_date: formatDate(end),
            },
            preserveScroll: true,
            preserveState: true,
            only: ['cashflowHistory'],
            onSuccess: (page) => {
                 setIsRandomMode(false);
            },
        });
    };

    const handleCustomFilterSubmit = () => {
        if (customStart && customEnd) {
            applyDateFilter(new Date(customStart), new Date(customEnd));
            setIsCustomDialogOpen(false);
        }
    };

    const generateRandomData = () => {
        setIsRandomMode(true);
        const days = 7;
        const newDaily: DailyCashflow[] = [];
        const newTransactions: Transaction[] = []; // Mock transactions
        let totalIn = 0;
        let totalOut = 0;

        for (let i = 0; i < days; i++) {
            const cashIn = Math.floor(Math.random() * 5000000); // 0 - 50,000.00
            const cashOut = Math.floor(Math.random() * 3000000); // 0 - 30,000.00
            const date = subDays(new Date(), days - 1 - i);
            const dateStr = formatDate(date);

            totalIn += cashIn;
            totalOut += cashOut;

            newDaily.push({
                date: dateStr,
                date_label: formatDateLabel(date),
                cash_in: cashIn,
                cash_out: cashOut,
                net: cashIn - cashOut,
            });

            // Add mock transactions
             if (cashIn > 0) {
                newTransactions.push({
                    id: `random_in_${i}`,
                    date: dateStr,
                    description: 'Direct Sale (Random)',
                    category: 'Product Sale',
                    amount: cashIn,
                    type: 'in',
                });
            }
            if (cashOut > 0) {
                newTransactions.push({
                    id: `random_out_${i}`,
                    date: dateStr,
                    description: 'Farm Supplies (Random)',
                    category: 'General',
                    amount: cashOut,
                    type: 'out',
                });
            }
        }

        setHistory({
            daily: newDaily,
            totals: {
                cash_in: totalIn,
                cash_out: totalOut,
                net: totalIn - totalOut,
            },
            period: {
                start: newDaily[0].date_label,
                end: newDaily[newDaily.length - 1].date_label,
                days: days,
            },
            transactions: newTransactions.sort((a, b) => b.date.localeCompare(a.date)),
        });
        setCurrentPage(0);
    };

    // Pagination Logic
    const transactions = history.transactions || [];
    const totalPages = Math.ceil(transactions.length / ITEMS_PER_PAGE);
    const paginatedTransactions = transactions.slice(
        currentPage * ITEMS_PER_PAGE,
        (currentPage + 1) * ITEMS_PER_PAGE
    );

    const handleNextPage = () => {
        if (currentPage < totalPages - 1) {
            setCurrentPage(currentPage + 1);
        }
    };

    const handlePrevPage = () => {
        if (currentPage > 0) {
            setCurrentPage(currentPage - 1);
        }
    };

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <div className="space-y-1">
                    <CardTitle>Cash Flow Tracker</CardTitle>
                    <CardDescription>
                        {isRandomMode ? 'Random Data (Troubleshooting)' : (
                            <>
                                Last {history.period.days} days ({history.period.start} - {' '}
                                {history.period.end})
                            </>
                        )}
                    </CardDescription>
                </div>

                <div className="flex items-center gap-2">
                     <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" size="sm" className="h-8 gap-1">
                                <Filter className="h-3.5 w-3.5" />
                                <span className="hidden sm:inline-block">Filter</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => handleFilterChange('7days')}>
                                Last 7 Days
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleFilterChange('30days')}>
                                Last 30 Days
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleFilterChange('this_month')}>
                                This Month
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleFilterChange('last_month')}>
                                Last Month
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleFilterChange('custom')}>
                                Custom Range...
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleFilterChange('random')} className="text-orange-600 focus:text-orange-700">
                                Random Data
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardHeader>
            <CardContent>
                 <Dialog open={isCustomDialogOpen} onOpenChange={setIsCustomDialogOpen}>
                    <DialogContent className="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Select Date Range</DialogTitle>
                            <DialogDescription>
                                Choose the start and end dates for the cash flow report.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="grid gap-4 py-4">
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="start-date" className="text-right">
                                    Start
                                </Label>
                                <Input
                                    id="start-date"
                                    type="date"
                                    className="col-span-3"
                                    value={customStart}
                                    onChange={(e) => setCustomStart(e.target.value)}
                                />
                            </div>
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="end-date" className="text-right">
                                    End
                                </Label>
                                <Input
                                    id="end-date"
                                    type="date"
                                    className="col-span-3"
                                    value={customEnd}
                                    onChange={(e) => setCustomEnd(e.target.value)}
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" onClick={handleCustomFilterSubmit}>
                                Apply Filter
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* Summary Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-3">
                    <div className="rounded-lg border bg-gradient-to-br from-green-50 to-green-100 p-3 dark:from-green-950 dark:to-green-900 border-green-200 dark:border-green-800">
                        <div className="flex items-center gap-2">
                            <ArrowDown className="h-4 w-4 text-green-600 dark:text-green-400" />
                            <p className="text-xs font-medium text-green-700 dark:text-green-300">
                                Cash In
                            </p>
                        </div>
                        <p className="mt-1 text-lg font-bold text-green-800 dark:text-green-200">
                            {formatCurrency(history.totals.cash_in)}
                        </p>
                    </div>

                    <div className="rounded-lg border bg-gradient-to-br from-red-50 to-red-100 p-3 dark:from-red-950 dark:to-red-900 border-red-200 dark:border-red-800">
                        <div className="flex items-center gap-2">
                            <ArrowUp className="h-4 w-4 text-red-600 dark:text-red-400" />
                            <p className="text-xs font-medium text-red-700 dark:text-red-300">
                                Cash Out
                            </p>
                        </div>
                        <p className="mt-1 text-lg font-bold text-red-800 dark:text-red-200">
                            {formatCurrency(history.totals.cash_out)}
                        </p>
                    </div>

                    <div
                        className={`rounded-lg border p-3 ${
                            history.totals.net >= 0
                                ? 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 border-blue-200 dark:border-blue-800'
                                : 'bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950 dark:to-orange-900 border-orange-200 dark:border-orange-800'
                        }`}
                    >
                        <div className="flex items-center gap-2">
                            <TrendingUp
                                className={`h-4 w-4 ${
                                    history.totals.net >= 0
                                        ? 'text-blue-600 dark:text-blue-400'
                                        : 'text-orange-600 dark:text-orange-400'
                                }`}
                            />
                            <p
                                className={`text-xs font-medium ${
                                    history.totals.net >= 0
                                        ? 'text-blue-700 dark:text-blue-300'
                                        : 'text-orange-700 dark:text-orange-300'
                                }`}
                            >
                                Net Flow
                            </p>
                        </div>
                        <p
                            className={`mt-1 text-lg font-bold ${
                                history.totals.net >= 0
                                    ? 'text-blue-800 dark:text-blue-200'
                                    : 'text-orange-800 dark:text-orange-200'
                            }`}
                        >
                            {history.totals.net >= 0 ? '+' : ''}
                            {formatCurrency(history.totals.net)}
                        </p>
                    </div>
                </div>

                {/* Bar Chart */}
                <div className="space-y-4 mb-8">
                    <div className="flex h-48 items-end justify-between gap-2">
                        {history.daily.map((day) => (
                            <div
                                key={day.date}
                                className="flex flex-1 flex-col items-center gap-1"
                            >
                                {/* Bars */}
                                <div className="flex w-full items-end justify-center gap-0.5">
                                    {/* Cash In Bar */}
                                    <div
                                        className="w-1/2 rounded-t bg-green-500 dark:bg-green-600 transition-all duration-500 ease-in-out"
                                        style={{
                                            height: `${getBarHeight(day.cash_in)}%`,
                                            minHeight: day.cash_in > 0 ? '4px' : '0',
                                        }}
                                        title={`Cash In: ${formatCurrency(day.cash_in)}`}
                                    />
                                    {/* Cash Out Bar */}
                                    <div
                                        className="w-1/2 rounded-t bg-red-500 dark:bg-red-600 transition-all duration-500 ease-in-out"
                                        style={{
                                            height: `${getBarHeight(day.cash_out)}%`,
                                            minHeight: day.cash_out > 0 ? '4px' : '0',
                                        }}
                                        title={`Cash Out: ${formatCurrency(day.cash_out)}`}
                                    />
                                </div>

                                {/* Date Label */}
                                <p className="text-xs text-muted-foreground whitespace-nowrap overflow-hidden text-ellipsis max-w-[40px] text-center">
                                    {day.date_label}
                                </p>
                            </div>
                        ))}
                    </div>

                    {/* Legend */}
                    <div className="flex items-center justify-center gap-4 text-xs">
                        <div className="flex items-center gap-1.5">
                            <div className="h-3 w-3 rounded bg-green-500" />
                            <span>Cash In</span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <div className="h-3 w-3 rounded bg-red-500" />
                            <span>Cash Out</span>
                        </div>
                    </div>
                </div>

                {/* Transaction Table */}
                {transactions.length > 0 && (
                    <div className="rounded-md border">
                        <div className="p-4">
                            <h3 className="text-sm font-semibold mb-3">Recent Transactions</h3>
                             <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="text-xs text-muted-foreground uppercase bg-muted/50">
                                        <tr>
                                            <th className="px-3 py-2 rounded-tl-md">Date</th>
                                            <th className="px-3 py-2">Attributes</th>
                                            <th className="px-3 py-2">Description</th>
                                            <th className="px-3 py-2 text-right rounded-tr-md">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {paginatedTransactions.map((tx) => (
                                            <tr key={tx.id} className="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                                <td className="px-3 py-2 font-medium whitespace-nowrap max-w-[100px] overflow-hidden text-ellipsis" title={tx.date}>
                                                    {tx.date}
                                                </td>
                                                <td className="px-3 py-2">
                                                    <span className={`inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border shadow-sm ${
                                                        tx.type === 'in'
                                                            ? 'bg-gradient-to-br from-green-50 to-green-100 text-green-700 dark:from-green-950 dark:to-green-900 dark:text-green-300 border-green-200 dark:border-green-800'
                                                            : 'bg-gradient-to-br from-red-50 to-red-100 text-red-700 dark:from-red-950 dark:to-red-900 dark:text-red-300 border-red-200 dark:border-red-800'
                                                    }`}>
                                                        {tx.type === 'in' ? 'Cash In' : 'Cash Out'}
                                                    </span>
                                                </td>
                                                <td className="px-3 py-2 max-w-[150px] truncate" title={tx.description}>
                                                   <div className="flex flex-col">
                                                       <span>{tx.description}</span>
                                                       <span className="text-[10px] text-muted-foreground">{tx.category}</span>
                                                   </div>
                                                </td>
                                                <td className={`px-3 py-2 text-right font-bold ${
                                                    tx.type === 'in' ? 'text-green-600' : 'text-red-600'
                                                }`}>
                                                    {tx.type === 'in' ? '+' : '-'}{formatCurrency(tx.amount)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination Controls */}
                            {totalPages > 1 && (
                                <div className="flex items-center justify-between mt-3 text-xs">
                                    <div className="text-muted-foreground">
                                        Page {currentPage + 1} of {totalPages}
                                    </div>
                                    <div className="flex gap-1">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="h-7 px-2"
                                            onClick={handlePrevPage}
                                            disabled={currentPage === 0}
                                        >
                                            Previous
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="h-7 px-2"
                                            onClick={handleNextPage}
                                            disabled={currentPage >= totalPages - 1}
                                        >
                                            Next
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
