<template>
    <va-modal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" title="Add supplier" hide-default-actions>
        <form @submit.prevent="submit">
            <va-select
                v-model="form.supplier_id"
                :options="suppliers"
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
                form.supplier_id = props.supplier.supplier_id
                form.value = props.supplier.value
                form.status = props.supplier.status
            }
        })

        const suppliers = computed(() => {
            return props.suppliers.filter(supplier => {
                return supplier.category_id == props.category.category_id
            })
        });

        function submit() {
            form.clearErrors()

            if (props.supplier) {

                form.post(route('suppliers.update', {
                    event: props.event.id,
                    category: props.category.id,
                    supplier: props.supplier.id,
                }), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            } else {
                form.post(route('suppliers.attach', {
                    event: props.event.id,
                    category: props.category.id,
                }), {
                    preserveScroll: true,
                    onSuccess: () => emit('update:modelValue', false)
                })
            }
        }

        return { form, submit, suppliers }
    }
}
</script>
