<template>
    <div class="flex items-center justify-between p-4">
        <div>
            <i
                v-if="!isMenuOpen"
                class="fa-solid fa-table-columns text-xl text-gray-600"
                @click="$emit('update:isMenuOpen', !isMenuOpen)"
            ></i>
            <select
                v-model="selectedAIModel"
                class="rounded-md bg-transparent border-none focus:outline-none focus:ring-0"
            >
                <option
                    v-for="model in models"
                    :key="model.name"
                    :value="model.id"
                >
                    {{ model.name }}
                </option>
            </select>
        </div>
        <!-- USER AVATAR  -->
        <!-- <div
            class="rounded-full text-white font-extrabold bg-cyan-600 w-8 h-8 flex items-center justify-center"
        > -->
        <!-- Settings Dropdown -->
        <div class="">
            <Dropdown align="right" width="48">
                <template #trigger>
                    <button
                        v-if="$page.props.jetstream.managesProfilePhotos"
                        class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                    >
                        <img
                            class="size-8 rounded-full object-cover"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name"
                        />
                    </button>

                    <span v-else class="inline-flex rounded-md">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
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
                    <!-- Account Management -->
                    <div class="block px-4 py-2 text-xs text-gray-400">
                        Manage Account
                    </div>

                    <DropdownLink :href="route('profile.show')">
                        Profile
                    </DropdownLink>
                    <DropdownLink :href="route('customInstruction.index')">
                        custom instruction
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
                        <DropdownLink as="button"> Log Out </DropdownLink>
                    </form>
                </template>
            </Dropdown>
        </div>
        <!-- </div> -->
    </div>
</template>

<script setup>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

defineProps({
    models: Array,
    user: Object,
    isMenuOpen: Boolean,
});
const selectedAIModel = defineModel();

defineEmits(["update:isMenuOpen"]);

const logout = () => {
    router.post(route("logout"));
};
</script>
