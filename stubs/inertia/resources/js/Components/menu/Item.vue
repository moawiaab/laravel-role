<template>
    <q-item clickable :active="route().current() == data.to ? true : false" dense active-class="bg-blue-1" @click="setRoute(data.to)" :id="data.to">
        <Link
            :href="route(data.to)"
            class="q-item row no-wrap q-link full-width q-px-xs"
        >
            <q-item-section avatar>
                <q-icon :name="data.icon || 'explore'" />
            </q-item-section>

            <q-item-section>
                {{ $t(data.text) }}
            </q-item-section>
        </Link>
    </q-item>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps(['data', 'id'])

const setRoute = (e) => {
    const eall = document.getElementsByClassName('q-item q-item-type row no-wrap')
    for (let index = 0; index < eall.length; index++) {
        eall[index].classList.remove('q-item--active');
        eall[index].classList.remove('bg-blue-1');
    }
    const el = document.getElementById(e)
   el.classList.add('q-item--active')
   el.classList.add('bg-blue-1')
}
</script>

<style></style>
