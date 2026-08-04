import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('navigate', (event) => {
        const page = (event as any).detail?.page;
        const flash = page?.props?.flash;

        if (!flash) return;

        if (flash.error) {
            toast.error(flash.error);
        } else if (flash.success) {
            toast.success(flash.success);
        }
    });
}
