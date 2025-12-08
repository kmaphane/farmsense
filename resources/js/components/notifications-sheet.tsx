import { Bell } from 'lucide-react';
import { useState } from 'react';
import { Sheet, SheetContent, SheetHeader, SheetBody } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';

// TODO: Replace with real notification data from API
const mockNotifications = [
  { id: 1, title: 'Welcome!', body: 'Thanks for joining FarmSense.', read: false, created_at: '2025-12-08T10:00:00Z' },
  { id: 2, title: 'System Update', body: 'FarmSense will be down for maintenance at 8pm.', read: false, created_at: '2025-12-07T18:00:00Z' },
  { id: 3, title: 'Reminder', body: 'Don’t forget to check your livestock inventory.', read: true, created_at: '2025-12-06T09:00:00Z' },
];

export function NotificationsSheet() {
  const [open, setOpen] = useState(false);
  const unreadCount = mockNotifications.filter(n => !n.read).length;

  return (
    <>
      <Button
        variant="ghost"
        className="relative p-2"
        onClick={() => setOpen(true)}
        aria-label="Open notifications"
      >
        <Bell className="size-6" />
        {unreadCount > 0 && (
          <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs text-white">
            {unreadCount}
          </span>
        )}
      </Button>
      <Sheet open={open} onOpenChange={setOpen}>
        <SheetContent side="right" size="md" className="p-0">
          <SheetHeader title="Notifications" icon={<Bell className="size-6" />} />
          <SheetBody className="divide-y">
            {mockNotifications.length === 0 ? (
              <div className="p-6 text-center text-muted-foreground">No notifications</div>
            ) : (
              <ul>
                {mockNotifications.map(n => (
                  <li key={n.id} className={`flex flex-col gap-1 p-4 ${!n.read ? 'bg-primary/5' : ''}`}>
                    <div className="flex items-center justify-between">
                      <span className={`font-medium ${!n.read ? 'text-primary' : ''}`}>{n.title}</span>
                      <span className="text-xs text-muted-foreground">{new Date(n.created_at).toLocaleString()}</span>
                    </div>
                    <span className="text-sm text-foreground/80">{n.body}</span>
                  </li>
                ))}
              </ul>
            )}
          </SheetBody>
        </SheetContent>
      </Sheet>
    </>
  );
}
