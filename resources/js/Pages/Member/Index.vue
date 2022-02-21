<template>
    <h1 class="display-1">Members</h1>

    <p v-for="member in members" :key="member.id">
        {{ member.name }}
    </p>
</template>

<script>
export default {
    props: ['members'],
}
</script>
