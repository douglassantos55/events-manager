import { ref } from 'vue'

export default function () {
    const visible = ref(false)

    const open = () => (visible.value = true)
    const close = () => (visible.value = false)

    return { visible, open, close }
}

