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
                <i
                    class="cursor-pointer fa-solid fa-magnifying-glass"
                    @click="toggleSearch"
                ></i>
                <Link :href="route('ask.index')">
                    <i class="fa-regular fa-pen-to-square"></i>
                </Link>
            </div>
        </div>

        <div v-if="isSearchVisible" class="px-4 mb-2">
            <input
                type="text"
                v-model="searchTerm"
                class="w-full px-2 py-1 text-sm border rounded-lg focus:outline-none focus:border-gray-400"
                placeholder="Rechercher..."
            />
        </div>

        <div class="flex flex-col p-2 space-y-2">
            <div
                v-for="conversation in filteredConversations"
                class="p-1 rounded-lg hover:bg-gray-200 hover:cursor-pointer"
                :class="conversation.id == conversationId ? 'bg-gray-200' : ''"
            >
                <div class="flex items-start space-x-2 group line-clamp-1">
                    <Link
                        :href="
                            route('ask.show', { conversation: conversation.id })
                        "
                        class="block w-full"
                        :title="conversation.title"
                    >
                        {{ conversation.title }}
                    </Link>

                    <button
                        @click="openDeleteModal(conversation.id)"
                        class="transition-opacity duration-150 opacity-0 group-hover:opacity-100"
                    >
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal v-model="showDeleteModal" @confirm="confirmDelete" />
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ConfirmationModal from "./ConfirmationModal.vue";

const props = defineProps({
    conversations: Object,
});

const isMenuOpen = defineModel();
const showDeleteModal = ref(false);
const conversationToDelete = ref(null);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const conversationId = route().params["conversation"];

const openDeleteModal = (id) => {
    conversationToDelete.value = id;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(
        route("ask.destroy", { conversation: conversationToDelete.value })
    );
    showDeleteModal.value = false;
};

const isSearchVisible = ref(false);
const searchTerm = ref("");

const toggleSearch = () => {
    isSearchVisible.value = !isSearchVisible.value;
    if (!isSearchVisible.value) {
        searchTerm.value = "";
    }
};

const filteredConversations = computed(() => {
    if (!searchTerm.value) return props.conversations;

    return props.conversations.filter((conversation) =>
        conversation.title
            .toLowerCase()
            .includes(searchTerm.value.toLowerCase())
    );
});
</script>
