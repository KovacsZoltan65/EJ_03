<script setup>
    import {reactive, onMounted, watch, computed, ref} from 'vue';
    import axios from 'axios';
    
    //import { initFlowbite } from 'flowbite';

    import AppLayout from '../../Layouts/AppLayout.vue';
    //import BookForm from '../../Components/Book/form.vue';
    import DialogModal from '@/Components/DialogModal.vue';

    import VPagination from '@hennge/vue3-pagination';
    import '@hennge/vue3-pagination/dist/vue3-pagination.css';

    //import SecondaryButton from '@/Components/SecondaryButton.vue';
    //import PrimaryButton from '@/Components/PrimaryButton.vue';
    import DefaultButton from '../../Components/buttons/DefaultButton.vue';
    import GreenButton from '../../Components/buttons/GreenButton.vue';
    import RedButton from '../../Components/buttons/RedButton.vue';
    import LightButton from '../../Components/buttons/LightButton.vue';

    import SorterIcon from '../../Components/icons/SorterIcon.vue';

    //===============================
    // FILEPOND
    //===============================
    import vueFilePond from 'vue-filepond';
    import "filepond/dist/filepond.min.css";
    import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
    import 'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css';
    import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
    import FilePondPluginImagePreview from "filepond-plugin-image-preview";
    import FilePondPluginFilePoster from 'filepond-plugin-file-poster';

    const FilePond = vueFilePond(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginFilePoster
    );
    //===============================

    const local_storage_column_key = 'ln_books_grid_columns';

    const errors = ref('');

    const props = defineProps({
        can: {
            type: Object,
            default: () => ({}),
        }
    });

    const defaultFormObject = {
        title: null,
        author: null,
        image: null,
    };

    // Új könyv adatai
    const newBook = () => {
        return {
            id: null,
            title: null,
            author: null,
            image: null,
        };
    }
    
    const state = reactive({
        // Összes rekord
        Books: [],
        // Kiválasztott rekord
        Book: newBook(),
        // Szerkeszteni kívánt rekord
        editingBook: null,
        // Törölni kívánt rekord
        deletingBook: null,

        // Van nyitott ablak
        isFormOpen: false,
        // A folyamatban levő művelet szerkesztés
        isEdit: false,

        // "settings" modal megnyitása / bezárása
        showSettingsModal: false,
        // "edit" modal megnyitása / bezárása
        showEditModal: false,
        // "delete" modal megnyitása / bezárása
        showDeleteModal: false,

        // Kiválasztott rekordok azonosítója
        selected: [],
        // Összes elem ki van választva
        selectAll: false,
        myFiles: [],

        // Táblázat oszlopai
        columns: {
            id: {
                label: '#',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            title: {
                label: 'title',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            author: {
                label: 'author',
                is_visible: true,
                is_sortable: true,
                is_filterable: true,
            },
            image: {
                label: 'image',
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
        //initFlowbite();

        getBooks();

        let columns = localStorage.getItem(local_storage_column_key);
        
        //console.log('columns', columns);

        if (columns) {
            columns = JSON.parse(columns);
            for(const column_name in columns){
                state.columns[column_name] = columns[column_name];
            }
        }
    });
    
    const image_path = (image) => {
        console.log(image);
        return '/' + image;
    };

    const sordedBook = () => {
        return state.Books.sort((a, b) => {
            return a.title.localeCompare(b.title);
        });
    };

    const filteredBooks = () => {
        return state.Books.filter((book) => {
            return book.title.toLowerCase().includes(state.filters.search.toLowerCase());
        });
    };

    // Kiválasztás
    const select = () => {
        state.selected = [];
        if( !state.selectAll ){
            state.Books.forEach(book => {
                state.selected.push(book.id);
            });
        }
    };

    // Táblázat adatainak lekérése
    const getBooks = (page = state.pagination.current_page) => {
        axios.post(route('getBooks', {
            filters: state.filters,
            config: {
                per_page: state.pagination.per_page,
            },
            page
        }))
        .then(response => {
            state.Books = response.data.books.data;
            
            state.pagination.total_number_of_pages = response.data.books.last_page;
            state.pagination.current_page = response.data.books.current_page;
        });
    }

    // Új könyv előkészítése
    /**
     * Initialize the process of creating a new book.
     *
     * This function sets the `Book` property to a new book object,
     * clears the `editingBook` property, and sets the `isEdit` property to false.
     * It then opens the edit modal.
     *
     * @return {void}
     */
    const newBook_init = () => {
        // Set the `Book` property to a new book object
        state.Book = newBook();
        
        // Clear the `editingBook` property
        state.editingBook = null;
        
        // Set the `isEdit` property to false
        state.isEdit = false;
        
        // Open the edit modal
        openEditModal();
    }

    // Szerkesztés
    /**
     * Edit the book data and open the edit modal.
     *
     * This function sets the `editingBook` property to a copy of the book data passed in.
     * It then sets the `Book` property to the same data and sets the `isEdit` property to true.
     * Finally, it opens the edit modal.
     *
     * @param {Object} book - The book data to edit.
     */
    const editBook = (book) => {
        console.log('editBook', book);
        // Make a copy of the book data so we don't modify the original
        state.editingBook = JSON.parse(JSON.stringify(book));
        state.Book = state.editingBook;
        state.isEdit = true;

        openEditModal();
    }

    /**
     * Save the book data by either updating an existing book or storing a new book.
     *
     * This function checks if the book data has an ID. If it does, it calls the
     * `updateBook` function to update the book in the database. If it doesn't, it
     * calls the `storeBook` function to store a new book in the database.
     */
    const saveBook = () => {
        // Check if the book data has an ID.
        // If it does, call the updateBook function to update the book in the database.
        // If it doesn't, call the storeBook function to store a new book in the database.
        
        if( state.Book.id ){
            // Call the updateBook function to update the book in the database.
            updateBook(state.Book);
        }else{
            // Call the storeBook function to store a new book in the database.
            storeBook(state.Book);
        }
    }

    /**
     * Store a new book in the database and update the list of books in the state.
     *
     * @param {Object} book - The book data to be stored.
     */
    const storeBook = (book) => {
        // Log the book data being stored
        console.log('storeBook', book);

        // Send a POST request to the 'books_store' route with the book data
        axios.post( route('books_store'), book )
        .then(res => {
            // Log the response data from the server
            console.log('storeBook res.data', res.data);
<<<<<<< HEAD:resources/js/Pages/Books/booksIndex.vue
            // Add the new book to the list of books in the state
            state.Books.push(res.data);
=======
            //state.Books.push(res.data);
>>>>>>> eaf2ea419b9a128699d2f573423a3c685d873da8:resources/js/Pages/Books/booksIndex_01.vue

            // Close the edit modal
            closeEditModal();
        })
        .catch(error => {
            // Log any errors that occur during the request
            console.log('storeBook error', error);
        });
    }

    /**
     * Update the book data in the database and update the list of books in the state.
     *
     * @param {Object} book - The book data to be updated.
     */
    const updateBook = (book) => {
        
        axios.put(route('books_update', {book: book.id}), {
            title: state.editingBook.title,
            author: state.editingBook.author,
            image: state.editingBook.image,
        })
        .then(res => {
            // Find the index of the book in the state's books list that has the same id
            const index = state.Books.findIndex(
                (b) => b.id === res.data.id
            );
            // Update the book data in the state's books list
            if (index !== -1) {
                state.Books[index] = res.data;
            }
        })
        .catch(e => {
            console.log('updateBook error: ', e);
        });
    }

    // Új rekord mentése
    /*const storeBook = () => {
        console.log('storeBook', state.Book);
        
        if( state.Book.id == null ){
            saveBook();
        }else{
            updateBook();
        }
        errors.value = '';
        axios.post(route('books_store'), state.Book)
        .then(res => {
            //console.log('res', res);
            //state.Books.push(res.data.book);

            closeEditModal();
            getBooks();
        })
        .catch(e => {
            if( e.response.status == 422 ){
                console.log(e.response.data.errors);
                errors.value = e.response.data.errors;
            }
        });
    }

    // Szerkesztett adatok mentése
    /*const updateBook = () => {
        console.log('updateBook');
        errors.value = '';
        axios.put('books_update', {book: state.editingBook.id})
        .then(res => {
            //console.log('res', res);
            // 
            for(let i = 0; i < state.Books.length; i++){
                if(state.Books[i].id == res.data.id){
                    state.Books[i] = res.data;
                }
            }

            closeEditModal();
        })
        .catch(e => {
            if( e.response.status == 422 ){
                console.log('e', e.response.data.errors);
                errors.value = e.response.data.errors;
            }
        });
    }*/

    // Régi mentés rutin
    /*const saveBook = () => {
        console.log('saveBook');
        if(state.editingBook && state.editingBook.id){
            // Rekord frissítése
            axios.put(route('books_update', {book: state.editingBook.id}), {
                title: state.editingBook.title,
                author: state.editingBook.author,
                image: state.editingBook.image,
            })
            .then((res) => {
                //
                for(let i = 0; i < state.Books.length; i++){
                    if(state.Books[i].id === res.data.id){
                        state.Books[i] = res.data;
                    }
                }

                closeEditModal();
            })
            .catch((error) => {
                console.log('updateBook', error);
            });
        }else{
            // Rekord mentése
            axios.post(route('books_store'), {
                title: state.Book.title,
                author: state.Book.author,
                image: state.Book.image,
            })
            .then((response) => {
                //state.Book = newBook();
                state.Books.push(response.data.book);

                closeEditModal();
            })
            .catch((error) => {
                console.log('storeBook', error);
            });
        }

        cancelEdit();
        return;
    }*/

    // Törlés előkészítése
    const deleteBook_init = (book) => {
        state.editingBook = null;
        state.deletingBook = book;

        openDeleteModal();
    }

    // Rekord törlése
    const deleteBook = () => {

        axios.delete(route('books_delete', {book: state.deletingBook.id}))
        .then((response) => {
            state.Books = state.Books.filter(book => book.id !== state.deletingBook.id);
            state.deletingBook = null;

            openDeleteModal();
        })
        .catch((error) => {
            console.log('deleteBook', error);
        });
    }

    // Szerkesztés megszakítása
    const cancelEdit = () => {
        state.editingBook = null;
        state.Book = newBook();
    }

    // Beállítások előkészítése
    const settings_init = () => { openSettingsModal(); }
    // SETTINGS MODAL megnyitása
    const openSettingsModal = () => { state.showSettingsModal = true; }
    // SETTINGS MODAL bezárása
    const closeSettingsModal = () => { state.showSettingsModal = false; }
    
    // EDIT MODAL megnyitása
    const openEditModal = () => {
        state.showEditModal = true;
    }

    // EDIT MODAL bezárása
    const closeEditModal = () => {
        cancelEdit();
        state.showEditModal = false;
    }
    // DELETE MODAL megnyitása
    const openDeleteModal = () => { state.showDeleteModal = true; }
    // DELETE MODAL bezárása
    const closeDeleteModal = () => { state.showDeleteModal = false; }
    //
    const handleFilePondInit = () => {
        if(state.Book.image) {
            state.myFiles = [{
                source: '/' + state.Book.image,
                options: {
                    type: 'local',
                    metadata: {
                        poster: '/' + state.Book.image
                    }
                }
            }];
        }else{
            state.myFiles = [];
        }
    };

    const handleFilePondLoad = (response) => {
        state.Book.image = response;
    };

    const handleFilePondRemove = (source, load, error) => {
        this.form.image = '';
        load();
    };

    const handleFilePondRevert = (uniqueId, load, error) => {
        axios.post('/upload-books-revert', {
            image: state.Book.image
        });
        load();
    };

</script>
<template>
    <app-layout :title="$t('books')">

        <!-- header -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('books') }}
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

        <!-- Könyvek listája -->
        <div class="py-6">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

                    <!-- selected ids -->
                    <div class="text-uppercase text-bold mb-4 mt-4">
                        <div class="relative">
                            id selected: {{state.selected}}
                        </div>
                    </div>

                    <!-- TABLE AND SEARCH -->
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

                        <!-- TABLE -->
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr class="bg-gray-100">
                                    
                                    <!-- header checkbox -->
                                    <th scope="col" class="px-6 py-3" >
                                        <div>
                                            <input id="checkbox-all" 
                                                type="checkbox"
                                                v-model="state.selectAll"
                                                @click="select"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 
                                                    dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 
                                                    focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                            <label for="checkbox-all" 
                                                class="sr-only">checkbox</label>
                                        </div>
                                    </th>
                                    <!-- ID -->
                                    <th scope="col" class="px-6 py-3" 
                                        v-show="state.columns.id.is_visible">
                                        <div class="flex items-center">
                                            {{ state.columns.id.label }}
                                            <a href="#" v-show="state.columns.id.is_sortable">
                                                <SorterIcon/>
                                            </a>
                                        </div>
                                    </th>

                                    <!-- TITLE -->
                                    <th scope="col" class="px-6 py-3" v-show="state.columns.title.is_visible">
                                        <div class="flex items-center">
                                            {{ $t(state.columns.title.label) }}
                                            <a href="#" v-show="state.columns.title.is_sortable">
                                                <SorterIcon/>
                                            </a>
                                        </div>
                                    </th>

                                    <!-- AUTHOR -->
                                    <th scope="col" class="px-6 py-3" v-show="state.columns.author.is_visible">
                                        <div class="flex items-center">
                                            {{ $t(state.columns.author.label) }}
                                            <a href="#" v-show="state.columns.author.is_sortable">
                                                <SorterIcon/>
                                            </a>
                                        </div>
                                    </th>
                                    
                                    <!-- IMAGE -->
                                    <th scope="col" class="px-6 py-3" 
                                        v-show="state.columns.image.is_visible">
                                        <div class="flex items-center">
                                            {{ $t(state.columns.image.label) }}
                                            <a href="#" v-show="state.columns.image.is_sortable">
                                                <SorterIcon/>
                                            </a>
                                        </div>
                                    </th>

                                    <!-- ACTION -->
                                    <th scope="col" class="px-6 py-3" width="250px" v-show="state.columns.action.is_visible">
                                        <div class="flex items-center">
                                            {{ $t(state.columns.action.label) }}
                                            <a href="#" v-show="state.columns.action.is_sortable">
                                                <SorterIcon/>
                                            </a>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr v-for="book in state.Books">
                                    <td class="px-6 py-3 border">
                                        <div>
                                            <input :id="book.id" type="checkbox" :value="book.id" :key="book.id" v-model="state.selected" 
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 
                                                dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 
                                                focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                            <label class="sr-only" :for="book.id">checkbox</label>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border" v-show="state.columns.id.is_visible">{{ book.id }}</td>
                                    <td class="px-4 py-2 border" v-show="state.columns.title.is_visible">{{ book.title }}</td>
                                    <td class="px-4 py-2 border" v-show="state.columns.author.is_visible">{{ book.author }}</td>
                                    
                                    <!-- IMAGE -->
                                    <td class="px-4 py-2 border w-20" 
                                        v-show="state.columns.image.is_visible">
                                        <img v-if="book.image" 
                                             :src="image_path(book.image)" alt="" />
                                    </td>

                                    <td class="px-4 py-2 w-45 border" width="250px" 
                                        v-show="state.columns.action.is_visible">
                                        <div type="justify-start lg:justify-end" no-wrap>
                                            <green-button class="mt-1" size="text-xs" 
                                                          @click="editBook(book)">{{ $t('edit') }}</green-button>
                                            <red-button class="mt-1" size="text-xs" 
                                                        @click="deleteBook_init(book)">{{ $t('delete') }}</red-button>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>

                    <!-- pagination -->
                    <div class="mb-3 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto p-3 d-flex align-items-center justify-content-center">
                        <v-pagination v-model="state.pagination.current_page" 
                            :pages="state.pagination.total_number_of_pages"  
                            :range-size="state.pagination.range"
                            active-color="#DCEDFF"
                            @update:modelValue="getBooks"/>
                    </div>

                </div>
            </div>
        </div>

    </app-layout>

    <!-- EDIT MODAL -->
    <dialog-modal :show="state.showEditModal" id="edit_modal">
        <template #title>
            <!--<span v-if="state.editingBook && state.editingBook.id">Edit Book</span>
            <span v-else>Create Book</span>-->
            {{ state.isEdit ? $t('books_edit') : $t('books_new') }}
        </template>

        <template #content>
            <div class="grid gap-6 mb-6 md:grid-cols-2">

                <!-- hibák -->
                <div v-if="errors">
                    <div v-for="(v, k) in errors" :key="k" 
                        class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
                        <p v-for="error in v" :key="error" class="text-sm">
                            {{ error }}
                        </p>
                    </div>
                </div>

                <!-- TITLE -->
                <div>
                    <label for="title" 
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >{{ $t('title') }}</label>
                    <input type="text" id="title" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 
                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 
                                dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                            :placeholder="$t('title')" 
                            v-model="state.Book.title" required>
                            <span></span>
                </div>

                <!-- AUTHOR -->
                <div>
                    <label for="author" 
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >{{ $t('author') }}</label>
                    <input type="text" id="author" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 
                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 
                                dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                            :placeholder="$t('author')" 
                            v-model="state.Book.author" required>
                </div>

                <!-- IMAGE -->
                <!--<div>
                    <label for="image" 
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >{{ $t('image') }}</label>
                    <input type="text" id="image" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                            :placeholder="$t('image')" 
                            v-model="state.Book.image" required>
                </div>-->
                <div>
                    <label for="image" 
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >{{ $t('image') }}</label>
                    <file-pond name="imageFilepond" id="imageFilepond" 
                               ref="pond" v-bind:allow-multiple="false"
                               accepted-file-types="image/png, image/jpeg"
                               v-bind:server="{
                                    url: '',
                                    timeout: 7000,
                                    process: {
                                        url: '/upload-books',
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': $page.props.csrf_token
                                        },
                                        withCredentials: false,
                                        onload: handleFilePondLoad,
                                        onerror: (error) => {
                                            console.log(error);
                                        }
                                    },
                                    remove: handleFilePondRemove,
                                    revert: handleFilePondRevert
                               }"
                               v-bind:files="state.myFiles"
                               v-on:init="handleFilePondInit"
                    >
                    </file-pond>
                </div>

            </div>
        </template>

        <template #footer>
            
            <!-- CANCEL -->
            <light-button size="text-xs" type="button" 
                          @click="closeEditModal()"
            >{{ $t('cancel') }}</light-button>
            
            <!-- SAVE -->
            <green-button size="text-xs" type="button" 
                          @click="saveBook()"
            >{{ state.isEdit ? $t('books_update') : $t('books_create') }}</green-button>
        </template>

    </dialog-modal>

    <!-- CONFIRM DELETE MODAL -->
    <dialog-modal :show="state.showDeleteModal" id="delete_modal">
        <template #title>
            {{ $t('books_delete') }}
        </template>
        <template #content>
            {{ $t('books_delete_confirmation') }}
        </template>
        <template #footer>
        <!--
            <secondary-button @click="closeDeleteModal()">Cancel</secondary-button>
            <primary-button type="button" class="ml-3" @click="deleteBook()">Delete</primary-button>
        -->
            <light-button size="text-xs" type="button" @click="closeDeleteModal()">{{ $t('cancel') }}</light-button>
            <red-button size="text-xs" type="button" @click="deleteBook()">{{ $t('delete') }}</red-button>
        </template>
    </dialog-modal>

    <!-- SETTINGS MODAL -->
    <dialog-modal :show="state.showSettingsModal" id="settings_modal">
        <template #title>{{ $t('setup') }}</template>
        <template #content>
            <div v-for="(config, column) in state.columns" 
                :key="column" 
                class="d-flex align-items-center">
                <input v-model="config.is_visible" 
                    :id="column" class="me-3" type="checkbox" />
                <label :for="column">{{ $t(config.label) }}</label>
            </div>
        </template>
        <template #footer>
            <light-button size="text-xs" type="button" 
                          @click="closeSettingsModal()">{{ $t('back') }}</light-button>
        </template>
    </dialog-modal>

</template>