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

        <Link :href="route('members.destroy', member.id)" v-if="member">
            <va-button type="button" color="danger" class="ml-2">Delete</va-button>
        </Link>
    </form>
</template>

<script>
import { Link, useForm } from '@inertiajs/inertia-vue3'

export default {
    components: {
        Link,
    },
    props: ['member', 'roles'],
    setup(props) {
        const form = useForm(props.member)

        function submit() {
            form.clearErrors()
            form.post(route('members.update', props.member.id))
        }

        return { form, submit }
    }
}
</script>
