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
                        <p class="mb-2 text--bold text--right">$ 1350 / $ 10000</p>
                        <va-progress-bar :model-value="50" />
                    </va-card-content>
                </va-card>
            </div>

            <div class="flex md3">
                <va-card>
                    <va-card-title>Guests</va-card-title>

                    <va-card-content>
                        <p class="mb-2 text--bold text--right">135 / 300</p>
                        <va-progress-bar :model-value="50" />
                    </va-card-content>
                </va-card>
            </div>
        </div>

        <va-tabs v-model="tab" grow class="mt-4">
            <template #tabs>
                <va-tab
                    v-for="title in ['Dashboard', 'Suppliers', 'Agenda', 'Guests']"
                    :name="title"
                    :key="title"
                    active
                >
                    {{ title }}
                </va-tab>
            </template>
        </va-tabs>

        <div class="mt-4">
            <div v-if="tab === 'Suppliers'">
                <div class="d-flex justify--space-between align--center">
                    <h2 class="display-3">Suppliers</h2>

                    <va-button icon="add" size="small" @click="showCategoryModal = !showCategoryModal" />

                    <va-modal v-model="showCategoryModal" size="small" title="Add category" hide-default-actions>
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
                </div>

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
import { ref, computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { Link, useForm } from '@inertiajs/inertia-vue3'
import Category from './Category.vue'

export default {
    props: ['event', 'members', 'suppliers', 'categories'],
    components: {
        Link,
        Category,
    },
    setup(props) {
        const tab = ref('Dashboard')
        const showCategoryModal = ref(false)

        const categoryForm = useForm({
            category: '',
            budget: '',
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

        function addCategory() {
            categoryForm.clearErrors();
            categoryForm.post(route('categories.attach', props.event.id), {
                onSuccess: () => (showCategoryModal.value = false)
            })
        }

        const assignableMembers = computed(() => props.members.filter(member => {
            return !props.event.assignees.find(assignee => assignee.id === member.id)
        }));

        return {
            tab,
            remove,
            assign,
            categoryForm,
            addCategory,
            assignableMembers,
            showCategoryModal,
        }
    },
}
</script>
