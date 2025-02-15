<template>
    <div
        class="fixed top-0 left-0 z-50 h-screen overflow-scroll duration-300 bg-gray-100 no-scrollbar"
        :class="isMenuOpen ? 'w-60 ' : 'w-0'"
    >
        <div>
            <div class="sticky top-0 bg-gray-100">
                <div
                    class="flex items-center justify-between p-4 text-xl text-gray-600"
                >
                    <i
                        class="fa-solid fa-table-columns"
                        @click="toggleMenu"
                    ></i>
                    <div class="space-x-5">
                        <i
                            class="cursor-pointer fa-solid fa-magnifying-glass"
                            @click="toggleSearch"
                        ></i>
                        <i
                            @click="newConversation()"
                            class="fa-regular fa-pen-to-square"
                        ></i>
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
            </div>

            <div class="relative flex flex-col p-2 space-y-2">
                <div
                    v-for="conversation in filteredConversations"
                    class="p-1 rounded-lg hover:bg-gray-200 hover:cursor-pointer"
                    :class="
                        conversation.id == conversationId ? 'bg-gray-200' : ''
                    "
                >
                    <div class="flex items-start space-x-2 group line-clamp-1">
                        <Link
                            :href="
                                route('ask.show', {
                                    conversation: conversation.id,
                                })
                            "
                            class="block w-full line-clamp-1"
                            :title="conversation.title"
                        >
                            <p class="line-clamp-2">{{ conversation.title }}</p>
                        </Link>

                        <button
                            @click="openDeleteModal(conversation.id)"
                            class="transition-opacity duration-150 opacity-0 group-hover:opacity-100"
                        >
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
                <div
                    :class="
                        isMenuOpen ? 'fixed bottom-0 left-0 z-50' : 'hidden'
                    "
                >
                    <div class="w-full">
                        <Dropdown
                            align="right"
                            class=""
                            :placement="'top'"
                            @someEvent="toggleIcon = !toggleIcon"
                            @click="toggleIcon = !toggleIcon"
                        >
                            <template #trigger>
                                <button
                                    v-if="
                                        $page.props.jetstream
                                            .managesProfilePhotos
                                    "
                                    class="flex items-center gap-10 px-5 py-1 pt-3 text-sm transition bg-white w-60 focus:outline-none focus:border-gray-300 hover:bg-gray-100 hover:cursor-pointer"
                                >
                                    <img
                                        class="object-cover rounded-full size-8"
                                        :src="
                                            $page.props.auth.user
                                                .profile_photo_url
                                        "
                                        :alt="$page.props.auth.user.name"
                                    />
                                    <p>{{ $page.props.auth.user.name }}</p>
                                    <i
                                        v-if="!toggleIcon"
                                        class="fa-solid fa-ellipsis"
                                    ></i>
                                    <i
                                        v-else
                                        class="cursor-pointer fa-solid fa-times hover:cursor-pointer"
                                    ></i>
                                </button>

                                <span v-else class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50"
                                    >
                                        {{ $page.props.auth.user.name }}

                                        <svg
                                            class="ms-2 -me-0.5 size-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.show')">
                                    <div class="flex items-center gap-2">
                                        <i
                                            class="text-gray-400 fa-solid fa-user-circle"
                                        ></i>
                                        <p>Profile</p>
                                    </div>
                                </DropdownLink>
                                <DropdownLink :href="route('ask.index')">
                                    <div
                                        class="flex items-center gap-2 z-[9999]"
                                    >
                                        <i
                                            class="text-gray-400 fa-solid fa-up-right-from-square"
                                        ></i>
                                        <p>Nouvelle Conversation</p>
                                    </div>
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('customInstruction.index')"
                                >
                                    <div class="flex items-center gap-2">
                                        <i
                                            class="text-gray-400 fa-solid fa-head-side-virus"
                                        ></i>
                                        <p>Instructions Personnalisées</p>
                                    </div>
                                </DropdownLink>

                                <DropdownLink
                                    v-if="$page.props.jetstream.hasApiFeatures"
                                    :href="route('api-tokens.index')"
                                >
                                    API Tokens
                                </DropdownLink>

                                <div class="border-t border-gray-200" />

                                <!-- Authentication -->
                                <form @submit.prevent="logout">
                                    <DropdownLink as="button">
                                        <div class="flex items-center gap-2">
                                            <i
                                                class="text-gray-400 fa-solid fa-arrow-right-from-bracket"
                                            ></i>
                                            <p>Déconnexion</p>
                                        </div>
                                    </DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </div>
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
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    conversations: Object,
});

const isMenuOpen = defineModel();
const showDeleteModal = ref(false);
const conversationToDelete = ref(null);
const toggleIcon = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
    localStorage.setItem("isMenuOpen", isMenuOpen.value);
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

const logout = () => {
    router.post(route("logout"));
};

const newConversation = () => {
    if (window.innerWidth < 500) {
        toggleMenu();
    }
    router.get(route("ask.index"));
};
</script>
