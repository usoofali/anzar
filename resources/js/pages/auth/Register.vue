<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login } from '@/routes';
import { ShieldAlert, ArrowLeft } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Registration Disabled',
        description: 'Self-registration is not allowed for this system',
    },
});
</script>

<template>
    <Head title="Registration Disabled - Anzar Table Water" />

    <div class="text-center space-y-6 py-4">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
            <ShieldAlert class="w-6 h-6" />
        </div>

        <div class="space-y-2">
            <h3 class="text-lg font-bold text-white">Self-Registration Disabled</h3>
            <p class="text-sm text-slate-300">
                User accounts for Anzar Table Water must be created by an administrator. Please contact your system administrator or manager to get access.
            </p>
        </div>

        <Link
            :href="login()"
            class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold border border-slate-700 transition-colors"
        >
            <ArrowLeft class="w-4 h-4" />
            <span>Return to Log In</span>
        </Link>
    </div>
</template>
