<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import MenuBar from "@/Components/MenuBar.vue";
import { onMounted, ref } from "vue";
import TopMenuBar from "@/Components/TopMenuBar.vue";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
const isMenuOpen = ref(true);

onMounted(() => {
    if (window.innerWidth < 500) {
        isMenuOpen.value = false;
    }
});
</script>

<template>
    <!-- <AppLayout title="Profile" :user="user" :conversations="conversations"> -->
    <!-- <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template> -->
    <div class="flex min-h-[100dvh] bg-white">
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />

        <div
            class="flex flex-col w-full h-[100dvh] duration-300"
            :class="isMenuOpen ? 'ml-60' : 'ml-0'"
        >
            <TopMenuBar
                :models="models"
                :user="user"
                v-model="selectedAIModel"
                :isMenuOpen="isMenuOpen"
                @update:isMenuOpen="isMenuOpen = $event"
            />
            <div class="py-10 pt-0 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm
                        :user="$page.props.auth.user"
                    />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <!-- <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                <TwoFactorAuthenticationForm
                    :requires-confirmation="confirmsTwoFactorAuthentication"
                    class="mt-10 sm:mt-0"
                />

                <SectionBorder />
            </div> -->

                <!-- <LogoutOtherBrowserSessionsForm
                :sessions="sessions"
                class="mt-10 sm:mt-0"
            /> -->

                <template
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                >
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </div>
    <!-- </AppLayout> -->
</template>
