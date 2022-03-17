<template>
    <va-list-item>
        <va-list-item-section>
            <va-list-item-label>
                {{ category.name }} - {{ category.budget }}
            </va-list-item-label>
        </va-list-item-section>

        <va-list-item-section>
            <va-list-item-label>
                <va-button icon="add" color="success" size="small" class="mr-2" @click="supplierModal.open" />
                <va-button icon="delete" color="danger" size="small" @click="removeCategory" />
            </va-list-item-label>
        </va-list-item-section>
    </va-list-item>

    <va-divider />

    <va-list class="pt-0">
        <va-list-item v-if="category.suppliers.length == 0">
            <va-list-item-section>
                <va-list-item-label class="text--secondary">
                    No suppliers registered for this category
                </va-list-item-label>
            </va-list-item-section>
        </va-list-item>

        <va-list-item v-for="supplier in category.suppliers" :key="supplier.id" v-else>
            <va-list-item-section>
                <va-list-item-label>
                    {{ supplier.name }} - {{ supplier.value }} - {{ supplier.status }}
                </va-list-item-label>
            </va-list-item-section>

            <va-list-item-section>
                <va-list-item-label>
                    <va-button icon="edit" size="small" class="mr-2" @click="() => {selectedSupplier = supplier; supplierModal.open()}" />
                    <va-button icon="delete" color="danger" size="small" @click="removeSupplier(supplier.id)" />
                </va-list-item-label>
            </va-list-item-section>
        </va-list-item>
    </va-list>

    <supplier-modal
        :event="event"
        :category="category"
        :suppliers="category.all_suppliers"
        :supplier="selectedSupplier"
        v-model="supplierModal.visible.value"
    />
</template>

<script>
import { ref, watch, computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import SupplierModal from './SupplierModal.vue'
import useModal from '../../composables/useModal'

export default {
    props: ['event', 'category', 'suppliers'],
    components: {
        SupplierModal,
    },
    setup(props) {
        const selectedSupplier = ref(null)
        const supplierModal = useModal()

        watch(() => props.category, () => {
            if (selectedSupplier.value) {
                selectedSupplier.value = props.category.suppliers.find(sup => sup.id == selectedSupplier.value.id)
            }
        })

        watch(() => supplierModal.visible.value, (current, previous) => {
            if (previous && !current) {
                selectedSupplier.value = null
            }
        })

        function removeCategory() {
            Inertia.delete(route('categories.detach', props.category.id), {
                preserveScroll: true,
            });
        }

        function removeSupplier(supplier) {
            Inertia.delete(route('suppliers.detach', supplier), {
                preserveScroll: true,
            });
        }

        return {
            removeCategory,
            removeSupplier,
            supplierModal,
            selectedSupplier,
        }
    }
}
</script>
