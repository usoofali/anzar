<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Mail, Send, ArrowLeft } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Forgot Password',
        description: 'Enter your email address to receive a password reset link',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot Password - Anzar Table Water" />

    <div
        v-if="status"
        class="mb-6 rounded-xl bg-cyan-950/60 border border-cyan-500/30 p-4 text-center text-sm font-medium text-cyan-300"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium text-slate-200">Email address</Label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <Mail class="w-4 h-4" />
                    </div>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="staff@anzarwater.com"
                        class="pl-10 bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 focus:border-cyan-500 focus:ring-cyan-500/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="mt-6 flex items-center justify-start">
                <Button
                    class="w-full h-11 bg-gradient-to-r from-cyan-600 to-sky-500 hover:from-cyan-500 hover:to-sky-400 text-white font-semibold shadow-lg shadow-cyan-600/25 transition-all duration-200 flex items-center justify-center gap-2"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" class="size-4" />
                    <Send v-else class="w-4 h-4" />
                    <span>Send Password Reset Link</span>
                </Button>
            </div>
        </Form>

        <div class="text-center text-xs text-slate-400 flex items-center justify-center gap-1.5 pt-2">
            <span>Remembered your password?</span>
            <TextLink :href="login()" class="text-cyan-400 hover:text-cyan-300 transition-colors inline-flex items-center gap-1">
                <span>Return to Log In</span>
            </TextLink>
        </div>
    </div>
</template>
