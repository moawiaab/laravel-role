<script setup>
import Layout from "../Layouts/AuthLayout.vue";

defineOptions({ layout: Layout });
const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    auth: Object,
});

</script>

<template>
    <q-page padding="">
        <pre>
            {{ $q.lang }}
        </pre>
    </q-page>
</template>
