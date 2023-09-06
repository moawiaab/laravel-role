<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ListMenu from "@/Components/menu/ListMenu.vue";

const logout = () => {
    router.post(route("logout"));
};
const drawerLeft = ref(Screen.width > 702 ? true : false);
</script>

<template>
    <q-layout
        view="lhH LpR lff"
        container
        style="height: 100vh"
        class="bg-white"
    >
        <q-header class="bg-grey-1 text-grey-8 q-py-xs">
            <q-toolbar>
                <q-btn
                    flat
                    @click="drawerLeft = !drawerLeft"
                    round
                    dense
                    icon="menu"
                />
                <q-space />
                <q-btn
                    flat
                    @click="logout()"
                    round
                    dense
                    icon="logout"
                    color="red"
                />
            </q-toolbar>
        </q-header>
        <q-drawer
            v-model="drawerLeft"
            :width="260"
            :breakpoint="700"
            bordered
            show-if-above
            class="bg-grey-2"
        >
            <div
                class="row justify-center q-px-md q-pt-md fixed-top"
                style="background: #e3f2fd; z-index: 8; height: 192px"
                v-if="$page.props.jetstream.managesProfilePhotos"
            >
                <q-avatar size="100px" style="border: 2px solid white">
                    <q-img
                        :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name"
                        fit="cover"
                        width="100%"
                        height="100%"
                    />
                </q-avatar>
                <q-item class="bg-light-blue-1 q-pt-sm" dense>
                    <Link
                        :href="route('profile.show')"
                        class="q-item row no-wrap q-link full-width q-px-xs"
                    >
                        <q-item-section class="q-pa-sm">
                            <q-item-label>
                                مرحباً :
                                {{ $page.props.auth.user.name }}</q-item-label
                            >
                            <q-item-label caption>
                                <q-item-section>
                                    البريد : {{ $page.props.auth.user.email }}
                                </q-item-section>
                            </q-item-label>
                        </q-item-section>
                    </Link>
                </q-item>
            </div>
            <q-separator />
            <!-- <q-scroll-area class="fit"> -->
            <q-list separator class="q-mb-xl" style="padding-top: 192px">
                <list-menu />
            </q-list>
            <!-- </q-scroll-area> -->
            <q-item
                v-ripple
                class="text-red footer bg-white full-width fixed-bottom"
                clickable
                @click="auth.logout()"
            >
                <q-item-section avatar>
                    <q-icon name="logout" />
                </q-item-section>

                <q-item-section> تسجيل خروج </q-item-section>
            </q-item>
        </q-drawer>

        <q-page-container>
            <transition
                appear
                enter-active-class="animated zoomIn"
                leave-active-class="animated zoomOut"
                mode="out-in"
            >
                <main
                    :key="$page.url"
                    class="container p-4 mx-auto mt-[60px] relative"
                >
                    <slot />
                </main>
            </transition>

            <!-- </q-page> -->

            <q-page-scroller position="bottom-right">
                <q-btn
                    fab
                    icon="keyboard_arrow_up"
                    color="primary"
                    class="glossy"
                />
            </q-page-scroller>
        </q-page-container>
    </q-layout>
</template>
