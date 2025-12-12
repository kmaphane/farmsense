import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useState } from 'react';

interface Props {
    title: string;
    children: React.ReactNode;
    summary?: React.ReactNode;
    defaultOpen?: boolean;
}

export function CollapsibleSection({
    title,
    children,
    summary,
    defaultOpen = true,
}: Props) {
    const [isOpen, setIsOpen] = useState(defaultOpen);

    return (
        <section className="space-y-4">
            <div className="flex items-center justify-between">
                <h2 className="flex items-center gap-2 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">
                    {title}
                </h2>
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setIsOpen(!isOpen)}
                    className="text-muted-foreground hover:text-foreground"
                >
                    {isOpen ? (
                        <ChevronUp className="h-5 w-5" />
                    ) : (
                        <ChevronDown className="h-5 w-5" />
                    )}
                </Button>
            </div>

            {/* Summary (visible only when collapsed) */}
            {summary && !isOpen && <div className="mb-4">{summary}</div>}

            {/* Collapsible content */}
            {isOpen && <div className="animate-in fade-in slide-in-from-top-2">{children}</div>}
        </section>
    );
}
