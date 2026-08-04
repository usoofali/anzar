<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { ShieldCheck, Droplets, Truck, Award } from '@lucide/vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative min-h-screen w-full lg:grid lg:grid-cols-12 bg-slate-950 text-slate-100 overflow-hidden font-sans">
        <!-- Left Side: Ambient Brand Visual Panel (Visible on lg+) -->
        <div class="relative hidden lg:col-span-6 xl:col-span-7 lg:flex flex-col justify-between p-12 overflow-hidden bg-gradient-to-br from-cyan-950 via-slate-900 to-sky-950 border-r border-cyan-500/10">
            <!-- Animated Ambient Water Wave Background Glows -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
            <div class="absolute top-1/2 -right-24 w-80 h-80 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 left-1/3 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10">
                <Link :href="home()" class="inline-flex items-center gap-3 group">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform duration-300">
                        <AppLogoIcon class="size-6 text-white" />
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white block">Anzar Table Water</span>
                        <span class="text-xs text-cyan-300/80 font-medium tracking-wide">Enterprise Management System</span>
                    </div>
                </Link>
            </div>

            <!-- Middle Value Propositions -->
            <div class="relative z-10 my-auto py-12 max-w-xl space-y-8">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-300 text-xs font-semibold uppercase tracking-wider">
                    <Droplets class="w-3.5 h-3.5 text-cyan-400" />
                    <span>Pure • Refreshing • Certified</span>
                </div>

                <h1 class="text-4xl xl:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Pure Water Excellence & Streamlined Factory Operations.
                </h1>

                <p class="text-slate-300 text-base leading-relaxed">
                    Manage production batches, shop deliveries, daily collections, and raw materials with total real-time precision.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-900/60 border border-white/5 backdrop-blur-md">
                        <div class="p-2 rounded-lg bg-cyan-500/10 text-cyan-400">
                            <ShieldCheck class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Quality Controlled</h4>
                            <p class="text-xs text-slate-400">Multi-stage filtration monitoring</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-900/60 border border-white/5 backdrop-blur-md">
                        <div class="p-2 rounded-lg bg-sky-500/10 text-sky-400">
                            <Truck class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Distribution</h4>
                            <p class="text-xs text-slate-400">Automated shop delivery logs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 border-t border-white/10 pt-6">
                <div class="flex items-center gap-2">
                    <Award class="w-4 h-4 text-cyan-400" />
                    <span>© {{ new Date().getFullYear() }} Anzar Table Water Ltd.</span>
                </div>
                <span>v2.5 Enterprise</span>
            </div>
        </div>

        <!-- Right Side: Auth Form Container -->
        <div class="lg:col-span-6 xl:col-span-5 flex flex-col justify-center min-h-screen p-6 sm:p-12 md:p-16 bg-slate-900/95">
            <!-- Mobile Brand Header -->
            <div class="lg:hidden flex items-center justify-between mb-8 pb-6 border-b border-slate-800">
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white shadow-md">
                        <AppLogoIcon class="size-5 text-white" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-white block">Anzar Table Water</span>
                        <span class="text-xs text-cyan-400 font-medium">Management Portal</span>
                    </div>
                </Link>
            </div>

            <div class="w-full max-w-md mx-auto space-y-6">
                <div class="space-y-2 text-center lg:text-left">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight" v-if="title">
                        {{ title }}
                    </h2>
                    <p class="text-sm text-slate-400" v-if="description">
                        {{ description }}
                    </p>
                </div>

                <!-- Form Content Slot -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
                    <slot />
                </div>

                <!-- Footer info -->
                <p class="text-center text-xs text-slate-500">
                    Restricted access. Authorized Anzar personnel only.
                </p>
            </div>
        </div>
    </div>
</template>
