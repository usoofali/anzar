<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Droplets,
    ShieldCheck,
    Truck,
    Boxes,
    BarChart3,
    ArrowRight,
    CheckCircle2,
    Sparkles,
    Factory,
    Building2,
    Receipt,
    UserCheck,
    Layers,
    Award,
    Menu,
    X
} from '@lucide/vue';

const mobileMenuOpen = ref(false);
</script>

<template>
    <Head title="Anzar - Pure & Refreshing Premium Drinking Water">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-slate-950 text-slate-100 font-['Plus_Jakarta_Sans',sans-serif] selection:bg-cyan-500 selection:text-white relative overflow-hidden">
        <!-- Background Ambient Lighting & Glow FX -->
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-cyan-600/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute top-1/3 -right-40 w-[500px] h-[500px] bg-sky-500/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-10 left-1/3 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[160px] pointer-events-none"></div>

        <!-- Navigation Bar -->
        <header class="relative z-50 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl sticky top-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between">
                <!-- Logo & Brand Name -->
                <Link href="/" class="flex items-center gap-2.5 sm:gap-3 group shrink-0">
                    <div class="flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white shadow-lg shadow-cyan-500/25 group-hover:scale-105 transition-all duration-300">
                        <AppLogoIcon class="size-5 sm:size-6 text-white" />
                    </div>
                    <div>
                        <span class="text-lg sm:text-xl font-bold tracking-tight text-white block leading-none">Anzar</span>
                        <span class="text-[10px] sm:text-xs text-cyan-400 font-medium tracking-wide">Pure & Premium Hydration</span>
                    </div>
                </Link>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                    <a href="#about" class="hover:text-cyan-400 transition-colors">Quality Assurance</a>
                    <a href="#products" class="hover:text-cyan-400 transition-colors">Product Line</a>
                    <a href="#features" class="hover:text-cyan-400 transition-colors">Factory Systems</a>
                </nav>

                <!-- Auth Buttons & Mobile Menu Toggle -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="dashboard()"
                        class="inline-flex items-center gap-1.5 sm:gap-2 px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-sky-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-cyan-600/30 hover:shadow-cyan-500/40 hover:from-cyan-500 hover:to-sky-400 transition-all duration-200"
                    >
                        <span class="hidden sm:inline">Go to Dashboard</span>
                        <span class="sm:hidden">Dashboard</span>
                        <ArrowRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </Link>

                    <Link
                        v-else
                        :href="login()"
                        class="inline-flex items-center gap-1.5 sm:gap-2 px-3.5 py-2 sm:px-5 sm:py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-sky-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-cyan-600/30 hover:shadow-cyan-500/40 hover:from-cyan-500 hover:to-sky-400 transition-all duration-200"
                    >
                        <UserCheck class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        <span>Sign In</span>
                    </Link>

                    <!-- Mobile Hamburger Button -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        type="button"
                        class="md:hidden p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                        aria-label="Toggle navigation menu"
                    >
                        <Menu v-if="!mobileMenuOpen" class="w-5 h-5" />
                        <X v-else class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-slate-800/80 bg-slate-950/95 backdrop-blur-2xl px-4 py-4 space-y-3">
                    <a
                        href="#about"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:text-cyan-400 hover:bg-slate-900/60 transition-colors"
                    >
                        Quality Assurance
                    </a>
                    <a
                        href="#products"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:text-cyan-400 hover:bg-slate-900/60 transition-colors"
                    >
                        Product Line
                    </a>
                    <a
                        href="#features"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:text-cyan-400 hover:bg-slate-900/60 transition-colors"
                    >
                        Factory Systems
                    </a>
                </div>
            </transition>
        </header>

        <!-- Hero Section -->
        <section class="relative z-10 pt-16 pb-24 lg:pt-24 lg:pb-32 max-w-7xl mx-auto px-6">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <!-- Hero Left Content -->
                <div class="lg:col-span-7 space-y-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-300 text-xs font-semibold uppercase tracking-wider">
                        <Sparkles class="w-4 h-4 text-cyan-400 animate-pulse" />
                        <span>Reverse Osmosis & UV Purified Water</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15]">
                        Pure Quality Water, <br />
                        <span class="bg-gradient-to-r from-cyan-400 via-sky-300 to-blue-400 bg-clip-text text-transparent">
                            Precision Operations.
                        </span>
                    </h1>

                    <p class="text-slate-300 text-lg leading-relaxed max-w-2xl">
                        Anzar Table Water provides clean, pure, refreshing sachet and bottled drinking water. Powered by an end-to-end enterprise management portal for production batch tracking, shop distribution, and inventory.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="dashboard()"
                            class="inline-flex items-center gap-3 px-7 py-3.5 rounded-xl bg-gradient-to-r from-cyan-600 to-sky-500 text-white font-bold shadow-xl shadow-cyan-600/30 hover:scale-[1.02] transition-transform duration-200"
                        >
                            <span>Open Operations Portal</span>
                            <ArrowRight class="w-5 h-5" />
                        </Link>
                        <Link
                            v-else
                            :href="login()"
                            class="inline-flex items-center gap-3 px-7 py-3.5 rounded-xl bg-gradient-to-r from-cyan-600 to-sky-500 text-white font-bold shadow-xl shadow-cyan-600/30 hover:scale-[1.02] transition-transform duration-200"
                        >
                            <span>Access Management Portal</span>
                            <ArrowRight class="w-5 h-5" />
                        </Link>

                        <a
                            href="#products"
                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-slate-900 border border-slate-700/80 text-slate-200 font-semibold hover:bg-slate-800 hover:border-slate-600 transition-all duration-200"
                        >
                            <span>View Water Lineup</span>
                        </a>
                    </div>

                    <!-- Highlights Grid -->
                    <div class="pt-6 grid grid-cols-3 gap-6 border-t border-slate-800/80">
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">50k+ L</div>
                            <div class="text-xs text-slate-400 mt-1">Daily Pure Output</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-cyan-400">100%</div>
                            <div class="text-xs text-slate-400 mt-1">Certified RO Standard</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">200+</div>
                            <div class="text-xs text-slate-400 mt-1">Retail Partners</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Right Decorative Graphic Card -->
                <div class="lg:col-span-5 relative">
                    <div class="relative rounded-3xl p-8 bg-gradient-to-b from-slate-900/90 to-cyan-950/40 border border-cyan-500/20 shadow-2xl backdrop-blur-xl overflow-hidden space-y-6">
                        <!-- Top Card Decorative Badge -->
                        <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-xl bg-cyan-500/10 text-cyan-400">
                                    <Droplets class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Anzar Quality Seal</div>
                                    <div class="text-xs text-cyan-400">Batch RO-9041 Verified</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold">
                                <CheckCircle2 class="w-3.5 h-3.5" />
                                <span>Active</span>
                            </span>
                        </div>

                        <!-- Card Feature Items -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                <div class="p-2.5 rounded-lg bg-sky-500/10 text-sky-400">
                                    <Factory class="w-5 h-5" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-400">Automated Production</div>
                                    <div class="text-sm font-semibold text-white">Real-time Sachet & Bottle Tracking</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                <div class="p-2.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                                    <Truck class="w-5 h-5" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-400">Distribution Logistics</div>
                                    <div class="text-sm font-semibold text-white">Shop Deliveries & Returns</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                                <div class="p-2.5 rounded-lg bg-blue-500/10 text-blue-400">
                                    <Receipt class="w-5 h-5" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-slate-400">Financial Ledger</div>
                                    <div class="text-sm font-semibold text-white">Daily Collections & Customer Debts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Lineup Section -->
        <section id="products" class="relative z-10 py-20 bg-slate-900/60 border-y border-slate-800">
            <div class="max-w-7xl mx-auto px-6 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 text-xs font-semibold">
                        <Droplets class="w-3.5 h-3.5" />
                        <span>Our Product Lineup</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Pure Hydration for Every Need</h2>
                    <p class="text-slate-400 text-sm sm:text-base">
                        Crafted under strict hygienic conditions using multi-stage filtration, reverse osmosis, and ultraviolet sterilization.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Product 1 -->
                    <div class="rounded-2xl p-6 bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition-all duration-300 space-y-4 group">
                        <div class="h-44 rounded-xl bg-gradient-to-br from-cyan-950/80 to-slate-900 flex items-center justify-center p-6 border border-cyan-500/10 group-hover:scale-[1.02] transition-transform">
                            <Droplets class="w-16 h-16 text-cyan-400 drop-shadow-[0_0_20px_rgba(6,182,212,0.4)]" />
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-cyan-400 tracking-wider uppercase">High Volume Sachet</span>
                            <h3 class="text-xl font-bold text-white mt-1">Pure Sachet Water (50cl)</h3>
                            <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                                Hygienically sealed sachets packaged in 20-bag bundles for wholesale and retail distribution.
                            </p>
                        </div>
                    </div>

                    <!-- Product 2 -->
                    <div class="rounded-2xl p-6 bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition-all duration-300 space-y-4 group">
                        <div class="h-44 rounded-xl bg-gradient-to-br from-sky-950/80 to-slate-900 flex items-center justify-center p-6 border border-sky-500/10 group-hover:scale-[1.02] transition-transform">
                            <Layers class="w-16 h-16 text-sky-400 drop-shadow-[0_0_20px_rgba(56,189,248,0.4)]" />
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-sky-400 tracking-wider uppercase">Premium Bottled</span>
                            <h3 class="text-xl font-bold text-white mt-1">Bottled Water (75cl & 1.5L)</h3>
                            <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                                Crystal clear drinking water bottled in food-grade PET bottles for events, offices, and homes.
                            </p>
                        </div>
                    </div>

                    <!-- Product 3 -->
                    <div class="rounded-2xl p-6 bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition-all duration-300 space-y-4 group">
                        <div class="h-44 rounded-xl bg-gradient-to-br from-blue-950/80 to-slate-900 flex items-center justify-center p-6 border border-blue-500/10 group-hover:scale-[1.02] transition-transform">
                            <Building2 class="w-16 h-16 text-blue-400 drop-shadow-[0_0_20px_rgba(96,165,250,0.4)]" />
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-blue-400 tracking-wider uppercase">Office & Home Dispenser</span>
                            <h3 class="text-xl font-bold text-white mt-1">Dispenser Refills (20L)</h3>
                            <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                                Heavy-duty dispenser bottles with tamper-proof seals for continuous corporate hydration.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- System Features Section -->
        <section id="features" class="relative z-10 py-24 max-w-7xl mx-auto px-6 space-y-16">
            <div class="text-center max-w-2xl mx-auto space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 text-xs font-semibold">
                    <Factory class="w-3.5 h-3.5" />
                    <span>Enterprise Operations System</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Integrated Factory & Logistics Management</h2>
                <p class="text-slate-400 text-sm sm:text-base">
                    Built specifically for Anzar Table Water to streamline daily production, deliveries, and financial accuracy.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3 hover:bg-slate-900 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                        <Boxes class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Batch Tracking</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Track raw material usage, film rolls, filter replacements, and net production output per shift.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3 hover:bg-slate-900 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                        <Truck class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Shop Distribution</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Record shop deliveries, driver dispatches, customer debts, and leakage return claims seamlessly.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3 hover:bg-slate-900 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                        <Receipt class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Daily Collections</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Monitor daily cash flow, debt repayments, and operating expense logs with full audit trails.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3 hover:bg-slate-900 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                        <BarChart3 class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Analytics & Reports</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Generate comprehensive PDF/print reports for daily sales, inventory levels, and profitability metrics.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative z-10 py-16 max-w-7xl mx-auto px-6">
            <div class="rounded-3xl p-10 sm:p-14 bg-gradient-to-r from-cyan-950 via-slate-900 to-sky-950 border border-cyan-500/30 flex flex-col md:flex-row items-center justify-between gap-8 shadow-2xl relative overflow-hidden">
                <div class="space-y-3 max-w-xl text-center md:text-left">
                    <h3 class="text-2xl sm:text-3xl font-bold text-white">Authorized Staff Portal</h3>
                    <p class="text-slate-300 text-sm">
                        Access your Anzar Table Water account to manage factory operations, inventory, or distribution records.
                    </p>
                </div>

                <div class="shrink-0">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="dashboard()"
                        class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 font-bold hover:brightness-110 shadow-lg transition-all"
                    >
                        <span>Open Operations Dashboard</span>
                        <ArrowRight class="w-5 h-5" />
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                        class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 font-bold hover:brightness-110 shadow-lg transition-all"
                    >
                        <span>Sign In to Portal</span>
                        <ArrowRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-slate-800/80 bg-slate-950 py-12 text-slate-400 text-xs">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-600 text-white">
                        <AppLogoIcon class="size-4 text-white" />
                    </div>
                    <span class="text-sm font-semibold text-slate-200">Anzar Table Water Ltd.</span>
                </div>

                <div class="flex items-center gap-6">
                    <span>Clean Water • Pure Life</span>
                    <span>•</span>
                    <span>© {{ new Date().getFullYear() }} All Rights Reserved</span>
                </div>
            </div>
        </footer>
    </div>
</template>
