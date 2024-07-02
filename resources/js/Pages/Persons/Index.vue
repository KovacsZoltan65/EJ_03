<script setup>
    import {reactive, onMounted, watch, computed, ref} from 'vue';
    import axios from 'axios';

    import AppLayout from '../../Layouts/AppLayout.vue';

    import VPagination from '@hennge/vue3-pagination';
    import '@hennge/vue3-pagination/dist/vue3-pagination.css';

    import DefaultButton from '@/Components/buttons/DefaultButton.vue';
    import GreenButton from '@/Components/buttons/GreenButton.vue';
    import RedButton from '@/Components/buttons/RedButton.vue';
    import LightButton from '@/Components/buttons/RedButton.vue';

    import SorterIcon from '@/Components/icons/SorterIcon.vue';

    import '../../modal.js';

    const local_storage_column_key = 'ln_persons_grid_columns';

    const errors = ref('');

    const props = defineProps({
        can: {
            type: Object,
            default: () => ({}),
        }
    });

    const defaultFormObject = {
        name: null,
        email: null,
        password: null,
        language: null,
        birthdate: null,
    };

    const newPerson = () => {
        return {
            name: null,
            email: null,
            password: null,
            language: null,
            birthdate: null,
        };
    };

    const state = reactive({
        Persons: [],
        Person: newPerson(),
        editingPerson: null,
        deletingBook: null,
        isFormOpen: false,
        isEdit: false,
        showSettingsModal: false,
        showEditModal: false,
        showDeleteModal: false,
        selected: [],
        selectAll: false,
        columns: {
            id: {
                label: '#',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            name: {
                label: 'name',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            email: {
                label: 'email',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            language: {
                label: 'language',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            birthdate: {
                label: 'birthdate',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            action: {
                label: 'actions',
                is_visible: true,
                is_sortable: false,
                is_filterable: false,
            },
        },

        // Oldaltörés
        pagination: {
            current_page: 1,
            total_number_of_pages: 0,
            per_page: 10,
            range: 5,
        },
        // Szűrés és keresés
        filters: {
            tags: [],
            search: null,
            column: null,
            direction: null,
        },
    });

    // Figyeli az oszlopok változását
    watch(state.columns, (new_value, old_value) => {
        //console.log(new_value);
        localStorage.setItem(local_storage_column_key, JSON.stringify(new_value));
    });

    onMounted(async () => {
        getPersons();

        let columns = localStorage.getItem(local_storage_column_key);

        if(columns){
            columns = JSON.parse(columns);
            for(const column_name in columns){
                state.columns[column_name] = columns[column_name];
            }
        }
    });

    const sortedPersons = () => {
        return state.Persons.sort((a, b) => {
            return a.name.localeCompare(b.name);
        });
    };

    const filteredPersons = () => {
        return state.Persons.filter((person) => {
            return person.name.toLowerCase().includes(state.filters.search.toLowerCase());
        });
    };

    const select = () => {
        state.selected = state.selectAll 
            ? state.Persons.map(person => person.id) 
            : [];
    };

    const getPersons = (page = state.pagination.current_page) => {
        axios.post(route('getPersons', {
            filter: state.filter,
            config: {
                per_page: state.pagination.per_page,
            }, page
        }))
        .then(response => {
            state.Persons = response.data.persons.data; 
            state.pagination.total_number_of_pages = response.data.persons.last_page; 
            state.pagination.current_page = response.data.persons.current_page;
        });
    };

    const newPerson_init = () => {
        state.Person = newPerson();
        state.editingPerson = null;
        state.isEdit = false;
        
        openEditModal();
    };

    const deletePerson_init = (person) => {
        state.editingPerson = null;
        state.deletingPerson = person;

        openDeleteModal();
    };

    const cancelEdit = () => {
        state.editingPerson = null;
        state.Person = newPerson();
    };

    const editPerson = (person) => {
        state.editPerson = JSON.parse(JSON.stringify(person));
        state.Person = state.editPerson;
        state.isEdit = true;

        openEditModal();
    };
</script>

<template>
    <app-layout :title="$t('persons')">

        <!-- header -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('persons') }}
            </h2>
        </template>

        <!-- Új elem felvitelle -->
        <div class="py-6" style="padding-bottom: 0px;">

            <!-- Új elem -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                    <div class="flex justify-between items=center">

                        <!-- FELIRAT -->
                        <div class="flex space-x-2 items-center">
                            {{ $t('books_description') }}
                        </div>

                        <!-- GOMBOK -->
                        <div class="flex space-x-2 items-center">
                            <default-button size="text-base" 
                                            @click="settings_init"
                            >{{ $t('setup') }}</default-button>
                            <green-button @click="newBook_init">+ {{ $t('books_new') }}</green-button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                    
                    <!-- selected ids -->
                    <div class="text-uppercase text-bold mb-4 mt-4">
                        <div class="relative">
                            id selected: {{state.selected}}
                        </div>
                    </div>

                    <!-- KERESÉS ÉS TÁBLÁZAT -->
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">

                        <!-- SEARCH -->
                        <div class="pb-4 bg-white dark:bg-gray-900">
                            <div class="relative mt-1 ml-10 mr-10">

                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" 
                                        aria-hidden="true" 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        fill="none" 
                                        viewBox="0 0 20 20">
                                        <path stroke="currentColor" 
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2" 
                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>

                                <!-- search field -->
                                <input type="search" id="default-search" 
                                    class="block w-full p-4 pl-10 
                                        text-sm text-gray-900 border 
                                        border-gray-300 rounded-lg bg-gray-50 
                                        focus:ring-blue-500 focus:border-blue-500 
                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    :placeholder="$t('books_search_placeholder')" 
                                    v-model="state.filters.search" 
                                    required>
                                <!-- search button -->
                                <button type="submit" 
                                    class="text-white absolute right-2.5 bottom-2.5 bg-blue-700 
                                        hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 
                                        font-medium rounded-lg text-sm px-4 py-2 
                                        dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                    @click="getBooks()"
                                >{{ $t('search') }}</button>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </app-layout>
</template>