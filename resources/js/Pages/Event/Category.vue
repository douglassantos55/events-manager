<template>
    <va-list-item>
        <va-list-item-section>
            <va-list-item-label>
                {{ category.name }} - {{ category.pivot.budget }}
            </va-list-item-label>
        </va-list-item-section>

        <va-list-item-section>
            <va-list-item-label>
                <va-button icon="add" color="success" size="small" class="mr-2" @click="$emit('add-supplier', category.id)" />
                <va-button icon="delete" color="danger" size="small" @click="removeCategory" />
            </va-list-item-label>
        </va-list-item-section>
    </va-list-item>

    <va-divider />

    <va-list class="pt-0">
        <va-list-item v-if="suppliers.length == 0">
            <va-list-item-section>
                <va-list-item-label class="text--secondary">
                    No suppliers registered for this category
                </va-list-item-label>
            </va-list-item-section>
        </va-list-item>

        <va-list-item v-for="supplier in suppliers" :key="supplier.id" v-else>
            <va-list-item-section>
                <va-list-item-label>
                    {{ supplier.name }} - {{ supplier.pivot.value }}
                </va-list-item-label>
            </va-list-item-section>

            <va-list-item-section>
                <va-list-item-label>
                    <va-button icon="delete" color="danger" size="small" @click="removeSupplier(supplier.id)" />
                </va-list-item-label>
            </va-list-item-section>
        </va-list-item>
    </va-list>

</template>

<script>
import { computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'

export default {
    props: ['event', 'category'],
    emits: ['add-supplier'],
    setup(props) {
        const suppliers = computed(() => {
            return props.event.suppliers.filter(supplier => supplier.category_id === props.category.id)
        })

        function removeCategory() {
            Inertia.delete(route('categories.detach', {
                event: props.event.id,
                category: props.category.id,
            }));
        }

        function removeSupplier(supplier) {
            Inertia.delete(route('suppliers.detach', {
                event: props.event.id,
                supplier: supplier,
            }));
        }

        return { suppliers, removeCategory, removeSupplier }
    }
}
</script>
