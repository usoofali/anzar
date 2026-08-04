import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatDate(dateString: string | null | undefined): string {
    if (!dateString) return 'N/A';

    const cleanDateStr = String(dateString).split('T')[0];
    const parts = cleanDateStr.split('-');

    if (parts.length === 3) {
        const [year, month, day] = parts;
        if (year.length === 4 && month && day) {
            return `${day.padStart(2, '0')}-${month.padStart(2, '0')}-${year}`;
        }
    }

    try {
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    } catch {
        return dateString;
    }
}
