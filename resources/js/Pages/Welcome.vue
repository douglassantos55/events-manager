<template>
    <h1>Hello</h1>
</template>

<script>
export default {
    props: {
        foo: String,
    },
    setup(props) {
        console.log(props)
    }
}
</script>
