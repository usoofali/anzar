<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Mail, Lock, LogIn, ShieldAlert } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Sign In to Portal',
        description: 'Enter your authorized credentials to access your account',
    },
});

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();
</script>

<template>
    <Head title="Log in - Anzar Table Water" />

    <div
        v-if="status"
        class="mb-6 rounded-xl bg-cyan-950/60 border border-cyan-500/30 p-4 text-center text-sm font-medium text-cyan-300 flex items-center justify-center gap-2"
    >
        <span>{{ status }}</span>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <!-- Email Input -->
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium text-slate-200">
                    Email address
                </Label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <Mail class="w-4 h-4" />
                    </div>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="staff@anzarwater.com"
                        class="pl-10 bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 focus:border-cyan-500 focus:ring-cyan-500/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Input -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium text-slate-200">
                        Password
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 z-10">
                        <Lock class="w-4 h-4" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="pl-10 bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 focus:border-cyan-500 focus:ring-cyan-500/20"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <Label for="remember" class="flex items-center space-x-3 cursor-pointer select-none">
                    <Checkbox 
                        id="remember" 
                        name="remember" 
                        :tabindex="3"
                        class="border-slate-700 data-[state=checked]:bg-cyan-600 data-[state=checked]:border-cyan-600"
                    />
                    <span class="text-xs text-slate-300">Keep me signed in</span>
                </Label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                class="mt-2 w-full h-11 bg-gradient-to-r from-cyan-600 to-sky-500 hover:from-cyan-500 hover:to-sky-400 text-white font-semibold shadow-lg shadow-cyan-600/25 transition-all duration-200 flex items-center justify-center gap-2"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="size-4" />
                <LogIn v-else class="w-4 h-4" />
                <span>Sign In to Dashboard</span>
            </Button>
        </div>
    </Form>
</template>
