<template>
    <va-modal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" title="Add supplier" hide-default-actions>
        <form @submit.prevent="submit">
            <va-select
                v-model="form.supplier_id"
                :options="category.all_suppliers"
                text-by="name"
                value-by="id"
                track-by="id"
                label="Supplier"
                class="mb-4"
                :error="!!form.errors.supplier_id"
                :error-messages="form.errors.supplier_id"
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

            <div v-if="supplier && form.status == 'hired'">
                <p class="mb-2">Upload the contract related files</p>

                <va-file-upload
                    label="Contract"
                    v-model="form.contract"
                    :error="!!form.errors.contract"
                    :error-messages="form.errors.contract"
                />

                <p v-for="file in supplier.files" :key="file.id">
                    {{ file.path }}
                    <va-button size="small" color="danger" icon="delete" @click="removeFile(file.id)" />
                </p>
            </div>

            <va-button type="submit" :loading="form.processing">
                Add supplier
            </va-button>
        </form>
    </va-modal>
</template>

<script>
import { computed, watchEffect } from 'vue'
import { Inertia } from '@inertiajs/inertia'
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
            supplier_id: '',
            _method: 'post',
            status: 'pending',
            contract: [],
        })

        watchEffect(() => {
            if (props.supplier) {
                form._method = 'put'
                form.value = props.supplier.value
                form.status = props.supplier.status
                form.supplier_id = props.supplier.supplier_id
            }
        })

        function submit() {
            form.clearErrors()

            if (props.supplier) {
                form.post(route('suppliers.update', props.supplier.id), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            } else {
                form.post(route('suppliers.attach', props.category.id), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            }
        }

        function removeFile(file) {
            Inertia.delete(route('files.delete', file), {
                onSuccess: () => emit('update:modelValue', false)
            })
        }

        return { form, submit, removeFile }
    }
}
</script>
