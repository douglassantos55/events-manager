<template>
    <form @submit.prevent="submit">
        <va-input
            label="Name"
            v-model="form.name"
            :error="!!form.errors.name"
            :error-messages="form.errors.name"
            class="mb-4"
        />

        <va-select
            label="Role"
            text-by="name"
            value-by="id"
            :options="roles"
            v-model="form.role_id"
            class="mb-4"
            :error="!!form.errors.role_id"
            :error-messages="form.errors.role_id"
        />

        <va-button type="submit" :loading="form.processing">Update</va-button>
    </form>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['member', 'roles', 'save_url'],
    setup({ member, save_url }) {
        const form = useForm(member)

        function submit() {
            form.clearErrors()
            form.post(save_url)
        }

        return { form, submit }
    }
}
</script>
