<template>
    <div class="d-flex mb-4 align--center justify--space-between">
        <h1 class="display-1">Roles</h1>

        <Link :href="route('roles.create')">
            <va-button>New role</va-button>
        </Link>
    </div>

    <table class="va-table va-table--striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="role in roles" :key="role.id">
                <td>
                    <Link :href="route('roles.edit', role.id)">
                        {{ role.name }}
                    </Link>
                </td>

                <td>{{ role.permissions.join(', ') }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

export default {
    props: ['roles'],
    components: {
        Link,
    },
}
</script>
