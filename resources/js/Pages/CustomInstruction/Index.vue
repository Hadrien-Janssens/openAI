<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import MenuBar from "@/Components/MenuBar.vue";
import TopMenuBar from "@/Components/TopMenuBar.vue";
import Stepper from "@/Components/Stepper.vue";

const props = defineProps({
    user: Object,
    conversations: Array,
});

const aboutInstruction = ref(props.user.about_instruction);
const comportementInstruction = ref(props.user.comportement_instruction);

const submitAboutForm = () => {
    router.post(
        "/custom-instruction/about",
        {
            aboutInstruction: aboutInstruction.value,
        },
        {
            onSuccess: () => {},
            preserveScroll: true,
        }
    );
};
const submitComportementForm = () => {
    router.post(
        "/custom-instruction/comportement",
        {
            comportementInstruction: comportementInstruction.value,
        },
        {
            onSuccess: () => {},
            preserveScroll: true,
        }
    );
};
const isMenuOpen = ref(true);
</script>

<template>
    <!-- <MenuBar v-model="isMenuOpen" :conversations="conversations" />

    <TopMenuBar
        :user="user"
        v-model="selectedAIModel"
        :isMenuOpen="isMenuOpen"
        @update:isMenuOpen="isMenuOpen = $event"
    /> -->
    <!-- test  -->
    <div class="flex min-h-[100dvh] bg-white">
        <!-- BAR LATERAL  -->
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />
        <!-- MAIN  -->
        <div
            class="flex flex-col w-full h-[100dvh] duration-300"
            :class="isMenuOpen ? 'ml-60' : 'ml-0'"
        >
            <div>
                <!-- TOP BAR MENU - Fixed at top -->
                <TopMenuBar
                    :models="models"
                    :user="user"
                    v-model="selectedAIModel"
                    :isMenuOpen="isMenuOpen"
                    @update:isMenuOpen="isMenuOpen = $event"
                />
                <div class="max-w-3xl p-4 mx-auto">
                    <div>
                        <!-- findetest -->
                        <!-- <div class="w-full px-10 pb-0"> -->
                        <Stepper />
                        <div class="mt-10">
                            <h3 class="mb-2 text-lg font-bold">
                                Etape 1 : A propos de vous
                            </h3>
                            <h4 class="mb-2 text-sm">
                                Ces informations aide l'assistant à adapter ses
                                réponses et suggestions à votre contexte.
                            </h4>
                        </div>
                        <form @submit.prevent="submitAboutForm">
                            <textarea
                                v-model="aboutInstruction"
                                name=""
                                id=""
                                rows="6"
                                class="w-full border"
                            ></textarea>
                            <SecondaryButton type="submit"
                                >Envoyer</SecondaryButton
                            >
                        </form>
                    </div>

                    <div class="w-full p-10 pb-0">
                        <div>
                            <h3 class="mb-2 text-lg font-bold">
                                Comportement de l'assistant
                            </h3>
                            <h4 class="mb-2 text-sm">
                                Ici, vous avez la possibilité de définir comment
                                vous souhaitez que l'assistant interagisse avec
                                vous. Cela comprend le ton des réponses, leur
                                format, et même la manière dont les concepts
                                sont expliqués.
                            </h4>
                        </div>
                        <form
                            action=""
                            @submit.prevent="submitComportementForm"
                        >
                            <textarea
                                v-model="comportementInstruction"
                                name=""
                                id=""
                                rows="10"
                                class="w-full border"
                            ></textarea>
                            <SecondaryButton type="submit"
                                >Envoyer</SecondaryButton
                            >
                        </form>
                    </div>

                    <div class="w-full p-10 pb-0">
                        <div>
                            <h3 class="mb-2 text-lg font-bold">
                                Créer des commandes personnalisées
                            </h3>
                            <h4 class="mb-2 text-sm">
                                Les commandes personnalisées vous permettent de
                                définir des interactions spécifiques avec
                                l'assistant, simplifiant et accélérant l'accès à
                                l'information ou l'exécution de tâches
                                récurrentes.
                            </h4>
                        </div>
                        <form action="">
                            <textarea
                                name=""
                                id=""
                                rows="10"
                                class="w-full border"
                            ></textarea>
                            <SecondaryButton>Envoyer</SecondaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- </AppLayout> -->
</template>
