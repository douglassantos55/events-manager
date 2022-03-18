import { useForm } from '@inertiajs/inertia-vue3'

export default function(initialData, modal) {
    const form = useForm(initialData)

    const post = () => {
        form.clearErrors()
        form.post(url, { ...options })
    }
    return form
}
