<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';
import { KeyRound, Lock } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Reset Password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules?: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset Password - Anzar Table Water" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium text-slate-200">Email Address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    v-model="inputEmail"
                    class="bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 opacity-80"
                    readonly
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-medium text-slate-200">New Password</Label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 z-10">
                        <Lock class="w-4 h-4" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="pl-10 bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 focus:border-cyan-500 focus:ring-cyan-500/20"
                        autofocus
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-sm font-medium text-slate-200">Confirm New Password</Label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 z-10">
                        <Lock class="w-4 h-4" />
                    </div>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="pl-10 bg-slate-900/80 border-slate-700/80 text-white placeholder:text-slate-500 focus:border-cyan-500 focus:ring-cyan-500/20"
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full h-11 bg-gradient-to-r from-cyan-600 to-sky-500 hover:from-cyan-500 hover:to-sky-400 text-white font-semibold shadow-lg shadow-cyan-600/25 transition-all duration-200 flex items-center justify-center gap-2"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" class="size-4" />
                <KeyRound v-else class="w-4 h-4" />
                <span>Update Password</span>
            </Button>
        </div>
    </Form>
</template>
