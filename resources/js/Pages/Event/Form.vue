<template>
    <h1 class="display-1 mb-4">New Event</h1>

    <form @submit.prevent="submit">
        <va-input
            v-model="form.title"
            label="Title"
            placeholder="John & Mary Wedding"
            class="mb-4"
            :error="!!form.errors.title"
            :error-messages="form.errors.title"
        />

        <va-input
            v-model="form.budget"
            label="Budget"
            class="mb-4"
            :error="!!form.errors.budget"
            :error-messages="form.errors.budget"
        />

        <div class="d-flex mb-4">
            <va-date-input
                v-model="form.attending_date"
                label="Attending date"
                :error="!!form.errors.attending_date"
                :error-messages="form.errors.attending_date"
            />

            <va-divider vertical />

            <va-time-input
                v-model="form.attending_time"
                label="Attending hour"
                :error="!!form.errors.attending_time"
                :error-messages="form.errors.attending_time"
            />
        </div>

        <va-select
            label="Assignees"
            v-model="form.users"
            value-by="id"
            text-by="name"
            :options="users"
            :error="!!form.errors.users"
            :error-messages="form.errors.users"
            multiple
            class="mb-4"
        />

        <va-button type="submit" :loading="form.processing">
            Create event
        </va-button>
    </form>
</template>

<script>
import { ref } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    props: ['users', 'save_url'],
    setup(props) {
        const form = useForm({
            title: '',
            budget: '',
            attending_date: new Date(),
            attending_time: new Date(),
            users: [],
        })

        function submit() {
            form.clearErrors()
            form.post(props.save_url)
        }

        return { form, submit }
    },
}
</script>
