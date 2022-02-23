<template>
    <div class="layout gutter--lg">
        <div class="mb-4 d-flex align--center justify--space-between">
            <h1 class="display-1">{{ event.title }}</h1>
            <va-button icon="edit">Edit</va-button>
        </div>

        <div class="row">
            <div class="flex md3">
                <va-card>
                    <va-card-title>Info</va-card-title>

                    <va-card-content>
                        <ul>
                            <li>Budget: {{ event.budget }}</li>
                            <li>Date: {{ event.attending_date }}</li>
                        </ul>
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Assignees</va-card-title>

                    <va-card-content>
                        <p v-for="user in event.assignees" :key="user.id">
                            <va-avatar size="small">
                                <va-icon name="warning" size="small" />
                            </va-avatar>

                            {{ user.name }}

                            <va-button icon="delete" size="small" color="danger" @click="remove(user.id)" />
                        </p>
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Financing</va-card-title>

                    <va-card-content>
                        <p class="mb-2 text--bold text--right">$ 1350 / $ 10000</p>
                        <va-progress-bar :model-value="50" />
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Guests</va-card-title>

                    <va-card-content>
                        <p class="mb-2 text--bold text--right">135 / 300</p>
                        <va-progress-bar :model-value="50" />
                    </va-card-content>
                </va-card>
            </div>
        </div>

        <va-tabs stateful grow class="mt-4">
            <template #tabs>
                <va-tab
                    v-for="title in ['Dashboard', 'Suppliers', 'Agenda', 'Guests']"
                    :name="title"
                    :key="title"
                    active
                >
                    {{ title }}
                </va-tab>
            </template>
        </va-tabs>
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia'

export default {
    props: ['event'],
    setup({ event }) {
        function remove(userId) {
            Inertia.delete(`/assignees/${event.id}/${userId}`)
        }
        return { remove }
    }
}
</script>
