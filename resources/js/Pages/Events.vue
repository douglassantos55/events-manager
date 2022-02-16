<template>
    <div class="flex align--center justify--space-between">
        <h1 class="display-1">Events</h1>
        <Link :href="create_url"><va-button>New event</va-button></Link>
    </div>

    <p v-for="event in events">{{ event.title }}</p>
    <p v-if="!events">no events</p>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

export default {
    props: ['events', 'create_url'],
    components: {
        Link,
    },
}
</script>
