<template>
    <div class="layout gutter--lg">
        <div class="mb-4 d-flex align--center justify--space-between">
            <h1 class="display-1">{{ event.title }}</h1>
            <Link :href="route('events.edit', event.id)">
                <va-button icon="edit">Edit</va-button>
            </Link>
        </div>

        <div class="row">
            <div class="flex md3">
                <va-card>
                    <va-card-title>Info</va-card-title>

                    <va-card-content>
                        <ul>
                            <li>Budget: {{ event.budget }}</li>
                            <li>Date: {{ event.attending_date }}</li>
                        </ul>
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title class="justify--space-between">
                        Assignees
                        <va-button-dropdown icon="add" size="small" v-if="assignableMembers.length > 0">
                            <va-list fit class="py-0">
                                <va-list-item href="#" v-for="user in assignableMembers" :key="user.id" @click="assign(user.id)">
                                    <va-list-item-section avatar>
                                        <va-avatar size="small">
                                            <va-icon name="warning" size="small" />
                                        </va-avatar>
                                    </va-list-item-section>

                                    <va-list-item-section>
                                        <va-list-item-label>
                                            {{ user.name }}
                                        </va-list-item-label>
                                    </va-list-item-section>
                                </va-list-item>
                            </va-list>
                        </va-button-dropdown>
                    </va-card-title>

                    <va-card-content>
                        <div class="d-flex align--center justify--space-between" v-for="user in event.assignees" :key="user.id">
                            <div>
                                <va-avatar size="small">
                                    <va-icon name="warning" size="small" />
                                </va-avatar>

                                {{ user.name }}
                            </div>

                            <va-button icon="delete" size="small" color="danger" @click="remove(user.id)" />
                        </div>
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Financing</va-card-title>

                    <va-card-content>
                        <p class="mb-2 text--bold text--right">{{ expenses }} / {{ event.budget }}</p>
                        <va-progress-bar :model-value="(expenses / event.budget) * 100" />

                        <p class="my-2 text--bold text--right">{{ paid }} / {{ expenses }}</p>
                        <va-progress-bar color="success" :model-value="(paid / expenses) * 100" />
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Guests</va-card-title>

                    <va-card-content>
                        <p class="mb-2 text--bold text--right">
                            {{ confirmedGuests }} / {{ event.guests.length }}
                        </p>
                        <va-progress-bar :model-value="(confirmedGuests / event.guests.length) * 100" />
                    </va-card-content>
                </va-card>
            </div>
        </div>

        <va-tabs v-model="tab" grow class="mt-4">
            <template #tabs>
                <va-tab
                    v-for="title in ['Suppliers', 'Agenda', 'Guests']"
                    :name="title"
                    :key="title"
                    active
                >
                    {{ title }}
                </va-tab>
            </template>
        </va-tabs>

        <div class="mt-4">
            <div v-if="tab === 'Guests'">
                <div class="d-flex align--center justify--space-between">
                    <va-input v-model="guest" placeholder="Search for a guest" class="mr-4 grow" clearable>
                        <template #prependInner>
                            <va-icon name="search" />
                        </template>
                    </va-input>

                    <va-button icon="add" size="small" @click="guestModal.open" />
                </div>

                <va-modal v-model="guestModal.visible.value" size="small" title="Invite guest" hide-default-actions @cancel="editGuest = null">
                    <form @submit.prevent="saveGuest">
                        <va-input
                            v-model="guestForm.name"
                            label="Name"
                            class="mb-4"
                            :error="!!guestForm.errors.name"
                            :error-messages="guestForm.errors.name"
                        />

                        <va-input
                            v-model="guestForm.email"
                            label="Email"
                            class="mb-4"
                            :error="!!guestForm.errors.email"
                            :error-messages="guestForm.errors.email"
                        />

                        <va-select
                            v-model="guestForm.relation"
                            label="Relation"
                            class="mb-4"
                            :options="relations"
                            :error="!!guestForm.errors.relation"
                            :error-messages="guestForm.errors.relation"
                        />

                        <va-select
                            v-if="editGuest"
                            v-model="guestForm.status"
                            label="Status"
                            class="mb-4"
                            :options="['pending', 'confirmed', 'refused']"
                            :error="!!guestForm.errors.status"
                            :error-messages="guestForm.errors.status"
                        />

                        <va-button type="submit" :loading="guestForm.processing">
                            Invite guest
                        </va-button>
                    </form>
                </va-modal>

                <va-data-table
                    :filter="guest"
                    :items="event.guests"
                    :columns="[
                        { key: 'name', sortable: true },
                        { key: 'email' },
                        { key: 'relation', sortable: true },
                        { key: 'status', sortable: true },
                        { key: 'actions' },
                    ]"
                >
                    <template #cell(actions)="{ rowIndex }">
                        <va-button flat icon="edit" @click="editGuest = event.guests[rowIndex]" />
                        <va-button flat icon="delete" />
                    </template>
                </va-data-table>
            </div>

            <div v-if="tab === 'Suppliers'">
                <div class="text-right">
                    <va-button icon="add" size="small" @click="categoryModal.open" />
                </div>

                <va-modal v-model="categoryModal.visible.value" size="small" title="Add category" hide-default-actions>
                    <form @submit.prevent="addCategory">
                        <va-select
                            v-model="categoryForm.category"
                            :options="categories"
                            text-by="name"
                            value-by="id"
                            track-by="id"
                            label="Category"
                            class="mb-4"
                            :error="!!categoryForm.errors.category"
                            :error-messages="categoryForm.errors.category"
                        />

                        <va-input
                            v-model="categoryForm.budget"
                            label="Budget"
                            class="mb-4"
                            :error="!!categoryForm.errors.budget"
                            :error-messages="categoryForm.errors.budget"
                        />

                        <va-button type="submit" :loading="categoryForm.processing">
                            Add category
                        </va-button>
                    </form>
                </va-modal>

                <va-list>
                    <Category
                        v-for="category in event.categories"
                        :key="category.id"
                        :category="category"
                        :event="event"
                        :suppliers="suppliers"
                    />
                </va-list>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { Link, useForm } from '@inertiajs/inertia-vue3'
