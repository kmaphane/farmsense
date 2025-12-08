import { Bird, DollarSign, Package, Plus, Scissors, Users } from 'lucide-react';
import { useState } from 'react';
import { Sheet, SheetContent, SheetHeader, SheetBody } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

interface QuickAction {
  label: string;
  description: string;
  href: string;
  icon: React.ElementType;
  color: string;
  group: 'production' | 'management';
}

const quickActions: QuickAction[] = [
  {
    label: 'New Batch',
    description: 'Start a new broiler batch',
    href: '/admin/batches/create',
    icon: Bird,
    color: 'text-green-600 dark:text-green-400',
    group: 'production',
  },
  {
    label: 'Record Slaughter',
    description: 'Process birds for sale',
    href: '/slaughter/create',
    icon: Scissors,
    color: 'text-red-600 dark:text-red-400',
    group: 'production',
  },
  {
    label: 'New Portioning',
    description: 'Cut into packs',
    href: '/portioning/create',
    icon: Package,
    color: 'text-purple-600 dark:text-purple-400',
    group: 'production',
  },
  {
    label: 'Update Pricing',
    description: 'Manage product prices',
    href: '/products/pricing',
    icon: DollarSign,
    color: 'text-blue-600 dark:text-blue-400',
    group: 'management',
  },
  {
    label: 'New Customer',
    description: 'Add a customer',
    href: '/admin/customers/create',
    icon: Users,
    color: 'text-amber-600 dark:text-amber-400',
    group: 'management',
  },
];

export function QuickCreateSheet() {
  const [open, setOpen] = useState(false);
  const productionActions = quickActions.filter(a => a.group === 'production');
  const managementActions = quickActions.filter(a => a.group === 'management');

  // TODO: Fetch last created item for duplication avoidance
  const lastCreated = {
    label: 'Last Batch',
    description: 'Broiler batch started on 2025-12-07',
    href: '/admin/batches/123',
  };

  return (
    <>
      <Button
        variant="ghost"
        size="icon"
        className="h-8 w-8 rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200 hover:text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
        onClick={() => setOpen(true)}
        aria-label="Quick Create"
      >
        <Plus className="h-4 w-4" />
        <span className="sr-only">Quick Create</span>
      </Button>
      <Sheet open={open} onOpenChange={setOpen}>
        <SheetContent side="right" size="md" className="p-0">
          <SheetHeader
            title="Quick Create"
            icon={<Plus className="size-6" />}
            className="border-b p-4 flex  gap-2"
          />
          <SheetBody className="divide-y">
            {lastCreated && (
              <div className="p-4 bg-primary/5 border-b">
                <div className="font-semibold text-primary">Last Created</div>
                <Link href={lastCreated.href} className="text-sm text-link underline">
                  {lastCreated.label}: {lastCreated.description}
                </Link>
              </div>
            )}
            <div className="p-4">
              <div className="text-xs font-normal text-muted-foreground mb-2">Production</div>
              <ul className="flex flex-col gap-2">
                {productionActions.map(action => (
                  <li key={action.href}>
                    <Link
                      href={action.href}
                      className="flex items-center gap-3 p-2 rounded hover:bg-primary/10"
                      onClick={() => setOpen(false)}
                    >
                      <action.icon className={`h-5 w-5 ${action.color}`} />
                      <div className="flex flex-col">
                        <span className="font-medium">{action.label}</span>
                        <span className="text-xs text-muted-foreground">{action.description}</span>
                      </div>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
            <div className="p-4">
              <div className="text-xs font-normal text-muted-foreground mb-2">Management</div>
              <ul className="flex flex-col gap-2">
                {managementActions.map(action => (
                  <li key={action.href}>
                    <Link
                      href={action.href}
                      className="flex items-center gap-3 p-2 rounded hover:bg-primary/10"
                      onClick={() => setOpen(false)}
                    >
                      <action.icon className={`h-5 w-5 ${action.color}`} />
                      <div className="flex flex-col">
                        <span className="font-medium">{action.label}</span>
                        <span className="text-xs text-muted-foreground">{action.description}</span>
                      </div>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          </SheetBody>
        </SheetContent>
      </Sheet>
    </>
  );
}
