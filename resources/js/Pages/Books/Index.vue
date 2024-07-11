<script setup>

    /**
     * $page.props.csrf_token
    */

    import { onMounted, ref, reactive } from 'vue';
    import axios from 'axios';

    import AppLayout from '@/Layouts/AppLayout.vue';
    import DefaultButton from '@/Components/buttons/DefaultButton.vue';
    import GreenButton from '@/Components/buttons/GreenButton.vue';
    import RedButton from '../../Components/buttons/RedButton.vue';

    const local_storage_column_key = 'ln_books_grid_columns';

    const errors = ref('');

    const props = defineProps({
        can: {
            type: Object,
            default: () => ({}),
        }
    });

    const state = reactive({
        Books: [],
        Book: null,

        editingBook: null,
        deletingBook: null,
        isEdit: false,
        selectAll: false,
        selected: [],

        // Táblázat oszlopai
        columns: {
            id:     {label: '#', is_visible: true, is_sortable: true, is_filterable: true,},
            title:  {label: 'title', is_visible: true,is_sortable: true,is_filterable: true,},
            author: {label: 'author', is_visible: true, is_sortable: true, is_filterable: true,},
            image:  {label: 'image', is_visible: true, is_sortable: true, is_filterable: true,},
            action: {label: 'actions', is_visible: true, is_sortable: false, is_filterable: false,},
        },

        pagination: {
            current_page: 1,
            total_number_of_pages: 0,
            per_page: 10,
            range: 5,
        },
        
        filters: {
            tags: [],
            search: null,
            column: null,
            direction: null,
        },
    });

    onMounted(() => {
        getBooks();
    });

    // Új elem felvitelének előkészítése
    const create_init = () => {
        state.Book = newBook();
        state.editingBook = null;
        state.isEdit = false;
    };

    // Szerkesztés előkészítése
    const edit_init = () => {};

    // Törlés előkészítése
    const delete_init = (book) => {
        state.editingBook = null;
        state.deletingBook = book;
    };

    // Szerkesztés megszakítása
    const cancelEdit = () => {
        state.editingBook = null;
        state.Book = newBook();
    };

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
            // Add the new book to the list of books in the state
            state.Books.push(res.data);

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

    const deleteBook_init = (book) => {
        state.editingBook = null;
        state.deletingBook = book;

        openDeleteModal();
    }

    // Törlés
    const deleteBook = () => {
        axios.delete(route('books_delete', {book: state.deletingBook.id}))
        .then(response => {
            state.Books = state.Books.filter(book => book.id !== state.deletingBook.id);
            state.deletingBook = null;
        })
        .catch(error => {
            console.log('deleteBook error', error);
        });
    };

    // Visszaállítás
    const restoreBook = () => {};

    // ==============================
    // MODAL KEZELÉS
    // ==============================
    const getBooks = (page = state.pagination.current_page) => {

        let url = 'getBooks';

        axios.post(route(url, {
            filters: state.filters,
            config: {
                per_page: state.pagination.per_page,
            },
            page
        }))
        .then(response => {
            //console.log(res.data.books);
            state.Books = response.data.books.data;
            
            state.pagination.total_number_of_pages = response.data.books.last_page;
            state.pagination.current_page = response.data.books.current_page;
        })
        .catch(error => {
            console.log(error);
        });
    };

    const settings_init = () => { openSettingsModal(); }
    const openSettingsModal = () => { state.showSettingsModal = true; }
    const closeSettingsModal = () => { state.showSettingsModal = false; }

    const openEditModal = () => { state.showEditModal = true; }
    const closeEditModal = () => {
        cancelEdit();
        state.showEditModal = false;
    }

    const openDeleteModal = () => { state.showDeleteModal = true; }
    const closeDeleteModal = () => { state.showDeleteModal = false; }

    // ==============================
    // FÁJL FELTÖLTÉS
    // ==============================
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
    <AppLayout :title="$t('books')">

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
                                            <green-button class="mt-1" size="text-xs">{{ $t('edit') }}</green-button>
                                            <red-button class="mt-1" size="text-xs">{{ $t('delete') }}</red-button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>

    </AppLayout>
</template>