<template>
    <div class="app-bar-container">
        <va-app-bar class="app-bar">
            <h1 style="color: --var(--va-gray)">Event manager</h1>

            <va-spacer />

            <Link :href="events_url">
                <va-button icon="create">Events</va-button>
            </Link>
        </va-app-bar>
    </div>

    <main>
        <slot />
    </main>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

export default {
    props: ['events_url'],
    components: {
        Link,
    },
}
</script>

<style scoped>
.app-bar-container {
    position: relative;
}
.app-bar {
    padding: 1rem;
}
main {
    padding: 1rem;
}
</style>
