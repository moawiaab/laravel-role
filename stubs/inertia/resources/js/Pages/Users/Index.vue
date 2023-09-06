<script setup>
import { Users } from "@/types/columns";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const filter = ref("");
defineProps({
    users: Object,
});

watch(
    filter,
    (e) => {
        router.get("/users", { s: e });
    },
    true
);
</script>
<template>
    <q-page>
        <data-table :columns="Users" :rows="users.data" router="users">
        </data-table>
    </q-page>
</template>
