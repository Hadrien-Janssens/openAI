<template>
    <div
        class="fixed top-0 left-0 h-screen overflow-scroll duration-300 bg-gray-100 no-scrollbar"
        :class="isMenuOpen ? 'w-60 ' : 'w-0'"
    >
        <div
            class="flex items-center justify-between p-4 text-xl text-gray-600"
        >
            <i class="fa-solid fa-table-columns" @click="toggleMenu"></i>
            <div class="space-x-5">
                <i class="fa-solid fa-magnifying-glass"></i>
                <Link :href="route('ask.post')">
                    <i class="fa-regular fa-pen-to-square"></i>
                </Link>
            </div>
        </div>
        <div class="flex flex-col p-2 space-y-2">
            <div
                v-for="conversation in conversations"
                class="p-1 rounded-lg hover:bg-gray-200 hover:cursor-pointer line-clamp-1"
                :class="conversation.id == conversationId ? 'bg-gray-200' : ''"
            >
                <Link
                    :href="route('ask.show', { conversation: conversation.id })"
                    class="block w-full"
                    :title="conversation.title"
                >
                    sxq{{ conversation.title }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    conversations: Object,
});

const isMenuOpen = defineModel();
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const conversationId = route().params["conversation"];
</script>
