<script setup>
    import { reactive, onMounted, watch, ref } from 'vue';
    import { useForm } from '@inertiajs/vue3';

    import AppLayout from '@/Layouts/AppLayout.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import InputError from '@/Components/InputError.vue';
    import TextInput from '@/Components/TextInput.vue';
    import DefaultButton from '@/Components/buttons/DefaultButton.vue';
    import GreenButton from '@/Components/buttons/GreenButton.vue';
    //import GreenLink from '@/Components/linkbuttons/GreenLink.vue';
    import DefaultLink from '@/Components/linkbuttons/DefaultLink.vue';

    import { trans } from 'laravel-vue-i18n';
    import Swal from 'sweetalert2';
    import 'sweetalert2/dist/sweetalert2.min.css';

    const alerta = Swal.mixin({
        buttonStyling: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    });

    const save_alert = Swal.mixin({
        buttonStyling: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        icon: 'question'
    });

    // Tulajdonságok
    const props = defineProps({
        can: {
            type: Object,
            required: true,
        },
        person: {
            type: Object,
            required: true,
        }
    });

    const form = useForm({
        name: props.person.name, 
        email: props.person.email, 
        password: props.person.password, 
        language: props.person.language, 
        birthdate: props.person.birthdate

    });

    //onMounted(() => {
    //    console.log('Person:', props.person);
    //});

    const submit = () => {
        form.patch(route('persons_update', props.person.id),{
            onSuccess: (response) => {
                save_alert.fire({})
                .then((result) => {
                    if (result.isConfirmed) {
                        //
                    }else if( result.isDenied ) {
                        //
                    }else if( result.isDismissed ) {
                        //
                    }
                });
            },
            onFinish: (values) => {},
            onError: (errors) => {},
            preserveScroll: true
        });
    };

</script>

<template>
    <AppLayout :title="$t('persons.create')">

        <!-- HEADER -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('persons.create') }}
            </h2>
        </template>

        <!-- FORM -->
        <div class="py-6">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

                    <form @submit.prevent="submit">
                        
                        <div class="grid gap-6 mb-6 md:grid-cols-2">
                            
                            <div>
                                <InputLabel 
                                    for="name" 
                                    class="block mb-2 text-sm font-medium 
                                           text-gray-900 dark:text-white"
                                >{{ $t('name') }}</InputLabel>
                                <TextInput 
                                    v-model="form.person" type="text" 
                                    id="name" name="name" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 
                                            text-sm rounded-lg focus:ring-blue-500 
                                            focus:border-blue-500 block w-full p-2.5 
                                            dark:bg-gray-700 dark:border-gray-600 
                                            dark:placeholder-gray-400 dark:text-white 
                                            dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    placeholder="name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" 
                                            class="block mb-2 text-sm font-medium text-gray-900 
                                                   dark:text-white"
                                >{{ $t('email') }}</InputLabel>
                                <TextInput v-model="form.email"
                                    type="url" id="email" name="email" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 
                                            text-sm rounded-lg focus:ring-blue-500 
                                            focus:border-blue-500 block w-full p-2.5 
                                            dark:bg-gray-700 dark:border-gray-600 
                                            dark:placeholder-gray-400 dark:text-white 
                                            dark:focus:ring-blue-500 
                                            dark:focus:border-blue-500" 
                                    placeholder="email" required />
                                <InputError :message="form.errors.email" />
                            </div>

                        </div>

                        <div class="grid gap-6 mb-6 md:grid-cols-3">
                            
                            <div>
                                <InputLabel for="name" 
                                    class="block mb-2 text-sm font-medium 
                                           text-gray-900 dark:text-white"
                                >{{ $t('name') }}</InputLabel>
                                <TextInput 
                                    v-model="form.person" type="text" 
                                    id="name" name="name" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 
                                            text-sm rounded-lg focus:ring-blue-500 
                                            focus:border-blue-500 block w-full p-2.5 
                                            dark:bg-gray-700 dark:border-gray-600 
                                            dark:placeholder-gray-400 dark:text-white 
                                            dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    placeholder="name" required />
                                <InputError />
                            </div>

                            <div>
                                <InputLabel for="name" 
                                    class="block mb-2 text-sm font-medium 
                                           text-gray-900 dark:text-white"
                                >{{ $t('name') }}</InputLabel>
                                <TextInput v-model="form.person" type="text" 
                                    id="name" name="name" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 
                                            text-sm rounded-lg focus:ring-blue-500 
                                            focus:border-blue-500 block w-full p-2.5 
                                            dark:bg-gray-700 dark:border-gray-600 
                                            dark:placeholder-gray-400 dark:text-white 
                                            dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    placeholder="name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel 
                                    for="name" 
                                    class="block mb-2 text-sm font-medium 
                                           text-gray-900 dark:text-white"
                                >{{ $t('name') }}</InputLabel>
                                <TextInput 
                                    v-model="form.person" type="text" 
                                    id="name" name="name" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 
                                            text-sm rounded-lg focus:ring-blue-500 
                                            focus:border-blue-500 block w-full p-2.5 
                                            dark:bg-gray-700 dark:border-gray-600 
                                            dark:placeholder-gray-400 dark:text-white 
                                            dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    placeholder="name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                        </div>

                        <!-- "Submit" button -->
                        <GreenButton type="submit" 
                            size="text-base" :title="$t('save')"
                        >{{ $t('save') }}</GreenButton>

                        <!-- "Cancel" button -->
                        <DefaultLink type="button" class="float-right" 
                            :href="route('persons')" :title="$t('back')"
                        >{{ $t('back') }}</DefaultLink>
                    </form>

                </div>
            </div>
        </div>

    </AppLayout>
</template>