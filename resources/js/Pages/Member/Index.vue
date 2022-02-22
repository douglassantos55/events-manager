<template>
    <div class="mb-4 d-flex align--center justify--space-between">
        <h1 class="display-1">Members</h1>

        <Link :href="invite_url">
            <va-button>Invite</va-button>
        </Link>
    </div>

    <table class="va-table va-table--striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Joined at</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="member in members" :key="member.id">
                <td>
                    <Link :href="`/members/edit/${member.id}`">
                        {{ member.name }}
                    </Link>
                </td>
                <td>{{ member.role.name }}</td>
                <td>
                    <span v-if="member.email_verified_at">
                        {{ new Date(member.email_verified_at).toLocaleDateString() }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

export default {
    props: ['members', 'invite_url'],
    components: {
        Link,
    },
}
</script>
