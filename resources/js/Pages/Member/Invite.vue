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

        <va-select
            v-model="form.role_id"
            :options="roles"
            text-by="name"
            value-by="id"
            label="Role"
            class="mb-4"
            :error="!!form.errors.role_id"
            :error-messages="form.errors.role_id"
        />

        <va-button type="submit" :loading="form.processing">
            Invite
        </va-button>
    </form>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['roles', 'save_url'],
    setup({ save_url }) {
        const form = useForm({
            name: '',
            email: '',
            role_id: '',
        })

        function submit() {
            form.clearErrors()
            form.post(save_url)
        }

        return { form, submit }
    }
}
</script>
