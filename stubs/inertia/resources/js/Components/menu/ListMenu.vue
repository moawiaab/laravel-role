<template>
    <template v-for="(item, index) in items" :key="index">
        <list-item
            v-if="item.children && $page.props.auth.can.includes(`${item.access}_access`)"
            :label="$t(item.text)"
            :icon="item.icon"
            :default-opened="item.to.includes(route().current())"
            >
            <!-- item.to.includes($route.path) -->
            <template v-for="(i, id) in item.children" :key="id">
                <item
                    v-if="$page.props.auth.can.includes(`${i.access}_access`)"
                    :data="i"
                />
            </template>
        </list-item>
        <template v-else>
            <item
                v-if="$page.props.auth.can.includes(`${item.access}_access`)"
                :data="item"
            />
        </template>
    </template>
</template>

<script setup>
import Item from "./Item.vue";
import ListItem from "./ListItem.vue";

const items = [
    {
        text: "g.dashboard",
        icon: "mdi-home-outline",
        to: "dashboard",
        access: "dashboard",
    },
    {
        text: "item.account",
        icon: "mdi-source-branch",
        to: "accounts.index",
        access: "account",
    },
    {
        text: "item.user_management",
        icon: "mdi-account-cog-outline",
        to: ["users.index", "roles.index", "permissions.index"],
        access: "user_management",
        children: [
            {
                text: "item.user",
                icon: "mdi-account-details-outline",
                to: "users.index",
                access: "user",
            },
            {
                text: "item.role",
                icon: "mdi-account-lock-outline",
                to: "roles.index",
                access: "role",
            },
            {
                text: "item.permission",
                icon: "mdi-lock-outline",
                to: "permissions.index",
                access: "permission",
            },
        ],
    },
];
</script>
