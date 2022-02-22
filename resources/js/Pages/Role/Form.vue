<template>
    <form @submit.prevent="submit">
        <va-input
            class="mb-4"
            label="Name"
            v-model="form.name"
            :error="!!form.errors.name"
            :error-messages="form.errors.name"
        />

        <va-checkbox
            class="mb-1"
            v-for="permission in permissions"
            :label="permission"
            v-model="form.permissions"
            :array-value="permission"
            :error="!!form.errors.permissions"
            :error-messages="form.errors.permissions"
        />

        <div class="mt-4">
            <va-button type="submit" :loading="form.processing">Save</va-button>

            <Link :href="destroy_url" v-if="role">
                <va-button type="button" color="danger" class="ml-2">Delete role</va-button>
            </Link>
        </div>
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
