<template>
    <h1 class="display-1 mb-4">Members</h1>

    <table class="va-table va-table--striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="member in members" :key="member.id">
                <td>{{ member.name }}</td>
                <td>{{ member.role.name }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    props: ['members'],
}
</script>