import useModal from '../../composables/useModal'
import Category from './Category.vue'

export default {
    props: ['event', 'members', 'suppliers', 'categories', 'relations'],
    components: {
        Link,
        Category,
    },
    setup(props) {
        const guest = ref('')
        const editGuest = ref(null)
        const tab = ref('Suppliers')

        const guestModal = useModal()
        const categoryModal = useModal()

        const categoryForm = useForm({
            category: '',
            budget: '',
        })

        const guestForm = useForm({
            name: '',
            email: '',
            status: '',
            relation: '',
        })

        watch(() => editGuest.value, (current, previous) => {
            if (!current) {
                guestForm.reset()
                guestModal.close()
            } else {
                guestForm.name = current.name
                guestForm.email = current.email
                guestForm.status = current.status
                guestForm.relation = current.relation

                guestModal.open()
            }
        })

        function remove(assignee) {
            Inertia.delete(route('assignees.remove', {
                event: props.event.id,
                assignee: assignee,
            }))
        }

        function assign(assignee) {
            Inertia.post(route('assignees.attach', props.event.id), {
                assignee,
            });
        }

        const hired = computed(() => {
            let suppliers = []
            props.event.categories.forEach(category => {
                suppliers = [
                    ...suppliers,
                    ...category.suppliers.filter(supplier => {
                        return supplier.status === 'hired'
                    })
                ]
            })
            return suppliers
        })

        const expenses = computed(() => {
            return hired.value.reduce((total, supplier) => {
                return total + parseFloat(supplier.value)
            }, 0)
        })

        const paid = computed(() => {
            return hired.value.reduce((total, supplier) => {
                return total + supplier.installments.reduce((total, installment) => {
                    if (installment.status === 'paid') {
                        return total + parseFloat(installment.value)
                    }
                    return total
                }, 0)
            }, 0)
        });

        function addCategory() {
            categoryForm.clearErrors();
            categoryForm.post(route('categories.attach', props.event.id))
        }

        const confirmedGuests = computed(() => {
            return props.event.guests.filter(guest => guest.status == 'confirmed').length
        })

        const assignableMembers = computed(() => props.members.filter(member => {
            return !props.event.assignees.find(assignee => assignee.id === member.id)
        }));

        function saveGuest() {
            if (editGuest.value) {
                guestForm.put(route('guests.update', editGuest.value.id), {
                    preserveScroll: true,
                    onSuccess: editGuest.value = null
                })
            } else {
                guestForm.post(route('guests.invite', props.event.id), {
                    preserveScroll: true,
                    onSuccess: guestModal.close
                })
            }
        }

        return {
            tab,
            remove,
            assign,
            paid,
            guest,
            editGuest,
            expenses,
            guestModal,
            categoryModal,
            categoryForm,
            saveGuest,
            guestForm,
            addCategory,
            confirmedGuests,
            assignableMembers,
        }
    },
}
</script>
