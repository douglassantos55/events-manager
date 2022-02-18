<template>
    <form @submit.prevent="submit">
        <va-input
            label="Name"
            v-model="form.name"
            :error="!!form.errors.name"
            :error-messages="form.errors.name"
        />

        <va-checkbox
            v-for="permission in permissions"
            :label="permission"
            v-model="form.permissions"
            :array-value="permission"
            :error="!!form.errors.permissions"
            :error-messages="form.errors.permissions"
        />

        <Link :href="destroy_url" v-if="role">
            <va-button>Delete role</va-button>
        </Link>

        <va-button type="submit" :loading="form.processing">Save</va-button>
    </form>
</template>

<script>
import { Link, useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['role', 'save_url', 'destroy_url', 'permissions'],
    components: {
        Link,
    },
    setup(props) {
        const form = useForm(props.role || {
            name: '',
            permissions: [],
        })

        function submit() {
            form.clearErrors()
            form.post(props.save_url)
        }

        return { form, submit }
    },
}
</script>
