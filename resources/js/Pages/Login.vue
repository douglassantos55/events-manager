<template>
    <form @submit.prevent="authenticate">
        <div>
            <label for="login-email">E-mail</label>
            <input type="email" v-model="form.email">
            <div v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>

        <div>
            <label for="login-password">Password</label>
            <input type="password" v-model="form.password">
        </div>

        <button type="submit" :disabled="form.processing">Login</button>
    </form>
</template>

<script>
import { reactive } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    setup(props) {
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
