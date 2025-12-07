import { AlertTriangle, Calendar, ChevronLeft, ChevronRight, CloudRain, Droplets, Edit, Gauge, Thermometer, Utensils, Wind } from 'lucide-react';
import * as React from 'react';

export interface DailyLogData {
    id: number;
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
    ammonia_ppm: number | null;
    rainfall_mm: number | null;
    isEditable: boolean;
}

interface DailyLogCalendarProps {
    logs: DailyLogData[];
    batchStartDate: string;
    batchEndDate?: string | null;
    batchAgeInDays: number;
    onEditLog: (log: DailyLogData) => void;
}

type ViewMode = 'today' | 'week' | 'all';

interface DayLogSummary {
    hasLog: boolean;
    log?: DailyLogData;
    isFuture: boolean;
    dayNumber: number;
    weekNumber: number;
}

export function DailyLogCalendar({ logs, batchStartDate, batchEndDate, batchAgeInDays, onEditLog }: DailyLogCalendarProps) {
    // Memoize date objects to prevent unnecessary recalculations
    const today = React.useMemo(() => new Date(), []);
    const batchStart = React.useMemo(() => new Date(batchStartDate), [batchStartDate]);
    const batchEnd = React.useMemo(() => batchEndDate ? new Date(batchEndDate) : null, [batchEndDate]);
    
    // Calculate current week of the batch
    const currentWeek = Math.ceil(batchAgeInDays / 7) || 1;
    
    // View mode state - default to current week
    const [viewMode, setViewMode] = React.useState<ViewMode>('week');
    const [selectedWeek, setSelectedWeek] = React.useState(currentWeek);

    // Create a map of logs by date for quick lookup
    const logsByDate = React.useMemo(() => {
        const map = new Map<string, DailyLogData>();
        logs.forEach(log => {
            const dateKey = log.log_date.split('T')[0];
            map.set(dateKey, log);
        });
        return map;
    }, [logs]);

    // Calculate all batch days
    const allBatchDays = React.useMemo(() => {
        const days: { date: Date; dayNumber: number; weekNumber: number }[] = [];
        const endDate = batchEnd || (today > batchStart ? today : batchStart);
        const currentDate = new Date(batchStart);
        let dayNumber = 1;

        while (currentDate <= endDate) {
            days.push({
                date: new Date(currentDate),
                dayNumber,
                weekNumber: Math.ceil(dayNumber / 7),
            });
            currentDate.setDate(currentDate.getDate() + 1);
            dayNumber++;
        }

        return days;
    }, [batchStart, batchEnd, today]);

    // Get total weeks
    const totalWeeks = Math.ceil(allBatchDays.length / 7) || 1;

    // Filter days based on view mode
    const filteredDays = React.useMemo(() => {
        switch (viewMode) {
            case 'today':
                return allBatchDays.filter(d => 
                    d.date.toDateString() === today.toDateString()
                );
            case 'week':
                return allBatchDays.filter(d => d.weekNumber === selectedWeek);
            case 'all':
            default:
                return allBatchDays;
        }
    }, [viewMode, selectedWeek, allBatchDays, today]);

    const getDayInfo = (date: Date, dayNumber: number, weekNumber: number): DayLogSummary => {
        const dateKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        const log = logsByDate.get(dateKey);

        return {
            hasLog: !!log,
            log,
            isFuture: date > today,
            dayNumber,
            weekNumber,
        };
    };

    const goToPreviousWeek = () => {
        if (selectedWeek > 1) {
            setSelectedWeek(prev => prev - 1);
        }
    };

    const goToNextWeek = () => {
        if (selectedWeek < totalWeeks) {
            setSelectedWeek(prev => prev + 1);
        }
    };

    return (
        <div className="w-full space-y-4">
            {/* Header with filters */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                {/* View Mode Tabs */}
                <div className="flex items-center gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button
                        type="button"
                        onClick={() => setViewMode('today')}
                        className={`px-3 py-1.5 text-xs font-medium rounded-md transition-colors ${
                            viewMode === 'today'
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        }`}
                    >
                        Today
                    </button>
                    <button
                        type="button"
                        onClick={() => {
                            setViewMode('week');
                            setSelectedWeek(currentWeek);
                        }}
                        className={`px-3 py-1.5 text-xs font-medium rounded-md transition-colors ${
                            viewMode === 'week'
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        }`}
                    >
                        Week
                    </button>
                    <button
                        type="button"
                        onClick={() => setViewMode('all')}
                        className={`px-3 py-1.5 text-xs font-medium rounded-md transition-colors ${
                            viewMode === 'all'
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        }`}
                    >
                        All
                    </button>
                </div>

                {/* Week Navigator - only show when in week view */}
                {viewMode === 'week' && (
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={goToPreviousWeek}
                            disabled={selectedWeek <= 1}
                            className="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </button>
                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[100px] text-center">
                            Week {selectedWeek} of {totalWeeks}
                        </span>
                        <button
                            type="button"
                            onClick={goToNextWeek}
                            disabled={selectedWeek >= totalWeeks}
                            className="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </button>
                    </div>
                )}

                {/* Summary info */}
                <div className="text-xs text-gray-500 dark:text-gray-400">
                    {filteredDays.length} {filteredDays.length === 1 ? 'day' : 'days'}
                    {viewMode === 'all' && ` • ${totalWeeks} weeks`}
                </div>
            </div>

            {/* Days Display */}
            {filteredDays.length === 0 ? (
                <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                    <Calendar className="h-8 w-8 mx-auto mb-2 opacity-50" />
                    <p className="text-sm">No logs for this period</p>
                </div>
            ) : viewMode === 'today' ? (
                // Single day expanded view
                <div className="space-y-3">
                    {filteredDays.map(({ date, dayNumber, weekNumber }) => {
                        const dayInfo = getDayInfo(date, dayNumber, weekNumber);
                        return (
                            <DayCardExpanded
                                key={dayNumber}
                                date={date}
                                dayNumber={dayNumber}
                                weekNumber={weekNumber}
                                dayInfo={dayInfo}
                                onEditLog={onEditLog}
                            />
                        );
                    })}
                </div>
            ) : (
                // Grid view for week/all
                <div className={`grid gap-2 ${
                    viewMode === 'week' 
                        ? 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7' 
                        : 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7'
                }`}>
                    {filteredDays.map(({ date, dayNumber, weekNumber }) => {
                        const dayInfo = getDayInfo(date, dayNumber, weekNumber);
                        const isToday = date.toDateString() === today.toDateString();

                        return (
                            <DayCard
                                key={dayNumber}
                                date={date}
                                dayNumber={dayNumber}
                                weekNumber={weekNumber}
                                dayInfo={dayInfo}
                                isToday={isToday}
                                onEditLog={onEditLog}
                                compact={viewMode === 'all'}
                            />
                        );
                    })}
                </div>
            )}

            {/* Legend */}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-2 pt-4 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                <div className="flex items-center gap-1.5">
                    <div className="w-2.5 h-2.5 rounded-full bg-green-500" />
                    <span>Logged</span>
                </div>
                <div className="flex items-center gap-1.5">
                    <div className="w-2.5 h-2.5 rounded-full bg-amber-500" />
                    <span>High mortality</span>
                </div>
                <div className="flex items-center gap-1.5">
                    <CloudRain className="w-3 h-3 text-blue-500" />
                    <span>Rain</span>
                </div>
            </div>
        </div>
    );
}

// Compact card for grid view
interface DayCardProps {
    date: Date;
    dayNumber: number;
    weekNumber: number;
    dayInfo: DayLogSummary;
    isToday: boolean;
    onEditLog: (log: DailyLogData) => void;
    compact?: boolean;
}

function DayCard({ date, dayNumber, weekNumber, dayInfo, isToday, onEditLog, compact = false }: DayCardProps) {
    const { hasLog, log, isFuture } = dayInfo;
    const isHighMortality = log && log.mortality_count > 10;
    const hasRain = log && log.rainfall_mm && log.rainfall_mm > 0;

    const handleClick = () => {
        if (log?.isEditable) {
            onEditLog(log);
        }
    };

    const getCellClasses = () => {
        const base = 'relative rounded-lg border transition-all flex flex-col overflow-hidden';
        const height = compact ? 'min-h-[80px]' : 'min-h-[110px] sm:min-h-[130px]';

        if (isFuture) {
            return `${base} ${height} border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/20`;
        }

        if (hasLog) {
            if (isHighMortality) {
                return `${base} ${height} border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 cursor-pointer hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md`;
            }
            return `${base} ${height} border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 cursor-pointer hover:border-green-300 dark:hover:border-green-700 hover:shadow-md`;
        }

        return `${base} ${height} border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900`;
    };

    const weekday = date.toLocaleDateString('en-US', { weekday: 'short' });
    const monthDay = date.getDate();
    const month = date.toLocaleDateString('en-US', { month: 'short' });

    return (
        <div className={getCellClasses()} onClick={handleClick}>
            {/* Header */}
            <div className={`flex items-center justify-between px-2 py-1.5 border-b ${
                isToday 
                    ? 'bg-green-500 border-green-600 text-white' 
                    : 'bg-gray-50 dark:bg-gray-800/50 border-gray-100 dark:border-gray-800'
            }`}>
                <div className="flex items-center gap-1.5">
                    <span className={`text-[11px] font-bold ${isToday ? 'text-white' : 'text-gray-700 dark:text-gray-300'}`}>
                        D{dayNumber}
                    </span>
                    {!compact && (
                        <span className={`text-[10px] px-1 py-0.5 rounded ${
                            isToday 
                                ? 'bg-green-400/30 text-green-100' 
                                : 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                        }`}>
                            W{weekNumber}
                        </span>
                    )}
                </div>
                <div className="flex items-center gap-1">
                    {hasRain && <CloudRain className={`h-3 w-3 ${isToday ? 'text-blue-200' : 'text-blue-500'}`} />}
                    <span className={`text-[10px] ${isToday ? 'text-green-100' : 'text-gray-500 dark:text-gray-400'}`}>
                        {monthDay} {month}
                    </span>
                </div>
            </div>

            {/* Content */}
            {hasLog && log ? (
                <div className="flex-1 p-2 space-y-1.5">
                    {/* Primary metrics - always visible */}
                    <div className="flex items-center justify-between gap-2">
                        <div className="flex items-center gap-1">
                            <AlertTriangle className={`h-3.5 w-3.5 ${isHighMortality ? 'text-amber-500' : 'text-gray-400'}`} />
                            <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">{log.mortality_count}</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <Utensils className="h-3.5 w-3.5 text-gray-400" />
                            <span className="text-xs text-gray-600 dark:text-gray-400">{log.feed_consumed_kg}kg</span>
                        </div>
                    </div>
                    
                    {/* Secondary metrics */}
                    {!compact && (
                        <div className="space-y-1">
                            {log.water_consumed_liters && (
                                <div className="flex items-center gap-1">
                                    <Droplets className="h-3 w-3 text-blue-400" />
                                    <span className="text-[10px] text-gray-500 dark:text-gray-400">{log.water_consumed_liters}L water</span>
                                </div>
                            )}
                            <div className="flex items-center gap-2 flex-wrap">
                                {log.temperature_celsius && (
                                    <div className="flex items-center gap-0.5" title="Temperature">
                                        <Thermometer className="h-3 w-3 text-orange-400" />
                                        <span className="text-[10px] text-gray-500 dark:text-gray-400">{log.temperature_celsius}°</span>
                                    </div>
                                )}
                                {log.humidity_percent && (
                                    <div className="flex items-center gap-0.5" title="Humidity">
                                        <Gauge className="h-3 w-3 text-cyan-500" />
                                        <span className="text-[10px] text-gray-500 dark:text-gray-400">{log.humidity_percent}%</span>
                                    </div>
                                )}
                                {log.ammonia_ppm && (
                                    <div className="flex items-center gap-0.5" title="Ammonia">
                                        <Wind className={`h-3 w-3 ${log.ammonia_ppm > 20 ? 'text-red-500' : 'text-emerald-500'}`} />
                                        <span className="text-[10px] text-gray-500 dark:text-gray-400">{log.ammonia_ppm}ppm</span>
                                    </div>
                                )}
                            </div>
                            {hasRain && (
                                <div className="flex items-center gap-1">
                                    <CloudRain className="h-3 w-3 text-blue-500" />
                                    <span className="text-[10px] text-blue-600 dark:text-blue-400">{log.rainfall_mm}mm rain</span>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Edit indicator */}
                    {log.isEditable && (
                        <div className="absolute bottom-1.5 right-1.5">
                            <Edit className="h-3 w-3 text-gray-400 dark:text-gray-500" />
                        </div>
                    )}
                </div>
            ) : isFuture ? (
                <div className="flex-1 flex flex-col items-center justify-center p-2 text-gray-400 dark:text-gray-600">
                    <span className="text-[10px]">{weekday}</span>
                    <span className="text-xs">Future</span>
                </div>
            ) : (
                <div className="flex-1 flex flex-col items-center justify-center p-2 text-gray-400 dark:text-gray-500">
                    <span className="text-[10px]">{weekday}</span>
                    <span className="text-xs">No log</span>
                </div>
            )}
        </div>
    );
}

// Expanded card for single day view (Today)
interface DayCardExpandedProps {
    date: Date;
    dayNumber: number;
    weekNumber: number;
    dayInfo: DayLogSummary;
    onEditLog: (log: DailyLogData) => void;
}

function DayCardExpanded({ date, dayNumber, weekNumber, dayInfo, onEditLog }: DayCardExpandedProps) {
    const { hasLog, log, isFuture } = dayInfo;
    const isHighMortality = log && log.mortality_count > 10;
    const hasRain = log && log.rainfall_mm && log.rainfall_mm > 0;

    const handleClick = () => {
        if (log?.isEditable) {
            onEditLog(log);
        }
    };

    const weekday = date.toLocaleDateString('en-US', { weekday: 'long' });
    const fullDate = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

    if (!hasLog) {
        return (
            <div className="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 text-center">
                <div className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    Day {dayNumber} • Week {weekNumber}
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {weekday}, {fullDate}
                </div>
                <p className="text-gray-500 dark:text-gray-400">
                    {isFuture ? 'This day is in the future' : 'No log recorded for today'}
                </p>
            </div>
        );
    }

    return (
        <div
            className={`rounded-xl border-2 ${
                isHighMortality
                    ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20'
                    : 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20'
            } ${log?.isEditable ? 'cursor-pointer hover:shadow-lg transition-shadow' : ''}`}
            onClick={handleClick}
        >
            {/* Header */}
            <div className="px-4 py-3 border-b border-gray-200/50 dark:border-gray-700/50 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <div className="flex items-center gap-2 flex-wrap">
                        <span className="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Day {dayNumber}
                        </span>
                        <span className="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium rounded">
                            Week {weekNumber}
                        </span>
                        {hasRain && (
                            <span className="flex items-center gap-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs rounded">
                                <CloudRain className="h-3 w-3" />
                                {log?.rainfall_mm}mm
                            </span>
                        )}
                    </div>
                    <div className="text-sm text-gray-500 dark:text-gray-400">
                        {weekday}, {fullDate}
                    </div>
                </div>
                {log?.isEditable && (
                    <div className="flex items-center gap-1 px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded-full">
                        <Edit className="h-3.5 w-3.5" />
                        Tap to edit
                    </div>
                )}
            </div>

            {/* Metrics Grid */}
            <div className="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                <MetricItem
                    icon={AlertTriangle}
                    label="Mortality"
                    value={log!.mortality_count.toString()}
                    iconColor={isHighMortality ? 'text-amber-500' : 'text-gray-400'}
                    alert={isHighMortality}
                />
                <MetricItem
                    icon={Utensils}
                    label="Feed"
                    value={`${log!.feed_consumed_kg} kg`}
                    iconColor="text-gray-400"
                />
                {log!.water_consumed_liters && (
                    <MetricItem
                        icon={Droplets}
                        label="Water"
                        value={`${log!.water_consumed_liters} L`}
                        iconColor="text-blue-400"
                    />
                )}
                {log!.temperature_celsius && (
                    <MetricItem
                        icon={Thermometer}
                        label="Temperature"
                        value={`${log!.temperature_celsius}°C`}
                        iconColor="text-orange-400"
                    />
                )}
                {log!.humidity_percent && (
                    <MetricItem
                        icon={Gauge}
                        label="Humidity"
                        value={`${log!.humidity_percent}%`}
                        iconColor="text-cyan-500"
                    />
                )}
                {log!.ammonia_ppm && (
                    <MetricItem
                        icon={Wind}
                        label="Ammonia"
                        value={`${log!.ammonia_ppm} ppm`}
                        iconColor={log!.ammonia_ppm > 20 ? 'text-red-500' : 'text-emerald-500'}
                        alert={log!.ammonia_ppm > 20}
                    />
                )}
            </div>
        </div>
    );
}

// Metric display component for expanded view
interface MetricItemProps {
    icon: React.ElementType;
    label: string;
    value: string;
    iconColor: string;
    alert?: boolean;
}

function MetricItem({ icon: Icon, label, value, iconColor, alert }: MetricItemProps) {
    return (
        <div className={`flex items-center gap-3 p-3 rounded-lg ${
            alert ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-white/60 dark:bg-gray-800/50'
        }`}>
            <Icon className={`h-5 w-5 shrink-0 ${iconColor}`} />
            <div className="min-w-0">
                <div className="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    {label}
                </div>
                <div className={`text-sm font-semibold truncate ${
                    alert ? 'text-amber-700 dark:text-amber-300' : 'text-gray-900 dark:text-gray-100'
                }`}>
                    {value}
                </div>
            </div>
        </div>
    );
}
