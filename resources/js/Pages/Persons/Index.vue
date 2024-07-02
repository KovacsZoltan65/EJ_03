<script setup>
    import {reactive, onMounted, watch, computed, ref} from 'vue';
    import axios from 'axios';

    import AppLayout from '../../Layouts/AppLayout.vue';
</script>

<template>
    <app-layout :title="$t('persons')">

        <!-- header -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('persons') }}
            </h2>
        </template>

    </app-layout>
</template>