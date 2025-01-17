<template>
    <AppLayout :user="user" :conversations="conversations">
        ma page custom instruction</AppLayout
    >
</template>
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    models: Array,
    user: Object,
    conversations: Array,
});
</script>
