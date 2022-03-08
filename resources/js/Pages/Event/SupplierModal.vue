<template>
    <va-modal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" size="small" title="Add supplier" hide-default-actions>
        <form @submit.prevent="submit">
            <va-select
                v-model="form.supplier"
                :options="suppliers"
                text-by="name"
                value-by="id"
                track-by="id"
                label="Supplier"
                class="mb-4"
                :error="!!form.errors.supplier"
                :error-messages="form.errors.supplier"
            />

            <va-input
                v-model="form.value"
                label="Value"
                class="mb-4"
                :error="!!form.errors.value"
                :error-messages="form.errors.value"
            />

            <va-select
                v-model="form.status"
                :options="['pending', 'hired']"
                label="Status"
                class="mb-4"
                :error="!!form.errors.status"
                :error-messages="form.errors.status"
            />

            <va-button type="submit" :loading="form.processing">
                Add supplier
            </va-button>
        </form>
    </va-modal>
</template>

<script>
import { computed, watchEffect } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default {
    emits: [
        'update:modelValue'
    ],
    props: [
        'event',
        'supplier',
        'category',
        'suppliers',
        'modelValue',
    ],
    setup(props, { emit }) {
        const form = useForm({
            value: '',
            supplier: '',
            status: 'pending',
        })

        watchEffect(() => {
            if (props.supplier) {
                form.supplier = props.supplier.id
                form.value = props.supplier.pivot.value
                form.status = props.supplier.pivot.status
            }
        })

        const suppliers = computed(() => {
            return props.suppliers.filter(supplier => {
                return supplier.category_id == props.category.id
            })
        })

        function submit() {
            form.clearErrors()

            if (props.supplier) {
                form.put(route('suppliers.update', {
                    event: props.event.id,
                    supplier: props.supplier.id
                }), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            } else {
                form.post(route('suppliers.attach', props.event.id), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            }
        }

        return { form, submit, suppliers }
    }
}
</script>
