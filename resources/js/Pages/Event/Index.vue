<template>
    <div class="d-flex mb-4 align--center justify--space-between">
        <h1 class="display-1">Events</h1>

        <Link :href="route('events.create')">
            <va-button>New event</va-button>
        </Link>
    </div>

    <div class="row">
        <div class="flex md3" v-for="event in events">
            <Link :href="route('events.view', event.id)">
                <va-card>
                    <va-card-title>{{ event.title }}</va-card-title>

                    <va-card-content>
                        <p>Budget: {{ event.budget }}</p>
                        <p>Date: {{ event.attending_date }}</p>

                        <p style="margin-top: 1rem" v-for="user in event.assignees">
                            <va-avatar size="small">
                                <va-icon name="warning" size="small" />
                            </va-avatar>

                            {{ user.name }}
                        </p>
                    </va-card-content>
                </va-card>
            </Link>
        </div>
    </div>

    <p v-if="!events">No events</p>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

export default {
    props: ['events'],
    components: {
        Link,
    },
}
</script>
