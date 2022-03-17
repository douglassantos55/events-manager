import { ref } from 'vue'

export default function () {
    const visible = ref(false)

    function open() {
        visible.value = true
    }

    return { visible, open }
}

