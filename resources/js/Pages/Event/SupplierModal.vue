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
                <h5 class="mb-2">Upload the contract related files</h5>

                <va-file-upload
                    label="Contract"
                    v-model="form.contract"
                    :error="!!form.errors.contract"
                    :error-messages="form.errors.contract"
                />

                <p v-for="file in supplier.files" :key="file.id" class="mb-1">
                    <a :href="file.path" target="_blank">{{ file.path }}</a>
                    <va-button size="small" color="danger" icon="delete" @click="removeFile(file.id)" />
                </p>

                <h5 class="mb-2 mt-4">Installments</h5>

                <table class="va-table" style="width: 100%" v-if="supplier.installments.length > 0">
                    <tr>
                        <th>#</th>
                        <th>Value</th>
                        <th>Due Date</th>
                        <th width="140">Status</th>
                    </tr>

                    <tr v-for="(installment, idx) in supplier.installments" :key="installment.id">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ installment.value }}</td>
                        <td>{{ installment.due_date }}</td>
                        <td>
                            <va-select
                                v-model="installment.status"
                                :options="['pending', 'paid']"
                                @update:modelValue="updateInstallment(installment)"
                            />
                        </td>
                    </tr>
                </table>

                <table class="va-table" style="width: 100%">
                    <tr>
                        <td>
                            <va-input
                                label="Value"
                                v-model="installmentForm.value"
                                :error="!!installmentForm.errors.value"
                                :error-messages="installmentForm.errors.value"
                            />
                        </td>
                        <td>
                            <va-date-input
                                label="Due date"
                                v-model="installmentForm.due_date"
                                :error="!!installmentForm.errors.due_date"
                                :error-messages="installmentForm.errors.due_date"
                            />
                        </td>
                        <td>
                            <va-select
                                label="Status"
                                v-model="installmentForm.status"
                                :options="['pending', 'paid']"
                                :error="!!installmentForm.errors.status"
                                :error-messages="installmentForm.errors.status"
                            />
                        </td>
                        <td>
                            <va-button @click="installmentForm.post(route('installments.create', supplier.id))">
                                Add
                            </va-button>
                        </td>
                    </tr>
                </table>
            </div>

            <va-button type="submit" :loading="form.processing" color="success" class="mt-4">
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

        const installmentForm = useForm({
            value: '',
            status: 'pending',
            due_date: '',
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

        function updateInstallment(installment) {
            Inertia.put(route('installments.update', installment.id), {
                ...installment
            })
        }

        return { form, submit, removeFile, installmentForm, updateInstallment }
    }
}
</script>
