<template>
    <h1 class="display-1 mb-4">Invite someone to your team</h1>

    <form @submit.prevent="submit">
        <va-input
            v-model="form.name"
            :error="!!form.errors.name"
            :error-messages="form.errors.name"
            label="Name" class="mb-4"
        />

        <va-input
            v-model="form.email"
            :error="!!form.errors.email"
            :error-messages="form.errors.email"
            label="Email" class="mb-4"
        />

        <va-button type="submit" :loading="form.processing">
            Invite
        </va-button>
    </form>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['save_url'],
    setup({ save_url }) {
        const form = useForm({
            name: '',
            email: '',
        })

        function submit() {
            form.clearErrors()
            form.post(save_url)
        }

        return { form, submit }
    }
}
</script>
