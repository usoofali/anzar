<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const activeBreadcrumbs = computed(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 0) {
        return props.breadcrumbs;
    }
    return (page.props.breadcrumbs as BreadcrumbItem[]) || [];
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="activeBreadcrumbs && activeBreadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="activeBreadcrumbs" />
            </template>
        </div>
    </header>
</template>
