<template>
    <form @submit.prevent="authenticate">
        <va-input
            type="email"
            v-model="form.email"
            label="Email"
            class="mb-4"
            :error="!!form.errors.email"
            :error-messages="form.errors.email"
        />

        <va-input class="mb-4" type="password" v-model="form.password" label="Password"/>

        <va-button type="submit" :loading="form.processing">Login</va-button>
    </form>
</template>

<script>
import AuthLayout from '../AuthLayout.vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    layout: AuthLayout,
    setup() {
        const form = useForm({
            email: '',
            password: '',
        })

        function authenticate() {
            form.clearErrors()
            form.post('/login')
        }

        return { form, authenticate }
    }
}
</script>
