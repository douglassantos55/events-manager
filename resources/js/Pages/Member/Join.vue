<template>
    <h1 class="display-1 mb-4">Join {{ member.captain.name }}'s team</h1>

    <form @submit.prevent="submit">
        <va-input label="Name" v-model="form.name" class="mb-4" :error="!!form.errors.name" :error-messages="form.errors.name" />
        <va-input label="Password" type="password" v-model="form.password" class="mb-4" :error="!!form.errors.password" :error-messages="form.errors.password" />
        <va-input label="Confirm password" type="password" v-model="form.password_confirmation" class="mb-4" />

        <va-button type="submit" :loading="form.processing">
            Join team
        </va-button>
    </form>
</template>

<script>
import AuthLayout from '../../AuthLayout.vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['member'],
    layout: AuthLayout,
    setup(props) {
        const form = useForm({
            name: props.member.name,
            password: '',
            password_confirmation: '',
        })

        function submit() {
            form.clearErrors();
            form.post(route('members.save', props.member.id))
        }

        return { form, submit }
    }
}
</script>
